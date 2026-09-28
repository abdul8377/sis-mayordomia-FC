<?php

namespace App\Support\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class Audit
{
    public static function record(string $action, Model $entity, array $changes = [], ?int $actorId = null): void
    {
        $safe = array_diff_key($changes, array_flip(['password', 'encrypted_content', 'content', 'remember_token']));
        DB::table('audit_logs')->insert(['actor_user_id' => $actorId ?? auth()->id(), 'action' => $action, 'entity_type' => $entity->getTable(), 'entity_id' => $entity->getKey(), 'changes' => json_encode($safe, JSON_THROW_ON_ERROR), 'occurred_at' => now(), 'request_id' => (string) Str::uuid(), 'created_at' => now(), 'updated_at' => now()]);
    }
}
