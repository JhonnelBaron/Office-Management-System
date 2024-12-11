<?php

namespace App\Models;

use App\Models\Employee\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentLink extends Model
{
    use HasFactory;

    
    protected $fillable = [
        'user_id',
        'task_id',
        'document_link',
    ];

    public function tasks()
    {
        return $this->belongsTo(Task::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
