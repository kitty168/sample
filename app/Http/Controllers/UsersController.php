<?php

/**
 * 用户资源控制器
 */
namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Auth;
use Mail;

class UsersController extends Controller
{
    public function __construct()
    {
        //引入中间件，
        /*
         * 我们在 __construct 方法中调用了 middleware 方法，
         * 该方法接收两个参数，第一个为中间件的名称，第二个为要进行过滤的动作。
         * 我们通过 except 方法来设定 指定动作 不使用 Auth 中间件进行过滤，
         * 意为 —— 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问，类似于黑名单的过滤机制。
         * 相反的还有 only 白名单方法，将只过滤指定动作。
         * 我们提倡在控制器 Auth 中间件使用中，首选 except 方法，
         * 这样的话，当你新增一个控制器方法时，默认是安全的，此为最佳实践。
         */
        $this->middleware('auth', [
            'except' => ['show', 'create', 'store', 'index', 'confirmEmail']
        ]);

        //Auth中间件提供的guest选项，用于指定一些只允许未登录用户访问的动作，
        //因此我们需要通过对 guest 属性进行设置，只让未登录用户访问登录页面和注册页面。
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }

    public function index()
    {
        //$users = User::all();//获取所有用户

        //paginate 方法来指定每页生成的数据数量为 10 条，
        //即当我们有 50 个用户时，用户列表将被分为五页进行展示。
        $users = User::paginate(10);

        return view('users.index', compact('users'));
    }

    /**
     * 显示用户注册视图
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
        //通过$user->statuses()获取此用户的微博信息
        $statuses = $user->statuses()
            ->orderBy('created_at', 'desc')
            ->paginate(5);

        return view('users.show', compact('user', 'statuses'));
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

        /*
        //创建用户成功后，执行登陆动作
        Auth::login($user);

        //通过session的闪存来临时保存用户数据
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        return redirect()->route('users.show', [$user]);
        */

        //发送验证码到用户注册的邮箱上
        $this->sendEmailConfirmationTo($user);

        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');

    }

    /**
     * 展示用户编辑视图
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit(User $user)
    {
        try {
            //authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据
            //只有被编辑的用户等于当前用户的时候才能操作
            $this->authorize('update', $user);

        }catch (AuthorizationException $e){
            return abort(403, '无权访问');
        }
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

        try {
            //authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据
            //它可以被用于快速授权一个指定的行为，当无权限运行该行为时会抛出 HttpException
            $this->authorize('update', $user);

        } catch (AuthorizationException $e) {
            return abort(403, '无权访问');
        }



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

    /**
     * 删除用户
     * @param User $user
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy(User $user)
    {
        //引入验证策略destroy
        $this->authorize('destroy', $user);

        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    /**
     * 发送确认邮件到用户邮箱
     * @param $user
     */
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
//        $from = 'aufree@yousails.com';
//        $name = 'Aufree';
        $to = $user->email;
        $subject = '感谢注册 Sample 应用！请确认你的邮箱。';

        //发送邮件
//        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject){
//            $message->from($from, $name)->to($to)->subject($subject);
//        });
        Mail::send($view, $data, function ($message) use ($to, $subject){
            $message->to($to)->subject($subject);
        });
    }

    /**
     * 通过邮件发送的链接，来激活用户账号
     * @param $token
     * @return \Illuminate\Http\RedirectResponse
     */
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        //登录
        Auth::login($user);
        session()->flash('success', '恭喜您，激活成功！');
        return redirect()->route('users.show', [$user]);
    }

    /**
     * 显示用户的关注人的列表
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followings(User $user)
    {
        $users = $user->followings()->paginate(20);
        $title =  '关注的人';

        return view('users.show_follow', compact('users', 'title'));
    }

    /**
     * 显示我的粉丝列表
     * @param User $user
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function followers(User $user)
    {
        $users = $user->followers()->paginate(20);
        $title = '粉丝';
        return view('users.show_follow', compact('users', 'title'));
    }
}
