<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class SessionsController extends Controller
{
    public function __construct()
    {
        //Auth中间件提供的guest选项，用于指定一些只允许未登录用户访问的动作，
        //因此我们需要通过对 guest 属性进行设置，只让未登录用户访问登录页面和注册页面。
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    /**
     * 显示登陆页面
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('sessions.create');
    }

    /**
     * 执行登录动作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        //Auth::attempt() 用于登录认证，
        //该方法可接收两个参数，
        //第一个参数为需要进行用户身份认证的数组，
        //第二个参数为是否为用户开启『记住我』功能的布尔值
        if(Auth::attempt($credentials, $request->has('remember'))){
            //认证成功后的相关操作
            if(Auth::user()->activated){
                //已激活的用户，执行登录成功，并做页面跳转

                session()->flash('success', '欢迎回来！');

                //intended方法可将页面重定向到上一次请求尝试访问的页面上，
                //并接收一个默认跳转地址参数，当上一次请求记录为空时，跳转到默认地址上。
                return redirect()->intended(route('users.show', [Auth::user()]));
            }else{
                //销毁session，即退出登录
                Auth::logout();
                session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }

        }else{
            //认证失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput(); //withInput() 用于回传请求参数
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
