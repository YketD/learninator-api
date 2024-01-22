<?php

namespace App\Http\Controllers;

use App\Http\Requests\GuestLoginRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function guestLogin(GuestLoginRequest $request)
    {
        /** @var User $user */
        $user = User::query()->updateOrCreate(
            [
                'device_id' => $request->device_id,
            ],
            [
                'name'           => $request->name ?? 'Guest',
                'accept_terms'   => $request->accept_terms,
                'accept_privacy' => $request->accept_privacy,
            ]
        );

        $user->save();

        return response()->json([
            'token' => $user->createToken('guest')->plainTextToken,
        ]);
    }
}
