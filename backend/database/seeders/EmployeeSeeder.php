<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Division;
use App\Models\Position;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class EmployeeSeeder extends Seeder
{
    /**
     * Number of employees to create (excluding the admin account).
     */
    protected int $employeeCount = 100;

    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');
        $defaultPassword = Hash::make('password');

        // ---- 1. Ensure Master Data (idempotent) -----------------------------
        $divisions = $this->ensureDivisions();
        $positions = $this->ensurePositions();
        $shift = $this->ensureShift();

        // ---- 2. Ensure the Admin account (idempotent) -----------------------
        User::updateOrCreate(
            ['email' => 'admin@hris.com'],
            [
                'name' => 'Super Admin',
                'password' => $defaultPassword,
                'role' => 'admin',
                'nik' => 'ADM-001',
                'status' => 'active',
                'division_id' => $divisions[1]->id,
                'position_id' => $positions[0]->id,
                'shift_id' => $shift->id,
                'leave_quota' => 12,
            ]
        );

        // ---- 3. Create Employees --------------------------------------------
        $this->command->info("Creating {$this->employeeCount} employees...");
        $baris = $this->command->getOutput();

        for ($i = 1; $i <= $this->employeeCount; $i++) {
            $gender = $faker->randomElement(['male', 'female']);

            User::updateOrCreate(
                ['email' => "employee{$i}@hris.com"],
                [
                    'name' => $faker->name($gender === 'male' ? 'male' : 'female'),
                    'password' => $defaultPassword,
                    'role' => 'employee',
                    'nik' => sprintf('%06d%02d%02d%04d', rand(340201, 340499), rand(1, 31), rand(1, 12), $i + 1000),
                    'status' => $faker->boolean(90) ? 'active' : 'inactive',
                    'division_id' => $faker->randomElement($divisions)->id,
                    'position_id' => $faker->randomElement($positions)->id,
                    'shift_id' => $shift->id,
                    'leave_quota' => 12,
                    'phone' => $faker->phoneNumber,
                    'address' => $faker->address,
                    'gender' => $gender,
                    'dob' => $faker->dateTimeBetween('-40 years', '-20 years')->format('Y-m-d'),
                    'join_date' => $faker->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
                ]
            );

            if ($i % 10 === 0) {
                $baris->writeln("  ✓ {$i}/{$this->employeeCount} employees");
            }
        }

        $this->command->info('✅ EmployeeSeeder completed.');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Division>
     */
    private function ensureDivisions()
    {
        $names = ['IT & Engineering', 'Human Resources', 'Finance & Tax', 'Marketing', 'Sales & BD', 'Operations'];

        return collect($names)->map(fn ($name) => Division::firstOrCreate(['name' => $name]));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Position>
     */
    private function ensurePositions()
    {
        $names = ['Manager', 'Senior Staff', 'Staff', 'Intern', 'Supervisor'];

        return collect($names)->map(function ($name) {
            return Position::firstOrCreate(['name' => $name], ['level' => rand(1, 5)]);
        });
    }

    private function ensureShift(): Shift
    {
        return Shift::firstOrCreate(
            ['name' => 'Regular Shift'],
            [
                'time_in' => '09:00:00',
                'time_out' => '17:00:00',
            ]
        );
    }
}
