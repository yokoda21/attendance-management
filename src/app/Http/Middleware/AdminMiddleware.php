<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
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
        // ログインしていない場合は管理者ログインページへリダイレクト
        if (!Auth::check()) {
            return redirect()->route('admin.login');
        }

        // ログインしているが管理者でない場合
        if (!Auth::user()->isAdmin()) {
            // ログアウトさせて管理者ログインページへリダイレクト
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            return redirect()->route('admin.login')->withErrors([
                'error' => 'アクセス権限がありません。',
            ]);
        }

        return $next($request);
    }
}
