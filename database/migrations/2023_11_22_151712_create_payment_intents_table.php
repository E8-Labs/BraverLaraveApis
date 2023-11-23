<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();
            $table->string("payment_intent_id")->nullable();
            $table->string("mode")->default("test");
            $table->string("next_action")->nullable();
            $table->string("userid");
            $table->string("payment_method")->nullable();
            $table->string("webhook_action")->default("");
            $table->string("reservation_id")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
