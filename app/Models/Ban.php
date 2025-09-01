<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ban extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'admin_id', 'reason', 'banned_until'];

    // العلاقة مع المستخدم المحظور
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // العلاقة مع الأدمن الذي قام بالحظر
    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
