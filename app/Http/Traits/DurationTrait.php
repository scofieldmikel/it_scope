<?php

namespace App\Http\Traits;

trait DurationTrait
{
    protected function getDuration($repayment): float|int|string
    {
        $period = explode(' ', $repayment->review->payback_period);

        if ($repayment->review->what_for[0] === 'private' || $repayment->review->what_for[0] === 'employed' || $repayment->review->what_for[0] === 'self employed') {
            return $period[0];
        }

        return $period[0] / 2;
    }
}
