<?php

namespace App\Http\Controllers\Auth;

use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Services\TotpService;
use App\Http\Traits\HasApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Notifications\Auth\EmailChangenotify;
use App\Notifications\Auth\Emailverifynotify;
use App\Notifications\Auth\VerifyEmailNotify;
use App\Http\Requests\Auth\ChangeEmailRequest;
use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;

class EmailController extends Controller
{
    use HasApiResponse, Helpers;

    public function verify(VerifyEmailRequest $request)
    {
        $user = $request->user();

        if ($this->checkIfVerified($user)) {
            $user->update([
                'email_verified_at' => now(),
                'status_id' => EmailController::getStatusId('Active'),

            ]);

            $user->notify(new Emailverifynotify($user));

            return $this->okResponse('Code Validated Successfully', new UserResource($request->user()));
        }

        return $this->forbiddenResponse('Email Has Already Been Validated');
    }

    public function resend($email)
    {
        $user = User::where('email', $email)->first();
        if(!isset($user))
        {
            return $this->forbiddenResponse('Email Did Not Exist');
        }
        if ($this->checkIfVerified($user)) {
            $this->sendToken($user);

            return $this->okResponse('Token Has Been Resent');
        }

        return $this->forbiddenResponse('Email Has Already Been Validated');
    }

    public function changeEmail(ChangeEmailRequest $request)
    {
        $user = $request->user();
        $user->email = $request->email;
        $user->email_verified_at = null;
        $user->save();

        $this->sendToken($user);
        $user->notify(new EmailChangenotify($user));

        return $this->okResponse('Email Has Been Changed And Token Resent', new UserResource($request->user()));
    }

    protected function checkIfVerified($user)
    {
        return is_null($user->email_verified_at);
    }

    protected function sendToken($user)
    {
        $user->notify(new VerifyEmailNotify($user, new TotpService()));

    }
}
