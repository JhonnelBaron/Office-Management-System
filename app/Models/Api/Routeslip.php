<?php

namespace App\Models\Api;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Routeslip extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'routeslip_no',
        'r_subject',
        'assigned_focal_user_id',
        'assigned_focal_name',
        'r_instructions',
        'reference',
        'status',
        'is_read',
        'r_action_taken',
        'r_action_taken_date',
        'r_action_taken_time',
        'r_remarks',
        'urgency',
        'r_drafts',
        'r_scanned_copy',
    ];

    /**
     * Optional: Relationship sa User model.
     * Para makuha mo yung full details ng focal person.
     */
    public function focalUser()
    {
        return $this->belongsTo(User::class, 'assigned_focal_user_id');
    }
}