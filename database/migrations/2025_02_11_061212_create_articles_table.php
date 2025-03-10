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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Add this line
            $table->date('publication_date');
            $table->date('posted_date');
            $table->time('time_posted');
            $table->string('title', 255);
            $table->string('type_of_report'); 
            $table->string('url', 2083);
            $table->string('editor_name');
            $table->string('approval_status')->default('Review');
            $table->mediumText('detailed_summary');
            $table->mediumText('analysis');
            $table->mediumText('recommendation');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
        
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article');
    }
};
