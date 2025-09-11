<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Models\User;

class RolesAndPermissionsController extends Controller
{
    public function assignPermissionToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'permissions' => 'array',
            'permissions.*' => 'required|string|exists:permissions,name',
        ]);

        $user = User::find($request->input('user_id'));
        $permissions = $request->input('permissions');

        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) continue;
        }

        $user->syncPermissions($permissions);

        return response()->json(['message' => 'Permission assigned successfully.']);
    }
}
