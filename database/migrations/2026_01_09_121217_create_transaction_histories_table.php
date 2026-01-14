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
        Schema::create('transaction_histories', function (Blueprint $table) {
            $table->id();
          
            $table->foreign(['id', 'created_at'])->references(['id', 'created_at'])->on('transactions')->onDelete('cascade');
            $table->integer('edit_number')->default(0);
            $table->string('old_note')->nullable();
            $table->string('new_note')->nullable();
            $table->bigInteger('new_amount')->default(0);
            $table->bigInteger('old_amount')->default(0);
            $table->string('old_type')->nullable();
            $table->string('new_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_histories');
    }
};
