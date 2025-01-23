<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DossierRejectionReport extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'dossier_id',
        'created_by',
        'content',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function dossier()
    {
        return $this->belongsTo(Dossier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
