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
        Schema::create('transactions_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_account')->constrained('accounts_of_user', 'id', 'transaction_from_account_id')->cascadeOnDelete();
            $table->foreignId('to_account')->constrained('accounts_of_user', 'id', 'transaction_to_account_id')->cascadeOnDelete();
            $table->string('type');
            $table->integer('money');
            $table->boolean('is_completed')->default(false)->nullable();
            $table->timestamps();
            //$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions_of_accounts');
    }
};
