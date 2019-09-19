<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DebitAccount extends Model
{
    //
    protected $fillable=['bank_code','bank_name','bank_suspense_account'];
}
