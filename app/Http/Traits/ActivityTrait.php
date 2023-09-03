<?php

namespace App\Http\Traits;

use App\Models\User;

trait ActivityTrait
{
    public function getUserActivities(User $refer_id)
    {
        $activities = [];

        if (! is_null($refer_id->phone_verified_at)) {
            $activities[] = 'Signed Up';
        }

        if ($refer_id->car()->exists()) {
            $activities[] = 'Car Added';
        }

        if ($refer_id->review()->exists()) {
            $activities[] = 'Car Care Done';
        }

        return $activities;
    }
}
