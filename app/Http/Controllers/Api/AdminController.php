<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index()
    {
        return User::query()->select('id', 'name', 'login_id', 'created_at')->orderBy('created_at')->get();
    }

    public function store(StoreAdminRequest $request)
    {
        $admin = User::create([
            'name' => $request->validated('name'),
            'login_id' => $request->validated('login_id'),
            'password' => Hash::make($request->validated('password')),
        ]);

        return response()->json([
            'id' => $admin->id,
            'name' => $admin->name,
            'login_id' => $admin->login_id,
            'created_at' => $admin->created_at,
        ])->setStatusCode(201);
    }
}
