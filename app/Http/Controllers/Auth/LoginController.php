<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Events\UserRegistered;
use App\Http\Traits\HasApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Auth\LoginRequest;

class LoginController extends Controller
{
    use HasApiResponse, Helpers;
    
    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user->status_id == LoginController::getStatusId('Inactive')) {
            UserRegistered::dispatch($user);
            return $this->forbiddenResponse('Login failed! Account is inactive');
        }

        if ($user->status_id == LoginController::getStatusId('Suspended')) {
            return $this->forbiddenResponse('Sorry, Your account has been suspended! Please, contact support!');
        }

        if (Auth::attempt($request->only(['email', 'password']))) {
            return new UserResource(auth()->user(), true);
        }

        return $this->notFoundResponse('Invalid Credentials');
    }
}
