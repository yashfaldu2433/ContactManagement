<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_document', function (Blueprint $table) {
            $table->unsignedBigInteger('contact_id');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');

            $table->unsignedBigInteger('document_id');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('contact_document', function (Blueprint $table) {
            $table->dropForeign(['contact_id']);
            $table->dropForeign(['document_id']);
        });

        Schema::dropIfExists('contact_document');
    }
};
