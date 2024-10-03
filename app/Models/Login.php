<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'time_in',
        'date',
        'status',
        'allowance',
        'score',
        'validation',
        'valdiated_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
