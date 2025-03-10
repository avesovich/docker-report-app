<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news_rss', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('link')->unique();
            $table->text('description')->nullable();
            $table->string('pubDate');
            $table->string('author')->nullable();
            $table->string('category')->nullable();
            $table->string('image')->nullable();
            $table->string('source')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news_rss');
    }
};
