<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * ログイン画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        // LoginRequestで既にバリデーション済み

        // ログイン認証を試行
        if (Auth::attempt($request->only('email', 'password'))) {
            // セッション再生成（セキュリティ対策）
            $request->session()->regenerate();

            // 打刻画面にリダイレクト（機能要件FN005）
            return redirect()->intended('/attendance');
        }

        // ログイン失敗時のエラーメッセージ（機能要件FN009）
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    /**
     * ログアウト処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
