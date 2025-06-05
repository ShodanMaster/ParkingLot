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
        Schema::create('allocates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations');
            $table->string('vehicle_number');
            $table->enum('status',['IN', 'OUT'])->default('IN');
            $table->timestamp('in_time')->useCurrent();
            $table->timestamp('out_time')->nullable();
            $table->string('qrcode')->unique();
            $table->timestamps();

            $table->index('location_id');
            $table->index('vehicle_number');
            $table->index('status');
            $table->index('in_time');
            $table->index('out_time');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allocates');
    }
};
