<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BreakModel;

class Attendance extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'status' => 'integer',
    ];

    /**
     * ステータス定数
     */
    const STATUS_OFF_WORK = 0;      // 勤務外
    const STATUS_CLOCKED_IN = 1;    // 出勤中
    const STATUS_ON_BREAK = 2;      // 休憩中
    const STATUS_CLOCKED_OUT = 3;   // 退勤済

    /**
     * 勤怠記録が属するユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 勤怠記録の休憩時間
     */
    public function breaks()
    {
        return $this->hasMany(BreakModel::class);
    }

    /**
     * 勤怠記録の修正申請
     */
    public function correctionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    /**
     * 勤怠記録の変更履歴
     */
    public function histories()
    {
        return $this->hasMany(AttendanceHistory::class);
    }

    /**
     * ステータス名を取得
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            self::STATUS_CLOCKED_IN => '出勤中',
            self::STATUS_ON_BREAK => '休憩中',
            self::STATUS_CLOCKED_OUT => '退勤済',
        ];

        return $statuses[$this->status] ?? '不明';
    }

    /**
     * 総勤務時間を計算（分単位）
     */
    public function getTotalWorkMinutesAttribute()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return null;
        }

        $totalMinutes = $this->clock_in->diffInMinutes($this->clock_out);
        $breakMinutes = $this->breaks->sum(function ($break) {
            if (!$break->break_start || !$break->break_end) {
                return 0;
            }
            return $break->break_start->diffInMinutes($break->break_end);
        });

        return $totalMinutes - $breakMinutes;
    }
}
