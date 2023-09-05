<?php

namespace App\Http\Controllers;

use App\Traits\Helpers;
use Illuminate\Http\Request;
use App\Http\Traits\HasApiResponse;

class UserController extends Controller
{
    use HasApiResponse, Helpers;
    
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return $this->okResponse('Profile Updated');
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return $this->okResponse('Logout Successful');
    }
}
