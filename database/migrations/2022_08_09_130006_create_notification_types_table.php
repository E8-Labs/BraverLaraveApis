<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Model\NotificationTypes;

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
            ['id'=> NotificationTypes::TypeMessage, 'name' => 'New Message'],
            ['id'=> NotificationTypes::TypeReservation, 'name' => 'New Reservation'],
            ['id'=> NotificationTypes::InvoicePaid, 'name' => 'Invoice Paid'],
            ['id'=> NotificationTypes::NewUser, 'name' => 'New User'],
            ['id'=> NotificationTypes::AdminBroadcast, 'name' => 'Admin Broadcast'],
            
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
