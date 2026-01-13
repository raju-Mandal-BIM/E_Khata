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
        Schema::create('khatas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('individual');//group or individual
            $table->string('name');
            $table->bigInteger('phone')->unique();
            $table->date('synced_at');
            $table->string('address')->nullable();
            $table->string('email')->nullable();
            $table->string('color')->nullable();
            $table->bigInteger('total_amount')->default(0);
            $table->bigInteger('received_amount')->default(0);
            $table->bigInteger('due_amount')->default(0);
            $table->string('last_transaction_note')->nullable();
            $table->date('last_transaction_date')->nullable();
            $table->string('last_transaction_type')->nullable();
            $table->foreignId('country_code_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('khatas');
    }
};
