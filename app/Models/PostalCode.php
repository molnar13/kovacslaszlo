<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostalCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'city_id'];

    protected $with = ['city.county'];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }
}