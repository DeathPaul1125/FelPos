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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('admin')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->unique();
            $table->string('address')->unique();
            $table->string('website')->unique();
            $table->string('logo')->nullable();
            $table->string('nit')->nullable();
            $table->string('user_fel')->nullable();
            $table->string('password_fel')->nullable();
            $table->string('token_fel')->nullable();
            $table->boolean('Produccion')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
