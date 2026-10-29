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
        Schema::table('periksa', function (Blueprint $table) {
            $table->string('status_bayar', 30)->default('belum_bayar')->after('biaya_periksa')->index();
            $table->string('bukti_bayar')->nullable()->after('status_bayar');
            $table->timestamp('tgl_upload_bukti')->nullable()->after('bukti_bayar');
            $table->timestamp('tgl_verifikasi')->nullable()->after('tgl_upload_bukti');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            $table->dropIndex(['status_bayar']);
            $table->dropColumn(['status_bayar', 'bukti_bayar', 'tgl_upload_bukti', 'tgl_verifikasi']);
        });
    }
};
