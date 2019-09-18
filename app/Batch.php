<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Batch extends Model
{
    //
protected $fillable = ['batch_split_id','date','transactions','total','initiator','payment_method'];
}
