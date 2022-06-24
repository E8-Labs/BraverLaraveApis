<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportTable extends Migration
{
    public function up()
    {
        Schema::create('report', function (Blueprint $table) {

		$table->integer('reportid',11);
		$table->string('reportedproduct',100);
		$table->string('reportedby',100);
		$table->text('reason');
		$table->timestamp('dateadded')->useCurrent();

        });
    }

    public function down()
    {
        Schema::dropIfExists('report');
    }
}