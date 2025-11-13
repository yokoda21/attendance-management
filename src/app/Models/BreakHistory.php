<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BreakModel;

class BreakHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'break_id',
        'changed_by',
        'changed_type',
        'old_break_start',
        'new_break_start',
        'old_break_end',
        'new_break_end',
        'reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'changed_type' => 'integer',
        'old_break_start' => 'datetime',
        'new_break_start' => 'datetime',
        'old_break_end' => 'datetime',
        'new_break_end' => 'datetime',
    ];

    /**
     * 変更タイプ定数
     */
    const TYPE_APPROVED_REQUEST = 0;    // 申請承認
    const TYPE_ADMIN_EDIT = 1;          // 管理者直接修正

    /**
     * 履歴が属する休憩記録
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function break()
    {
        return $this->belongsTo(BreakModel::class);
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
