<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    protected $fillable = ['name', 'type', 'price'];

    public function schedules(): HasMany
    {
        return $this->hasMany(FieldSchedule::class);
    }
}
