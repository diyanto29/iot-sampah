<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('capacity_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('waste_bin_id');
            $table->enum('compartment', ['organik', 'anorganik']);
            $table->decimal('capacity_percent', 5, 2);
            $table->enum('status', ['menunggu', 'ditangani'])->default('menunggu');
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('waste_bin_id')->references('id')->on('waste_bins')->onDelete('cascade');
            $table->index(['waste_bin_id', 'status', 'created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('capacity_alerts');
    }
};
