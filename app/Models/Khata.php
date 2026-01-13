<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Khata extends Model
{
   protected $fillable = [
    'user_id',
    'type',
    'name',
    'phone',
    'synced_at',
    'address',
    'email',
    'color',
    'total_amount',
    'received_amount',
    'due_amount',
    'last_transaction_note',
    'last_transaction_date',
    'last_transaction_type',
    'country_code_id',
];

}
