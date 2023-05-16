<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('group_message_user_unseen', function (Blueprint $table) {
            $table->unsignedBigInteger('group_message_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['group_message_id', 'user_id']);
            $table->foreign('group_message_id')->references('id')->on('group_messages');
            $table->foreign('user_id')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_message_user_unseen');
    }
};
