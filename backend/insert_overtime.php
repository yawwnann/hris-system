<?php
use App\Models\User;
use App\Models\OvertimeRequest;
use Carbon\Carbon;

$users = User::take(3)->get();

if ($users->isEmpty()) {
    echo "No users found!\n";
    exit;
}

$statuses = ['pending', 'approved', 'rejected'];

foreach ($users as $index => $user) {
    for ($i = 0; $i < 3; $i++) {
        $date = Carbon::now()->subDays(rand(1, 15))->toDateString();
        $startTime = Carbon::createFromTime(17, 0, 0);
        $endTime = (clone $startTime)->addHours(rand(2, 4));
        $duration = $startTime->diffInHours($endTime);

        $status = $statuses[array_rand($statuses)];

        OvertimeRequest::create([
            'user_id' => $user->id,
            'date' => $date,
            'start_time' => $startTime->toTimeString(),
            'end_time' => $endTime->toTimeString(),
            'total_duration' => $duration,
            'reason' => 'Penyelesaian target proyek dan bug fixing sprint ' . rand(10, 20),
            'status' => $status,
            'admin_note' => $status === 'rejected' ? 'Alasan kurang jelas' : ($status === 'approved' ? 'Silahkan diproses' : null),
        ]);
    }
}
echo "Overtime data inserted successfully!\n";
