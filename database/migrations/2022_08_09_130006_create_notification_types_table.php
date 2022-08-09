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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        \DB::table('notification_types')->insert([
            ['id'=> NotificationType::TypeMessage, 'name' => 'New Message'],
            ['id'=> NotificationType::TypeReservation, 'name' => 'New Reservation'],
            ['id'=> NotificationType::InvoicePaid, 'name' => 'Invoice Paid'],
            ['id'=> NotificationType::NewUser, 'name' => 'New User'],
            ['id'=> NotificationType::AdminBroadcast, 'name' => 'Admin Broadcast'],
            
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_types');
    }
};
