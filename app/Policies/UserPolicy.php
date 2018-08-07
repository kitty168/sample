<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * 验证编辑的用户是否为当前用户的策略
     * @param User $currentUser
     * @param User $user
     * @return bool
     */
    public function update(User $currentUser, User $user)
    {
        return $currentUser->id === $user->id;
    }

    /**
     * 当前策略为：只有管理员才能执行删除，且管理不能自己删除自己
     * @param User $currenUser
     * @param User $user
     * @return bool
     */
    public function destroy(User $currenUser, User $user)
    {
        return $currenUser->is_admin && $currenUser->id !== $user->id;
    }
}
