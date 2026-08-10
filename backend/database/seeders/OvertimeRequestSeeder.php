<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OvertimeRequest;
use App\Models\User;
use Carbon\Carbon;

class OvertimeRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->info('No users found. Please seed users first.');
            return;
        }

        $statuses = ['pending', 'approved', 'rejected'];
        $reasons = [
            'Menyelesaikan laporan bulanan yang mendesak.',
            'Deployment sistem HRIS ke server produksi.',
            'Persiapan materi presentasi untuk meeting dengan klien.',
            'Backup data server mingguan.',
            'Pengecekan stok barang di gudang.',
            'Menyelesaikan desain UI/UX untuk fitur baru.',
        ];

        foreach ($users as $user) {
            // Give each user 2 to 4 overtime requests
            $numRequests = rand(2, 4);

            for ($i = 0; $i < $numRequests; $i++) {
                // Generate a random past date within the last 30 days
                $date = Carbon::now()->subDays(rand(1, 30));
                
                // Typical overtime is after work hours
                $startHour = rand(17, 19);
                $endHour = $startHour + rand(1, 4);
                
                $startTime = Carbon::createFromTime($startHour, 0, 0);
                $endTime = Carbon::createFromTime($endHour, 0, 0);
                
                $status = $statuses[array_rand($statuses)];
                $adminNote = null;

                if ($status === 'approved') {
                    $adminNote = 'Lembur disetujui, tolong pastikan hasilnya di-upload ke sistem.';
                } elseif ($status === 'rejected') {
                    $adminNote = 'Harap diselesaikan pada jam kerja normal.';
                }

                OvertimeRequest::create([
                    'user_id' => $user->id,
                    'date' => $date->format('Y-m-d'),
                    'start_time' => $startTime->format('H:i:s'),
                    'end_time' => $endTime->format('H:i:s'),
                    'total_duration' => $endHour - $startHour,
                    'reason' => $reasons[array_rand($reasons)],
                    'status' => $status,
                    'admin_note' => $adminNote,
                ]);
            }
        }
    }
}
