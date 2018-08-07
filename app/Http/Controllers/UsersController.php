<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        //引入中间件
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store']
        ]);

        /*$this->middleware('auth', [
            'only' => ['create']
        ]);*/


    }

    /**
     * create试图显示
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * 用户信息显示
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    /**
     * store执行插库动作
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        //创建用户成功后，执行登陆动作
        Auth::login($user);

        //通过session的闪存来临时保存用户数据
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
    }

    /**
     * 展示用户编辑视图
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $user)
    {
        //authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    /**
     * 执行用户更新操作
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(User $user, Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6',
        ]);

        //authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据
        //它可以被用于快速授权一个指定的行为，当无权限运行该行为时会抛出 HttpException
        $this->authorize('update', $user);

        $data = [];
        $data['name'] = $request->name;
        if($request->password){
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        //页面消息提示
        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);
    }

}
