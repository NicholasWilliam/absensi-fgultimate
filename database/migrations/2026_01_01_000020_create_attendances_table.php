<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['in', 'out']);
            $table->string('photo_path');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('distance_meters')->nullable();
            $table->boolean('within_radius')->default(false);
            $table->enum('status', ['tepat_waktu', 'telat', 'lebih_awal', 'tidak_ada_jadwal'])->default('tidak_ada_jadwal');
            $table->dateTime('server_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
