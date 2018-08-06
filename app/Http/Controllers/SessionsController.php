<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionsController extends Controller
{
    /**
     * 显示登陆页面，git测试
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('sessions.create');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        //Auth::attempt() 方法可接收两个参数，
        //第一个参数为需要进行用户身份认证的数组，
        //第二个参数为是否为用户开启『记住我』功能的布尔值
        if(Auth::attempt($credentials, $request->has('remember'))){
            //登录成功后的相关操作
            session()->flash('success', '欢迎回来！');
            return redirect()->route('users.show', [Auth::user()]);
        }else{
            //登录失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱喝密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    /**
     * 销毁会话（退出登陆）
     */
    public function destroy()
    {
        //用Laravel提供的logout方法实现退出登录
        Auth::logout();
        session()->flash('success', '您已成功退出');
        return redirect('login');
    }
}
