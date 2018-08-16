<?php

namespace App\Models;

use App\Notifications\ResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

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
        return $this->statuses()->orderBy('created_at', 'desc');
    }
}
