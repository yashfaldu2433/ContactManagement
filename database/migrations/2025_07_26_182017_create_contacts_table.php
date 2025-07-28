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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->index();
            $table->string('last_name')->nullable();
            $table->string('email')->index();
            $table->string('phone');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->unsignedBigInteger('profile_image_id')->nullable();
            $table->foreign('profile_image_id')->references('id')->on('media');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['profile_image_id']);
        });
        Schema::dropIfExists('contacts');
    }
};
