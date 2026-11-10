<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->string('waste_bin_id');
            $table->enum('category', ['organik', 'anorganik']);
            $table->decimal('confidence', 4, 2);
            $table->timestamp('sorted_at')->useCurrent();

            $table->foreign('waste_bin_id')->references('id')->on('waste_bins')->onDelete('cascade');
            $table->index(['waste_bin_id', 'category', 'sorted_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('waste_logs');
    }
};

