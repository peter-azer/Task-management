<?php

namespace App\Http\Controllers\api_v1;

use App\Http\Controllers\Controller;
use App\Logic\api_v1\UserApiLogic;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected UserApiLogic $logic)
    {
    }

    /**
     * List users (paginated).
     * Query params: per_page (int, default 15)
     */
    public function index(Request $request)
    {
        $perPage = (int)($request->query('per_page', 15));
        $perPage = $perPage > 0 && $perPage <= 100 ? $perPage : 15;
        return response()->json($this->logic->listUsers($perPage));
    }

    /**
     * Show single user by id.
     */
    public function show(int $id)
    {
        return response()->json($this->logic->findUserById($id));
    }
}
