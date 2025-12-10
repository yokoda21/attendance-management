<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    /**
     * 管理者ログイン画面を表示
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('auth.admin-login');
    }

    /**
     * 管理者ログイン処理
     *
     * @param  \App\Http\Requests\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        // LoginRequestで既にバリデーション済み

        // ログイン認証を試行（管理者のみ）
        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
            'role' => 1, // 管理者のみ
        ])) {
            // セッション再生成（セキュリティ対策）
            $request->session()->regenerate();

            // 日次勤怠一覧画面にリダイレクト
            return redirect()->route('admin.attendance.list');
        }

        // ログイン失敗時のエラーメッセージ（機能要件FN016）
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }

    /**
     * 管理者ログアウト処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}
