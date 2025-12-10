<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // ログインしていない場合はログインページへリダイレクト
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // ログインしているが管理者の場合
        if (Auth::user()->isAdmin()) {
            // ログアウトさせてログインページへリダイレクト
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'error' => 'このページへのアクセス権限がありません。',
            ]);
        }

        return $next($request);
    }
}
