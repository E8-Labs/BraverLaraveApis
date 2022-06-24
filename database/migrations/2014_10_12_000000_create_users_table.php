<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('userid',20);
            $table->string('name',30);
            $table->string('email',40);
            $table->string('phone',20);
            $table->string('dob',15);
            $table->string('gender',20)->nullable()->default('NULL');
            $table->string('password',16);
            $table->timestamp('dateadded')->useCurrent();
            $table->string('role',30);
            $table->string('fcmtoken',1000);
            $table->string('accountstatus',30)->default('Pending');
            $table->string('url',50);
            $table->string('myinvitecode',5);
            $table->string('invitedbycode',5);
            $table->boolean('deleted')->default(false);
            $table->string('stripecustomerid',50);
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
};
