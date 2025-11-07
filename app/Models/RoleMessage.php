<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleMessage extends Model
{
    protected $table = 'role_messages';

    protected $fillable = [
        'room',
        'sender_id',
        'sender_role',
        'sender_name',
        'recipient_role',
        'body',
        'seen_at',
    ];

    protected $casts = [
        'seen_at'   => 'datetime',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];

    /**
     * Buat key room yang konsisten untuk sepasang role.
     * Ex: admin + marketing => admin|marketing (urut alfabet).
     */
    public static function makeRoom(string $a, string $b): string
    {
        $pair = [strtolower(trim($a)), strtolower(trim($b))];
        sort($pair);
        return implode('|', $pair);
    }
}
