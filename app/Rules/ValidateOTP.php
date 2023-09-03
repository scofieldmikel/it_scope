<?php

namespace App\Rules;

use App\Services\TotpService;
use Illuminate\Contracts\Validation\Rule;

class ValidateOTP implements Rule
{
    /**
     * @var null
     */
    private string $time;

    protected TotpService $totpService;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($time = '')
    {
        //
        $this->time = $time;
        $this->totpService = new TotpService();
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (! empty($this->time)) {
            $this->totpService->setTime(now()->addHour()->timestamp);
        }

        return $this->totpService->addChecksum(true)->validateCode($value);
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The Code You Have Entered Is Invalid';
    }
}
