<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'settlement_id', 'county_id'];

    public function settlement()
    {
        return $this->belongsTo(Settlement::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }
}