<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;


class StaticPagesController extends Controller
{
    public function home()
    {
        $feed_items = [];
        if(Auth::check()){
            //已登录
            $feed_items = Auth::user()->feed()->paginate(20);
        }
        return view('static_pages/home', compact('feed_items'));
    }

    public function help()
    {
        return view('static_pages/help');
    }

    public function about()
    {
        return view('static_pages/about');
    }
}
