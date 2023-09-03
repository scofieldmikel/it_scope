<?php

namespace App\Http\Traits;

use App\Helpers\Misc;
use App\Mail\PaymentPolicy;
use App\Mail\SendUnsignedMandate;
use App\Models\Auth\Month;
use App\Models\Auth\Repayment;
use App\Models\Auth\Week;
use App\Models\Technician\Area;
use App\Models\Technician\Mechanic;
use App\Models\Transaction\Review;
use App\Models\User;
use App\Models\Vehicle\Car;
use App\Notifications\LoanApplication\ApplicationApproved;
use App\Notifications\PushNotify;
use App\Notifications\UserNotify;
use App\Services\Mono\Mono;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Mail;
use Wnx\SidecarBrowsershot\BrowsershotLambda;

trait ServiceTrait
{
    public function createNewService(User $user, Area $area, Car $car, Mechanic $mechanic, $request): Review
    {
        $review = new Review();
        $review->payment_type = $request->payment_type;
        $review->service_date = $request->service_date;
        $review->appointment_date = $request->appointment_date;
        $review->what_for = $request->what_for;
        $review->description = $request->description;
        $review->data = [];
        $review->user()->associate($user);
        $review->area()->associate($area);
        $review->car()->associate($car);
        $review->mechanic()->associate($mechanic);
        $review->save();

        return $review;
    }

    /**
     * @throws Exception
     */
    protected function processData(Review $review, $interest = false): void
    {
        $duration = null;
        $period = explode(' ', $review->payback_period);

        if ($review->user->job_category->job_type === 'EMPLOYED') {
            $duration = Month::where('title', $review->payback_period)->firstOrFail();
            $this->period = $period[0];
        }

        if ($review->user->job_category->job_type === 'E-HAIL') {
            $duration = Week::where('title', $review->payback_period)->firstOrFail();
            $this->period = $period[0] / 2;
        }

        if (is_null($duration)) {
            throw new Exception('Duration not found');
        }

        $this->interest = ! $interest ? $duration->interest : $this->interest;

        $dataTotal = collect($review->data)->reduce(function ($item, $key) {
            return $item + $key;
        });
        $this->total = $review->workmanship + $dataTotal;

        $this->totalLoan = ($this->total * $this->interest / 100) + $this->total;

        $this->amount = $this->totalLoan / $this->period;
    }

    protected function calculateGrossTotal(Review $review)
    {
        if ($review->payment_type === Review::INSTALMENTS) {
            if (
                $review->what_for[0] === 'private' ||
                $review->what_for[0] === 'employed' ||
                $review->what_for[0] === 'self employed') {
                $duration = Month::where('title', $review->payback_period)->firstOrFail();
            } else {
                $duration = Week::where('title', $review->payback_period)->firstOrFail();
            }

            if (is_null($duration)) {
                return 0;
            }

            return $duration->interest;
        }

        return Misc::settings('service')['flickify_service_charge'];
    }

    public function showMono(User $user, Review $review): JsonResponse
    {
        //check if the  data sync can be access
        if ($user->mono()->exists()) {
            $mono = Mono::dataSync($user->mono->balance['account']['_id']);

            if (is_array($mono)) {
                $status = $mono['status'] ?? null;
                $code = $mono['code'] ?? null;

                if ($status === 'failed') {
                    $user->mono->update(
                        [
                            'data_status' => 0,
                            'requires_reauth' => 0,
                        ]
                    );
                } else {
                    return $this->okResponse(
                        'You have successfully completed your request for an auto service loan. Go for your vehicle auto service on the due date.',
                        [
                            'mono' => true,
                            'id' => $review->id,
                            'paystack' => $user->paystack()->exists(),
                        ]
                    );
                }
            }
        }

        return $this->okResponse('To further verify your identity and determine if you are eligible for this auto service, We need your account statement', [
            'mono' => false,
            'mono_message' => [
                'title' => 'Connect to bank',
                'message' => 'Send a 6months account statement of your most active bank account (salary account) by connecting through internet or mobile banking',
            ],
            'manual' => [
                'title' => 'Upload manually',
                'message' => 'Open your bank mobile app, Generate a 6months account statement and upload here Or Visit your bank, obtain and upload your 6months account statement ',
            ],
            'id' => $review->id,
            'paystack' => $user->paystack()->exists(),
            'authorization_url' => URL::temporarySignedRoute(
                'mono', now()->addMinutes(30), ['user' => $user->id]
            ),
        ]);
    }

    /**
     * @throws Exception
     */
    public function processAcceptance(Review $review, User $user): void
    {
        $interest = false;
        if ($review->coupon()->exists() && ! $review->coupon->used) {
            $review->coupon->used = true;
            $review->coupon->user()->associate($user);
            $review->coupon->save();
            $this->interest = $review->coupon->discount($review);
            $interest = true;
        }

        $this->processData($review, $interest);

        $review->total = $this->totalLoan;
        $review->interest = $this->interest;
        $review->status = Review::OFFER_APPROVED;
        $review->save();
        $period = explode(' ', $review->payback_period);

        Collection::times($this->period, function ($week) use ($user, $review, $period) {
            if (strtolower($period[1]) === 'weeks') {
                $date = $review->review_setting->end_date->addWeeks($week * 2);
            } else {
                $date = $review->review_setting->end_date->addMonths($week);
            }

            $repayment = new Repayment();
            $repayment->due_date = $date;
            $repayment->amount = $this->amount;
            $repayment->user()->associate($user);
            $repayment->review()->associate($review);
            $repayment->save();
        });
        $user->notify(new ApplicationApproved($review, $user));
        Mail::to($user)->queue(new PaymentPolicy());
    }

    public function processGenerateMandate(Review $review, User $user): string
    {
        $encrypted_id = Crypt::encryptString($review->id);
        $target = $user->id.'/'.$user->first_name.'_mandate.pdf';
        $url = config('app.env') == 'production' ? 'https://flickwheel.com/mandate_form?id='.$encrypted_id : 'https://flickwheel-limited.github.io/mandate_form2/';
        BrowsershotLambda::url($url)
            ->format('A4')
            ->saveToS3($target);

        Storage::setVisibility($target, 'public');

        $review->review_setting()->update([
            'mandate_url' => $target,
        ]);

        $review->update([
            'status' => Review::MANDATE_SENT,
        ]);

        \Illuminate\Support\Facades\Mail::to($user)->queue(new SendUnsignedMandate($target, $review));
        $user->notify(new PushNotify('You Have A Mandate Waiting To Be Signed', 'DIRECT DEBIT MANDATE FORM SENT'));
        $user->notify(new UserNotify($user, 'You Have A Mandate Waiting To Be Signed', 'DIRECT DEBIT MANDATE FORM SENT'));

        return $target;
    }
}
//        if ($review->what_for[0] === 'private' || $review->what_for[0] === 'employed' || $review->what_for[0] === 'self employed') {
//            $duration = Month::where('title', $review->payback_period)->firstOrFail();
//            $this->period = $period[0];
//        }

//        if ($review->what_for[0] === 'uber' || $review->what_for[0] === 'taxify') {
//            $duration = Week::where('title', $review->payback_period)->firstOrFail();
//            $this->period = $period[0] / 2;
//        }
