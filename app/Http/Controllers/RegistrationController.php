<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Http\Resources\UserResource;
use App\Http\Requests\RegisterRequest;
use Illuminate\Notifications\Notifiable;

class RegistrationController extends Controller
{
    use Helpers, Notifiable;
    
    public function store(RegisterRequest $request)
    {

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'status_id' => RegistrationController::getStatusId('Inactive'),
            'user_type' => $request->user_type,
        ]);

        UserRegistered::dispatch($user);

        return new UserResource($user, true);
    }
}
