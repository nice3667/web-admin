<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
        });

        // อัพเดตข้อมูลจาก backup
        $this->updateClientUserIds();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * อัพเดต user_id จากข้อมูล backup
     */
    private function updateClientUserIds()
    {
        // อ่านข้อมูลจาก backup file
        $backupFile = storage_path('backups/clients_backup_2025-06-19_19-21-52.json');
        
        if (!file_exists($backupFile)) {
            return;
        }

        $backupData = json_decode(file_get_contents($backupFile), true);
        
        if (!$backupData) {
            return;
        }

        // สร้าง map ของ client_uid กับ user_id
        $clientUserMap = [];
        foreach ($backupData as $client) {
            if (isset($client['client_uid']) && isset($client['user_id'])) {
                $clientUserMap[$client['client_uid']] = $client['user_id'];
            }
        }

        // อัพเดตข้อมูลในตาราง clients
        $updatedCount = 0;
        foreach ($clientUserMap as $clientUid => $userId) {
            $result = DB::table('clients')
                ->where('client_uid', $clientUid)
                ->update(['user_id' => $userId]);
            
            if ($result > 0) {
                $updatedCount++;
            }
        }

        // Log ผลลัพธ์
        \Log::info("Updated {$updatedCount} clients with user_id from backup data");
    }
};
