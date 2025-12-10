<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\AttendanceCorrectionRequestController;
use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CorrectionController as AdminCorrectionController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


/*
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ホーム（ログイン画面）へリダイレクト
Route::get('/', function () {
    return redirect('/login');
});

// ログイン
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

// 会員登録
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// 管理者ログイン
Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'store']);
Route::post('/admin/logout', [AdminLoginController::class, 'destroy'])->name('admin.logout');

// 一般ユーザー用ルート（userミドルウェアで保護）
Route::middleware(['auth', 'verified', 'user'])->group(function () {
    // 打刻画面
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');

    // 出勤・退勤
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');

    // 休憩
    Route::post('/break/start', [BreakController::class, 'start'])->name('break.start');
    Route::post('/break/end', [BreakController::class, 'end'])->name('break.end');

    // 勤怠一覧
    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    // 勤怠詳細（ルートの順序重要：listの後に配置）
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');


    // 申請作成（POSTのみ）
    Route::post('/stamp_correction_request', [AttendanceCorrectionRequestController::class, 'store'])->name('stamp_correction_request.store');
});

// 管理者用ルート（adminミドルウェアで保護）
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    // 日次勤怠一覧
    Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])->name('admin.attendance.list');

    // 勤怠詳細（管理者）
    Route::get('/attendance/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendance.show');

    // 勤怠修正（管理者による直接修正）
    Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendance.update');

    // スタッフ一覧
    Route::get('/staff/list', [AdminUserController::class, 'index'])->name('admin.staff.list');

    // スタッフ別月次勤怠一覧
    Route::get('/attendance/staff/{id}', [AdminUserController::class, 'attendances'])->name('admin.attendance.staff');

    // CSV出力
    Route::get('/attendance/staff/{id}/csv', [AdminUserController::class, 'exportCsv'])->name('admin.attendance.staff.csv');
});

// 申請一覧（一般・管理者 共通パス、これを先に定義）
// ミドルウェアは 'auth' のみ適用し、内部でユーザーの権限(role)を見て分岐させる
Route::middleware(['auth'])->get('/stamp_correction_request/list', function (\Illuminate\Http\Request $request) { // ← ここでRequestを受け取る
    // ユーザーが管理者の場合 (role = 1)
    if (Auth::user()->role === 1) {
        // 管理者用コントローラーをインスタンス化して index を実行
        return app(App\Http\Controllers\Admin\CorrectionController::class)->index($request);
    }

    // それ以外（一般ユーザー）の場合
    // 一般ユーザー用コントローラーをインスタンス化して index を実行
    return app(App\Http\Controllers\AttendanceCorrectionRequestController::class)->index($request);
})->name('stamp_correction_request.list');



// 申請一覧・承認（一般・管理者共通パス、ミドルウェアで区別）
Route::middleware(['auth', 'admin'])->group(function () {
    // 修正申請承認
    Route::put('/stamp_correction_request/approve/{id}', [AdminCorrectionController::class, 'approve'])->name('stamp_correction_request.approve');

    // 申請詳細（エラー出ないよう id は数字のみに限定）
    Route::get('/stamp_correction_request/{id}', [AdminCorrectionController::class, 'show'])
        ->where('id', '[0-9]+') // id=list のようなアクセスがここに来るのを防ぐ
        ->name('stamp_correction_request.show');
});

