<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class FollowersController extends Controller
{
    public function __construct()
    {
        //引入中间件，auth中间件
        $this->middleware('auth');
    }

    public function store(User $user)
    {
        if (Auth::user()->id === $user->id) {
            return redirect('/');
        }

        if (!Auth::user()->isFollowing($user->id)) {
            Auth::user()->follow($user->id);
        }
        return redirect()->route('users.show', $user->id);
    }

    public function destroy(User $user)
    {
        if (Auth::user()->id === $user->id) {
            return redirect('/');
        }

        if (Auth::user()->isFollowing($user->id)) {
            Auth::user()->unfollow($user->id);
        }

        return redirect()->route('users.show', $user->id);
    }
}
