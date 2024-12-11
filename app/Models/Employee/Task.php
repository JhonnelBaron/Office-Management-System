<?php

namespace App\Models\Employee;

use App\Models\DocumentLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'link',
        'status',
        'date_added',
        'date_finished',
        'hours_worked',
        'pending_days',
        'paps',
        'type',
        'task',
        'no_of_document',
        'time_suspended',
        'time_continued',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
    public function documentLinks()
    {
        return $this->hasMany(DocumentLink::class, 'task_id');
    }
}

