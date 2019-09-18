<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('batch_split_id');
            $table->string('payment_info_id');
            $table->string('record_id')->unique();
            $table->string('initiator');
            $table->string('debiting_agent');
            $table->string('debit_account');
            $table->string('amount');
            $table->string('currency');
            $table->string('payment_method');
            $table->string('beneficiary_name');
            $table->string('beneficiary_account');
            $table->string('crediting_agent');
            $table->string('reference');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('records');
    }
}
