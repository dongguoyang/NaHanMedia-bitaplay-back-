<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('token');
        if (!$token) {
            return response()->json(['code' => ERR_NOT_LOGIN, 'msg' => '请登录', 'data' => '']);
        }
        if (!$user = Admin::where('token', $token)->first()) {
            return response()->json(['code' => ERR_NOT_LOGIN, 'msg' => '请登录', 'data' => '']);
        }
        Auth::guard('admin')->setUser($user);
        return $next($request);
    }
}
