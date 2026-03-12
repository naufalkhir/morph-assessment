<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Entry extends Model
{
    use HasFactory;
    protected $fillable = [
        'description',
        'amount',
        'currency',
        'transaction_date',
    ];
    protected $casts = [
        'amount' => 'float',
        'transaction_date' => 'date:Y-m-d',
    ];
}
