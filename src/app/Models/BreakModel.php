<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakModel extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'breaks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'attendance_id',
        'break_start',
        'break_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'break_start' => 'datetime',
        'break_end' => 'datetime',
    ];

    /**
     * 休憩が属する勤怠記録
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * 休憩の変更履歴
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function histories()
    {
        return $this->hasMany(BreakHistory::class);
    }

    /**
     * 休憩時間を計算（分単位）
     *
     * @return int|null
     */
    public function getBreakMinutesAttribute()
    {
        if (!$this->break_start || !$this->break_end) {
            return null;
        }

        return $this->break_start->diffInMinutes($this->break_end);
    }

    /**
     * 休憩中かどうかを判定
     *
     * @return bool
     */
    public function isOnBreak()
    {
        return $this->break_start !== null && $this->break_end === null;
    }
}
