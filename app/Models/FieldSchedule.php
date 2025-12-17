<?php

namespace App\Models;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldSchedule extends Model
{
    protected $fillable = [
        'field_id',
        'schedule_date',
        'booked',
        'used',
    ];

    public function bookings(): BelongsTo
    {
        return $this->belongsTo(Field::class);
        // return $this->hasMany(Booking::class);
    }
}
