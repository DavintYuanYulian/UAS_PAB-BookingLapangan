<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'field_schedule_id',
        'customer_name',
        'is_booked',
        'is_used',
        'email',
        'channel',
    ];

    protected static function booted()
    {
        static::creating(function ($booking) {
            if (empty($booking->unique_code)) {
                $booking->unique_code = (string) Str::uuid();
            }
        });
    }

    public function getOrderIdAttribute()
    {
        $order_date = new Carbon($this->created_at);
        return $order_date->year
            . str_pad($order_date->month, 2, "0", STR_PAD_LEFT)
            . str_pad($order_date->day, 2, "0", STR_PAD_LEFT)
            . str_pad($this->id, 5, "0", STR_PAD_LEFT);
    }
}
