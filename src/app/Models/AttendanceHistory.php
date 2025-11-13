<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'changed_by',
        'changed_type',
        'old_clock_in',
        'new_clock_in',
        'old_clock_out',
        'new_clock_out',
        'reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'changed_type' => 'integer',
        'old_clock_in' => 'datetime',
        'new_clock_in' => 'datetime',
        'old_clock_out' => 'datetime',
        'new_clock_out' => 'datetime',
    ];

    /**
     * 変更タイプ定数
     */
    const TYPE_APPROVED_REQUEST = 0;    // 申請承認
    const TYPE_ADMIN_EDIT = 1;          // 管理者直接修正

    /**
     * 履歴が属する勤怠記録
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 変更を行ったユーザー
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function changer()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * 変更タイプ名を取得
     *
     * @return string
     */
    public function getChangedTypeNameAttribute()
    {
        $types = [
            self::TYPE_APPROVED_REQUEST => '申請承認',
            self::TYPE_ADMIN_EDIT => '管理者直接修正',
        ];

        return $types[$this->changed_type] ?? '不明';
    }
}
