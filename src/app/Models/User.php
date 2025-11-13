<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => 'integer',
    ];

    /**
     * 管理者かどうかを判定
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->role === 1;
    }

    /**
     * ユーザーの勤怠記録
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * ユーザーが作成した勤怠修正申請
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceCorrectionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    /**
     * ユーザーが承認した勤怠修正申請
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function approvedCorrectionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class, 'approved_by');
    }

    /**
     * ユーザーが変更した勤怠履歴
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function attendanceHistories()
    {
        return $this->hasMany(AttendanceHistory::class, 'changed_by');
    }

    /**
     * ユーザーが変更した休憩履歴
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function breakHistories()
    {
        return $this->hasMany(BreakHistory::class, 'changed_by');
    }
}
