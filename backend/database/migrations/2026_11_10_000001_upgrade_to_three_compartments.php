<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Tambah residu_percent ke bin_capacities
        if (Schema::hasTable('bin_capacities') && !Schema::hasColumn('bin_capacities', 'residu_percent')) {
            Schema::table('bin_capacities', function (Blueprint $table) {
                $table->decimal('residu_percent', 5, 2)->default(0)->after('anorganik_percent');
            });
        }

        // 2. Modifikasi waste_logs untuk menerima enum 'residu'
        // Untuk kompatibilitas SQLite & MySQL/PostgreSQL, ubah menjadi string
        if (Schema::hasTable('waste_logs')) {
            Schema::table('waste_logs', function (Blueprint $table) {
                $table->string('category', 50)->change();
            });
        }

        // 3. Modifikasi capacity_alerts untuk menerima compartment 'residu'
        if (Schema::hasTable('capacity_alerts')) {
            Schema::table('capacity_alerts', function (Blueprint $table) {
                $table->string('compartment', 50)->change();
            });
        }
    }

    public function down(): void {
        if (Schema::hasColumn('bin_capacities', 'residu_percent')) {
            Schema::table('bin_capacities', function (Blueprint $table) {
                $table->dropColumn('residu_percent');
            });
        }
    }
};

