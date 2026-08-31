<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use App\Models\Position;
use App\Models\Shift;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\WorkCalendar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // Create Divisions
        $divisions = [];
        $divNames = ['IT & Engineering', 'Human Resources', 'Finance & Tax', 'Marketing', 'Sales & BD', 'Operations'];
        foreach ($divNames as $name) {
            $divisions[] = Division::firstOrCreate(['name' => $name]);
        }

        // Create Positions
        $positions = [];
        $posNames = ['Manager', 'Senior Staff', 'Staff', 'Intern', 'Supervisor'];
        foreach ($posNames as $name) {
            $positions[] = Position::firstOrCreate(['name' => $name], ['level' => rand(1, 5)]);
        }

        // Create Default Shift
        $shift = Shift::firstOrCreate(
            ['name' => 'Regular Shift'],
            [
                'time_in' => '09:00:00',
                'time_out' => '17:00:00',
            ]
        );

        // Create Admins
        User::updateOrCreate(
            ['email' => 'admin@hris.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'nik' => 'ADM-001',
                'status' => 'active',
                'division_id' => $divisions[1]->id,
                'position_id' => $positions[0]->id,
                'shift_id' => $shift->id,
                'leave_quota' => 12,
            ]
        );

        // Create 100 Employees
        $users = [];
 
        for ($i = 1; $i <= 100; $i++) {
            $users[] = User::updateOrCreate(
                ['email' => "employee{$i}@hris.com"],
                [
                    'name' => $faker->name,
                    'password' => Hash::make('password'),
                    'role' => 'employee',
                    'nik' => sprintf(
                        '%06d%02d%02d%04d',
                        rand(340201, 340499),
                        rand(1, 31),
                        rand(1, 12),
                        $i + 1000
                    ),
                    'status' => $faker->boolean(90) ? 'active' : 'inactive',
                    'division_id' => $faker->randomElement($divisions)->id,
                    'position_id' => $faker->randomElement($positions)->id,
                    'shift_id' => $shift->id,
                    'leave_quota' => 12,
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'dob' => $faker->dateTimeBetween('-40 years', '-20 years'),
                    'join_date' => $faker->dateTimeBetween('-3 years', 'now'),
                ]
            );
        }

        // Seed Work Calendar (Upcoming Events)
        $events = ['Monthly Meeting', 'Company Outing', 'Townhall', 'Client Visit', 'Training Session'];
        for ($i = 0; $i < 5; $i++) {
            WorkCalendar::create([
                'date' => Carbon::today()->addDays(rand(1, 14))->format('Y-m-d'),
                'type' => 'company_holiday',
                'description' => $faker->randomElement($events) . ' ' . $faker->time('H:i'),
            ]);
        }

        // Seed Attendance for last 7 days + today for ACTIVE employees
        $activeUsers = array_filter($users, fn($u) => $u->status === 'active');
        
        for ($i = 7; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            
            // Skip weekends
            if (Carbon::parse($date)->isWeekend()) {
                continue;
            }

            foreach ($activeUsers as $u) {
                // Random status
                $rand = rand(1, 100);
                if ($rand <= 75) { // 75% present
                    $status = 'present';
                    $in = '08:' . str_pad(rand(30, 59), 2, '0', STR_PAD_LEFT) . ':00';
                } elseif ($rand <= 90) { // 15% late
                    $status = 'late';
                    $in = '09:' . str_pad(rand(05, 59), 2, '0', STR_PAD_LEFT) . ':00';
                } else { // 10% absent/leave
                    continue; 
                }

                $out = '17:' . str_pad(rand(00, 30), 2, '0', STR_PAD_LEFT) . ':00';

                Attendance::create([
                    'user_id' => $u->id,
                    'date' => $date,
                    'time_in' => $in,
                    'time_out' => $out,
                    'status' => $status,
                    'total_hours' => 8,
                ]);
            }
        }

        // Seed some Leaves
        for ($i = 0; $i < 20; $i++) {
            $u = $faker->randomElement($activeUsers);
            LeaveRequest::create([
                'user_id' => $u->id,
                'type' => $faker->randomElement(['annual', 'sick', 'permission']),
                'start_date' => Carbon::today()->addDays(rand(-5, 5))->format('Y-m-d'),
                'end_date' => Carbon::today()->addDays(rand(5, 10))->format('Y-m-d'),
                'reason' => $faker->sentence,
                'status' => $faker->randomElement(['pending', 'approved', 'rejected']),
            ]);
        }

        $this->call([
            EmployeeSeeder::class,
            SettingSeeder::class,
            OvertimeRequestSeeder::class,
        ]);
    }
}
