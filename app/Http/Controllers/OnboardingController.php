<?php

namespace App\Http\Controllers;

use App\Models\Interest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OnboardingController extends Controller
{
    public function setName(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return response()->json([
            'message' => 'Name saved!',
        ]);
    }

    public function setInterests(Request $request)
    {
        $request->validate([
            'interests' => 'required|array',
            'interests.*' => 'required|integer|exists:interests,id',
        ]);

        $interests = $request->interests;
        /** @var User $user */
        $user = Auth::user();

        $user->interests()->sync($interests);
        // $user->interests()->whereNotIn('id', $request->interests)->delete();
        //
        // Add new comments from the array
        // foreach ($interests as $interest) {
        //     $user->interests()->save(Interest::find($interest));
        // }

        return response()->json([
            'message' => 'Interests saved!',
        ]);
    }
}
