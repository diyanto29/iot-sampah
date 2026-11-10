<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('waste_bins', function (Blueprint $table) {
            $table->string('id')->primary(); // e.g. 'BIN-01'
            $table->string('location_name');
            $table->decimal('bin_height_cm', 5, 2)->default(30.0);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_active_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('waste_bins');
    }
};

