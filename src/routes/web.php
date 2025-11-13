<?php

use Illuminate\Support\Facades\Route;
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

// 認証が必要なルート
Route::middleware('auth')->group(function () {
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
    Route::get('/attendance/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');

    // 修正申請一覧
    Route::get('/attendance-correction', [AttendanceCorrectionRequestController::class, 'index'])->name('attendance-correction.index');
    
    // 修正申請作成
    Route::get('/attendance-correction/create', [AttendanceCorrectionRequestController::class, 'create'])->name('attendance-correction.create');
    Route::post('/attendance-correction', [AttendanceCorrectionRequestController::class, 'store'])->name('attendance-correction.store');
});

// 管理者用ルート
Route::prefix('admin')->middleware('auth')->group(function () {
    // 日次勤怠一覧
    Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('admin.attendances.index');
    
    // 勤怠詳細（管理者）
    Route::get('/attendances/{id}', [AdminAttendanceController::class, 'show'])->name('admin.attendances.show');
    
    // 勤怠修正（管理者による直接修正）
    Route::put('/attendances/{id}', [AdminAttendanceController::class, 'update'])->name('admin.attendances.update');
    
    // スタッフ一覧
    Route::get('/users', [AdminUserController::class, 'index'])->name('admin.users.index');
    
    // スタッフ別月次勤怠一覧
    Route::get('/users/{user_id}/attendances', [AdminUserController::class, 'attendances'])->name('admin.users.attendances');
    
    // CSV出力
    Route::get('/users/{user_id}/attendances/csv', [AdminUserController::class, 'exportCsv'])->name('admin.users.attendances.csv');
    
    // 修正申請一覧
    Route::get('/corrections', [AdminCorrectionController::class, 'index'])->name('admin.corrections.index');
    
    // 修正申請承認（詳細より前に配置）
    Route::put('/corrections/{id}/approve', [AdminCorrectionController::class, 'approve'])->name('admin.corrections.approve');
    
    // 修正申請詳細
    Route::get('/corrections/{id}', [AdminCorrectionController::class, 'show'])->name('admin.corrections.show');
});
