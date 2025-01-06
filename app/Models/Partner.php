<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'type',
        'status',
        'commission_rate',
        'notes'
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2'
    ];

    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }
}
