<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'start_datetime',
        'end_datetime',
        'location',
        'color',
        'is_working_day',
        'is_all_day',
        'is_recurring',
        'recurrence_rule',
        'visibility',
        'created_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_working_day' => 'boolean',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'calendar_event_user');
    }

    public function divisions()
    {
        return $this->belongsToMany(Division::class, 'calendar_event_division');
    }
}
