<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatusersTable extends Migration
{
    public function up()
    {
        Schema::create('chatusers', function (Blueprint $table) {

		$table->integer('chatuserid',11);
		$table->string('userid',50);
		$table->string('chatid',100);
		$table->timestamp('dateadded')->useCurrent();
		$table->string('role',50);
		$table->integer('unreadcount')->default(0);

        });
    }

    public function down()
    {
        Schema::dropIfExists('chatusers');
    }
}