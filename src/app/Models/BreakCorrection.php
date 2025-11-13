<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'correction_request_id',
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
     * 休憩修正が属する修正申請
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function correctionRequest()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class, 'correction_request_id');
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
}
