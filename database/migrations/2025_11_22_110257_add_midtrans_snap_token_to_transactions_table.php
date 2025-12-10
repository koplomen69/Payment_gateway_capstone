<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Tambahkan kolom snap_token setelah midtrans_order_id
            if (!Schema::hasColumn('transactions', 'midtrans_snap_token')) {
                $table->text('midtrans_snap_token')->nullable()->after('midtrans_order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('midtrans_snap_token');
        });
    }
};
