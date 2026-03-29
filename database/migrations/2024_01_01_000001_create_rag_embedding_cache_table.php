<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rag_embedding_cache', function (Blueprint $table) {
            $table->id();
            $table->string('hash', 64)->unique();
            $table->longText('embedding');
            $table->string('text_preview', 200)->nullable();
            $table->timestamps();

            $table->index('hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rag_embedding_cache');
    }
};
