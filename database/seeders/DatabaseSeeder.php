<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\CaseApproval;
use App\Models\CaseLog;
use App\Models\Procedure;
use App\Models\Resident;
use App\Models\SystemNotification;
use App\Models\TrainingRequirement;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $director = User::updateOrCreate(
            ['email' => 'director@example.com'],
            ['name' => 'Training Director', 'role' => 'director', 'password' => Hash::make('password')]
        );

        $supervisorOne = User::updateOrCreate(
            ['email' => 'supervisor@example.com'],
            ['name' => 'Supervisor One', 'role' => 'supervisor', 'password' => Hash::make('password')]
        );

        $supervisorTwo = User::updateOrCreate(
            ['email' => 'supervisor.two@example.com'],
            ['name' => 'Supervisor Two', 'role' => 'supervisor', 'password' => Hash::make('password')]
        );

        $residentUsers = [
            ['name' => 'Resident A', 'email' => 'resident.a@example.com', 'training_year' => 3],
            ['name' => 'Resident B', 'email' => 'resident.b@example.com', 'training_year' => 4],
            ['name' => 'Resident C', 'email' => 'resident.c@example.com', 'training_year' => 2],
            ['name' => 'Resident D', 'email' => 'resident.d@example.com', 'training_year' => 5],
            ['name' => 'Resident E', 'email' => 'resident.e@example.com', 'training_year' => 1],
        ];

        $residents = collect($residentUsers)->map(function (array $residentUser) {
            $user = User::updateOrCreate(
                ['email' => $residentUser['email']],
                ['name' => $residentUser['name'], 'role' => 'resident', 'password' => Hash::make('password')]
            );

            return Resident::updateOrCreate(
                ['user_id' => $user->id],
                ['training_year' => $residentUser['training_year']]
            );
        });

        $procedures = [
            ['name' => 'Appendectomy', 'slug' => 'appendectomy', 'required' => 30, 'expected' => 12],
            ['name' => 'Hernia Repair', 'slug' => 'hernia-repair', 'required' => 40, 'expected' => 15],
            ['name' => 'Cholecystectomy', 'slug' => 'cholecystectomy', 'required' => 25, 'expected' => 10],
            ['name' => 'Colorectal Surgery', 'slug' => 'colorectal-surgery', 'required' => 25, 'expected' => 8],
            ['name' => 'Emergency Laparotomy', 'slug' => 'emergency-laparotomy', 'required' => 20, 'expected' => 6],
        ];

        $procedureModels = collect($procedures)->map(function (array $definition) {
            $procedure = Procedure::updateOrCreate(
                ['slug' => $definition['slug']],
                ['name' => $definition['name'], 'is_major' => true]
            );

            TrainingRequirement::updateOrCreate(
                ['procedure_id' => $procedure->id],
                [
                    'required_by_end_of_program' => $definition['required'],
                    'expected_by_r3' => $definition['expected'],
                ]
            );

            return $procedure;
        });

        DB::table('case_approvals')->delete();
        DB::table('case_logs')->delete();
        DB::table('assignments')->delete();
        DB::table('notifications')->delete();

        $supervisorIds = [$supervisorOne->id, $supervisorTwo->id];
        $roles = ['assistant', 'first_assistant', 'primary', 'supervised_primary'];
        $feedbackPool = [
            'Good dissection plane and safe tissue handling.',
            'Improve anatomical landmark recognition before clipping.',
            'Better camera navigation needed in laparoscopy.',
            'Strong suturing and knot security.',
            'Needs more confidence in independent decision steps.',
        ];

        foreach ($residents as $residentIndex => $resident) {
            foreach ($procedureModels as $procedureIndex => $procedure) {
                for ($i = 0; $i < 6; $i++) {
                    $operationDate = now()->subDays(($residentIndex * 20) + ($procedureIndex * 8) + ($i * 5));
                    $role = $roles[($residentIndex + $procedureIndex + $i) % count($roles)];
                    $supervisorId = $supervisorIds[($procedureIndex + $i) % count($supervisorIds)];

                    $log = CaseLog::create([
                        'resident_id' => $resident->id,
                        'procedure_id' => $procedure->id,
                        'case_code' => strtoupper(Str::random(10)),
                        'operation_type' => $i % 2 === 0 ? 'elective' : 'emergency',
                        'difficulty_level' => (($i + $procedureIndex) % 5) + 1,
                        'role' => $role,
                        'operation_date' => $operationDate->toDateString(),
                        'supervisor_id' => $supervisorId,
                        'note' => 'Seeded operation case for MVP demo data.',
                    ]);

                    $status = match (true) {
                        $i === 0 => 'pending',
                        $i % 5 === 0 => 'rejected',
                        default => 'approved',
                    };

                    CaseApproval::create([
                        'case_log_id' => $log->id,
                        'supervisor_id' => $supervisorId,
                        'status' => $status,
                        'feedback' => $feedbackPool[($residentIndex + $procedureIndex + $i) % count($feedbackPool)],
                        'approved_role' => $status === 'approved' ? $role : null,
                        'approved_procedure_id' => $status === 'approved' ? $procedure->id : null,
                        'decided_at' => $status === 'pending' ? null : $operationDate->copy()->addDays(1),
                    ]);
                }

                $recommendedResident = $residents[($residentIndex + $procedureIndex) % $residents->count()];
                Assignment::create([
                    'resident_id' => $recommendedResident->id,
                    'procedure_id' => $procedure->id,
                    'recommended_by_id' => $director->id,
                    'priority_score' => round(mt_rand(650, 980) / 10, 2),
                    'reason' => 'Resident is behind expected exposure and has low recent case volume for this procedure.',
                    'scheduled_for' => now()->addDays(($procedureIndex * 4) + ($residentIndex + 1))->toDateString(),
                    'status' => $procedureIndex % 2 === 0 ? 'suggested' : 'planned',
                ]);
            }
        }

        $allUsers = User::all();
        foreach ($allUsers as $user) {
            for ($n = 1; $n <= 3; $n++) {
                SystemNotification::create([
                    'user_id' => $user->id,
                    'type' => $n === 1 ? 'approval' : ($n === 2 ? 'assignment' : 'alert'),
                    'title' => $n === 1 ? 'Case Review Update' : ($n === 2 ? 'Operation Assignment Suggestion' : 'Training Progress Alert'),
                    'message' => 'Seeded notification item for MVP demonstration.',
                    'data' => ['batch' => 'seed', 'sequence' => $n],
                    'read_at' => $n === 3 ? null : now()->subDays($n),
                ]);
            }
        }
    }
}
