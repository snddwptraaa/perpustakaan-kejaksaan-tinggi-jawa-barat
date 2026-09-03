<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'aksi', 'entitas', 'entitas_id', 'sebelum', 'sesudah', 'metadata', 'ip_address'];

    protected function casts(): array
    {
        return ['sebelum' => 'array', 'sesudah' => 'array', 'metadata' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
