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
        DB::statement("
            CREATE TABLE transactions (
                id BIGSERIAL,
                khata_id BIGINT NOT NULL,
                user_id BIGINT NOT NULL,
                note VARCHAR(255),
                amount BIGINT NOT NULL,
                attachment VARCHAR(255),
                status VARCHAR(50) NOT NULL,
                type VARCHAR(50) NOT NULL,
                transaction_date DATE NOT NULL,
                due_date DATE,
                is_edited BOOLEAN DEFAULT FALSE,
                edit_count INTEGER DEFAULT 0,
                created_at TIMESTAMP NOT NULL,
                updated_at TIMESTAMP NOT NULL,

                PRIMARY KEY (id, created_at),

                CONSTRAINT transactions_khata_id_fk
                    FOREIGN KEY (khata_id) REFERENCES khatas(id) ON DELETE CASCADE,

                CONSTRAINT transactions_user_id_fk
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) PARTITION BY RANGE (created_at);
        ");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
