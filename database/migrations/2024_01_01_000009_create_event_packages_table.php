<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('event_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('award_id')->constrained()->onDelete('cascade');
            $table->enum('package_type', ['nomination', 'majesty', 'sovereign', 'monarch']);
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->json('benefits')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('event_packages');
    }
};
