<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTable extends Migration
{
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {

		$table->string('invoice_id',200);
		;
		$table->string('invoice_by',20);
		$table->string('reservation_id',50);
		$table->string('crypto_charge_code',50);
		$table->string('crypto_charge_id',200);
		$table->string('crypto_charge_url',200);
		$table->string('payment_status',30)->default('NEW');
		$table->string('timeline_status',50)->default('NEW');

        });
    }

    public function down()
    {
        Schema::dropIfExists('invoice');
    }
}