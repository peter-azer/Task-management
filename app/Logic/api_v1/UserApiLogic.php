<?php

namespace App\Logic\api_v1;

use App\Models\User;

class UserApiLogic
{
    /**
     * Return paginated list of users.
     */
    public function listUsers(int $perPage = 15)
    {
        return User::query()->paginate($perPage);
    }

    /**
     * Find a user by id or fail.
     */
    public function findUserById(int $id): User
    {
        return User::query()->findOrFail($id);
    }
}
