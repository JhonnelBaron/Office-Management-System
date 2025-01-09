<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Login extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'time_in',
        'time_out',
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

    public function getTimeInAttribute($value)
    {
        return Carbon::parse($value)->format('h:i A');
    }

    public function getDateAttribute($value)
    {
        return Carbon::parse($value)->format('m-d-Y');
    }

    public function getDayOfWeekAttribute()
    {
        // Calculate the day of the week based on the original 'date' value
        return Carbon::parse($this->attributes['date'])->format('l');
    }
}
