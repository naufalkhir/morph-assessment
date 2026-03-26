<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    // HasFactory — allows us to use Entry::factory() for testing and seeding
    use HasFactory;

    // $fillable — whitelist of columns that can be mass assigned
    // Prevents Mass Assignment vulnerability — only these fields
    // can be filled via Entry::create() or $entry->update()
    protected $fillable = [
        'description',
        'amount',
        'currency',
        'transaction_date',
    ];

    // $casts — automatically converts database values to PHP types
    // amount: stored as DECIMAL in MySQL, cast to float in PHP
    // transaction_date: stored as DATE in MySQL, cast to Y-m-d string
    // always get consistent types without manual conversion
    protected $casts = [
        'amount' => 'float',
        'transaction_date' => 'date:Y-m-d',
    ];
}
