<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatTable extends Migration
{
    public function up()
    {
        Schema::create('chat', function (Blueprint $table) {

		$table->string('chatid',50);
		$table->string('productid',50);
		$table->timestamp('dateadded')->useCurrent();
		$table->timestamp('updatedat')->nullable()->useCurrent();
		$table->string('fromuserid',50);
		$table->text('lastmessage');
		$table->string('chattype',100);
		$table->string('chatforproduct',50);
		$table->string('customaddress',100);

        });
    }

    public function down()
    {
        Schema::dropIfExists('chat');
    }
}