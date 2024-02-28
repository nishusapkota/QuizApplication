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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('question_categories');
            $table->string("title");
            $table->string("slug");
            $table->text("description");
            $table->json("options");
            $table->text("answer");
            $table->enum("weightage",[5,10,15])->default(5);
            $table->boolean('status')->default(1);
            $table->softDeletes();
            $table->timestamps();
            $table->unique(['slug','deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
