<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    //
protected $fillable=[
   'batch_split_id',
    'payment_info_id',
    'record_id',
    'initiator',
    'debiting_agent',
    'debit_account',
    'amount',
    'currency',
    'payment_method',
    'beneficiary_name',
    'beneficiary_account',
    'crediting_agent',
    'reference',
    'naration',
    'response'
];
}
