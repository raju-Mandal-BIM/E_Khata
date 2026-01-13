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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('khata_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('note')->nullable();
            $table->bigInteger('amount');
            $table->string('attachment')->nullable();
            $table->string('status');//synced , not synced,edited
            $table->string('type');
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->boolean('is_edited')->default(0);
            $table->integer('edit_count')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
