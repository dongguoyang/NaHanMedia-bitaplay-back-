<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class ApiAuth
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
            return response()->json(['code' => ERR_NOT_LOGIN, 'msg' => '请登录','data'=>'']);
        }
        if (!$user = User::where('token', $token)->first()) {
            return response()->json(['code' => ERR_NOT_LOGIN, 'msg' => '请登录','data'=>'']);
        }
        if ($user->status != USER_STATUS_ABLE) {
            return response()->json(['code' => ERR_USER_DISABLED, 'msg' => '已禁用','data'=>'']);
        }
        Auth::guard('api')->setUser($user);
        return $next($request);
    }
}
