<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('item_id')
                ->unique()
                ->constrained()
                ->restrictOnDelete();

            $table->timestamp('spun_at');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spins');
    }
};
