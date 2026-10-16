<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Картинки, загруженные в админке. Файловая система Vercel только для чтения,
        // поэтому сжатые WebP хранятся прямо в базе (base64).
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->longText('data');
            $table->unsignedInteger('size');
            $table->timestamps();
        });

        // Публикации из Instagram и добавленные вручную работы
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('kind')->default('photo'); // photo | reel | carousel
            $table->string('category')->default('tattoo'); // tattoo | piercing | studio | life
            $table->string('shortcode')->nullable()->unique();
            $table->text('caption')->nullable();
            $table->string('cover')->nullable();
            $table->json('images')->nullable();
            $table->string('video_url')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('comments')->default(0);
            $table->unsignedInteger('duration')->nullable();
            $table->timestamp('taken_at')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        // Актуальные (highlights) и сторис
        Schema::create('highlights', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('cover')->nullable();
            $table->json('items')->nullable(); // [{type: image|video, src}]
            $table->boolean('is_visible')->default(true);
            $table->integer('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('contact');
            $table->string('service')->default('tattoo');
            $table->string('placement')->nullable();
            $table->string('size')->nullable();
            $table->text('idea')->nullable();
            $table->string('status')->default('new'); // new | contacted | done | cancelled
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('highlights');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('media');
        Schema::dropIfExists('settings');
    }
};
