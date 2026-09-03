<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public function record(string $aksi, string $entitas, ?int $entitasId = null, ?array $sebelum = null, ?array $sesudah = null, array $metadata = []): AuditLog
    {
        return AuditLog::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'entitas' => $entitas,
            'entitas_id' => $entitasId,
            'sebelum' => $sebelum,
            'sesudah' => $sesudah,
            'metadata' => $metadata,
            'ip_address' => app()->bound('request') ? app('request')->ip() : null,
        ]);
    }

    public function model(string $aksi, Model $model, ?array $sebelum = null, ?array $sesudah = null, array $metadata = []): AuditLog
    {
        return $this->record($aksi, $model->getTable(), $model->getKey(), $sebelum, $sesudah, $metadata);
    }
}
