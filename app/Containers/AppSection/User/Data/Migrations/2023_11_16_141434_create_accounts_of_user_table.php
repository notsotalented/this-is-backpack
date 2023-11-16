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
        Schema::create('accounts_of_user', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('password')->nullable();
            $table->string('type');
            $table->integer('money');
            $table->foreignId('belongs_to')->constrained('users', 'id', 'account_user_id')->cascadeOnDelete();
            $table->timestamps();
            //$table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts_of_user');
    }
};
