<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KlienSurvei extends Model
{
    protected $table = 'klien_survei';
    protected $fillable = ['klien_id','approved_by','approved_at'];

    public function klien()    { return $this->belongsTo(Klien::class); }
    public function approver() { return $this->belongsTo(User::class,'approved_by'); }
}
