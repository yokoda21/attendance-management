<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'user_id',
        'clock_in',
        'clock_out',
        'note',
        'status',
        'approved_by',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'status' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * ステータス定数
     */
    const STATUS_PENDING = 0;       // 承認待ち
    const STATUS_APPROVED = 1;      // 承認済み

    /**
     * 修正申請が属する勤怠記録
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 修正申請を作成したユーザー
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 修正申請を承認したユーザー
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * 修正申請に含まれる休憩修正
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function breakCorrections()
    {
        return $this->hasMany(BreakCorrection::class, 'correction_request_id');
    }

    /**
     * ステータス名を取得
     *
     * @return string
     */
    public function getStatusNameAttribute()
    {
        $statuses = [
            self::STATUS_PENDING => '承認待ち',
            self::STATUS_APPROVED => '承認済み',
        ];

        return $statuses[$this->status] ?? '不明';
    }

    /**
     * 承認済みかどうかを判定
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * 承認待ちかどうかを判定
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::STATUS_PENDING;
    }
}
