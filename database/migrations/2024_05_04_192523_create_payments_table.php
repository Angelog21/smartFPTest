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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('origin_id');
            $table->foreignUuid('receiver_id');
            $table->string('payment_ms');
            $table->string('client_name');
            $table->string('cpf');
            $table->string('description');
            $table->decimal('amount', 13, 2);
            $table->enum('status',['pendiente','pagado','vencido','fallido']);
            $table->dateTime('payment_date');
            $table->timestamps();

            $table->foreign('origin_id')->references('id')->on('users');
            $table->foreign('receiver_id')->references('id')->on('users');
            $table->foreign('payment_ms')->references('slug')->on('payment_methods');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
