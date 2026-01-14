<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
 protected $fillable = [
        'khata_id',
        'user_id',
        'note',
        'amount',
        'attachment',
        'status',
        'type',
        'transaction_date',
        'due_date',
        'is_edited',
        'edit_count',
    ];
}
