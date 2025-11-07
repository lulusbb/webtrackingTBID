<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <- import

class SurveyRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'klien_id',
        'status',          // pending|accepted|rejected
        'sent_by',         // user id yg kirim (marketing)
        'sent_at',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'reject_reason',
    ];

    protected $casts = [
        'sent_at'     => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function klien()      { return $this->belongsTo(Klien::class); }
    public function sender()     { return $this->belongsTo(User::class, 'sent_by'); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }
}
