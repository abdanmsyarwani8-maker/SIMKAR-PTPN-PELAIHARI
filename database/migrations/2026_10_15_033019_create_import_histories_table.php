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
        Schema::create('import_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_name');
            $table->integer('inserted')->default(0);
            $table->integer('updated')->default(0);
            $table->integer('family_processed')->default(0);
            $table->integer('family_inserted')->default(0);
            $table->integer('family_updated')->default(0);
            $table->integer('skipped')->default(0);
            $table->json('sheet_results')->nullable();
            $table->json('skipped_details')->nullable();
            $table->json('errors')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('import_histories');
    }
};
