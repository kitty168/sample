<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
     */
    public static function boot()
    {
        parent::boot();

        //creating 用于监听模型被创建之前的事件，
        //created 用于监听模型被创建之后的事件。
        static::creating(function ($user){
            $user->activation_token = str_random(30);
        });
    }

    /**
     * 生成用户头像
     * @param string $size
     * @return string
     */
    public function gravatar($size = '100')
    {
        /*$hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";*/

        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    /**
     * 调用app\Notifications\ResetPassword.php
     * @param string $token
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }


    /**
     * 获得此用户的微博
     * 一对多，一个用户有多条微博
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    /**
     * 根据当前用户取出该用户的微博信息，并按照发表时间排序
     */
    public function feed()
    {
        //return $this->statuses()->orderBy('created_at', 'desc');
        //下面是改进的方法
        /*
         * $user->followings 与 $user->followings() 调用时返回的数据是不一样的，
         * $user->followings 返回的是 Eloquent：集合 。
         * 而 $user->followings() 返回的是 数据库请求构建器
         */
        $user_ids = Auth::user()->followings->pluck('id')->toArray();
        array_push($user_ids, Auth::user()->id);

        //注意with的用法
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    /**
     * 获取粉丝关系列表，如我有哪些粉丝
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    /**
     * 来获取用户关注人列表,如我关注了哪些人
     * follower_id 是user_id的粉丝
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }

    /**
     * 关注某个用户
     * @param $user_ids
     */
    public function follow($user_ids)
    {
        if(!is_array($user_ids)){
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids, false);
    }

    /**
     * 取消关注
     * @param $user_ids
     */
    public function unfollow($user_ids)
    {
        if (!is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->detach($user_ids);
    }

    /**
     * 用于判断当前登录的用户 A 是否关注了用户 B，代码实现逻辑很简单，
     * 我们只需要判断用户 B 是否包含在用户 A 的关注人列表上即可。
     * 这里我们将用到 contains 方法来做判断
     * @param $user_id
     * @return mixed
     */
    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
