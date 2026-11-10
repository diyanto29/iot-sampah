<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bin_capacities', function (Blueprint $table) {
            $table->id();
            $table->string('waste_bin_id');
            $table->decimal('organik_percent', 5, 2);
            $table->decimal('anorganik_percent', 5, 2);
            $table->timestamp('recorded_at')->useCurrent();

            $table->foreign('waste_bin_id')->references('id')->on('waste_bins')->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('bin_capacities');
    }
};

