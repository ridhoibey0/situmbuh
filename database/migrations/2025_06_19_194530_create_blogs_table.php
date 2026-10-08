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
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // Judul blog
            $table->text('content'); // Konten blog
            $table->string('slug')->unique(); // Slug untuk URL
            $table->string('image')->nullable(); // Path gambar
            $table->boolean('is_published')->default(false); // Status publikasi
            $table->timestamp('published_at')->nullable(); // Tanggal publikasi
            $table->foreignId('author_id')->constrained('users')->onDelete('cascade'); // Relasi ke pengguna (author)
            $table->timestamps(); // Timestamps created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
