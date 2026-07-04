<?php

namespace Database\Seeders;

use App\Models\ApprovalStatusOption;
use App\Models\Assignment;
use App\Models\CaseApproval;
use App\Models\CaseLog;
use App\Models\OperationRoleOption;
use App\Models\OperationTypeOption;
use App\Models\Procedure;
use App\Models\ProcedureYearlyTarget;
use App\Models\Resident;
use App\Models\SystemNotification;
use App\Models\TrainingRequirement;
use App\Models\User;
use App\Models\UserRoleOption;
use App\Support\ProgressCalculator;
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
        $this->seedReferenceData();

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'System Administrator', 'role' => 'administrator', 'password' => Hash::make('password')]
        );

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

            $this->seedYearlyTargets($procedure, $definition['required'], $definition['expected']);

            return $procedure->load('trainingRequirement');
        });

        DB::table('case_approvals')->delete();
        DB::table('case_logs')->delete();
        DB::table('assignments')->delete();
        DB::table('notifications')->delete();

        $supervisorIds = [$supervisorOne->id, $supervisorTwo->id];
        $roles = ['assistant', 'first_assistant', 'primary', 'supervised_primary'];
        $qualifyingRoles = ['first_assistant', 'primary', 'supervised_primary'];
        $targetRatioByTrainingYear = [
            1 => 0.78, // at risk
            2 => 1.02, // on track
            3 => 1.00, // on track
            4 => 0.82, // at risk
            5 => 0.66, // behind
            6 => 0.70,
        ];
        $feedbackPool = [
            'Good dissection plane and safe tissue handling.',
            'Improve anatomical landmark recognition before clipping.',
            'Better camera navigation needed in laparoscopy.',
            'Strong suturing and knot security.',
            'Needs more confidence in independent decision steps.',
        ];

        foreach ($residents as $residentIndex => $resident) {
            foreach ($procedureModels as $procedureIndex => $procedure) {
                $residentRatio = $targetRatioByTrainingYear[$resident->training_year] ?? 0.80;
                $expectedForProcedure = ProgressCalculator::expectedByTrainingYear($resident, $procedure->trainingRequirement);
                $qualifiedApprovedTarget = max(1, (int) round($expectedForProcedure * $residentRatio));

                $caseBlueprints = [];

                for ($i = 0; $i < $qualifiedApprovedTarget; $i++) {
                    $caseBlueprints[] = [
                        'status' => 'approved',
                        'role' => $qualifyingRoles[$i % count($qualifyingRoles)],
                    ];
                }

                // Add non-qualifying or undecided entries to keep data realistic.
                $caseBlueprints[] = ['status' => 'approved', 'role' => 'assistant'];
                $caseBlueprints[] = ['status' => 'rejected', 'role' => $roles[($residentIndex + $procedureIndex) % count($roles)]];
                $caseBlueprints[] = ['status' => 'pending', 'role' => $roles[($residentIndex + $procedureIndex + 1) % count($roles)]];

                foreach ($caseBlueprints as $i => $blueprint) {
                    $operationDate = now()->subDays(($residentIndex * 20) + ($procedureIndex * 8) + ($i * 5));
                    $role = $blueprint['role'];
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

                    $status = $blueprint['status'];

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

        $recentRoles = ['assistant', 'primary'];
        foreach ($residents as $residentIndex => $resident) {
            for ($i = 0; $i < 3; $i++) {
                $procedure = $procedureModels[($residentIndex + $i) % $procedureModels->count()];
                $operationDate = now()->subDays(($residentIndex * 3 + $i * 2) % 14);
                $role = $recentRoles[($residentIndex + $i) % count($recentRoles)];
                $supervisorId = $supervisorIds[($residentIndex + $i) % count($supervisorIds)];
                $status = $i === 2 ? 'pending' : 'approved';

                $log = CaseLog::create([
                    'resident_id' => $resident->id,
                    'procedure_id' => $procedure->id,
                    'case_code' => strtoupper(Str::random(10)),
                    'operation_type' => $i % 2 === 0 ? 'elective' : 'emergency',
                    'difficulty_level' => (($residentIndex + $i) % 5) + 1,
                    'role' => $role,
                    'operation_date' => $operationDate->toDateString(),
                    'supervisor_id' => $supervisorId,
                    'is_external' => (bool) (($residentIndex + $i) % 2),
                    'note' => 'Recent seeded case from the last two weeks.',
                ]);

                $decidedAt = $operationDate->copy()->addDay();
                if ($decidedAt->greaterThan(now())) {
                    $decidedAt = now();
                }

                CaseApproval::create([
                    'case_log_id' => $log->id,
                    'supervisor_id' => $supervisorId,
                    'status' => $status,
                    'feedback' => $status === 'pending' ? null : $feedbackPool[($residentIndex + $i) % count($feedbackPool)],
                    'approved_role' => $status === 'approved' ? $role : null,
                    'approved_procedure_id' => $status === 'approved' ? $procedure->id : null,
                    'decided_at' => $status === 'pending' ? null : $decidedAt,
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

    private function seedReferenceData(): void
    {
        $roles = [
            ['code' => 'administrator', 'label' => 'Administrator', 'is_system' => true],
            ['code' => 'director', 'label' => 'Director', 'is_system' => true],
            ['code' => 'supervisor', 'label' => 'Supervisor', 'is_system' => true],
            ['code' => 'resident', 'label' => 'Resident', 'is_system' => true],
        ];

        foreach ($roles as $role) {
            UserRoleOption::query()->updateOrCreate(
                ['code' => $role['code']],
                ['label' => $role['label'], 'is_system' => $role['is_system']]
            );
        }

        $operationTypes = [
            ['code' => 'elective', 'label' => 'Elective'],
            ['code' => 'emergency', 'label' => 'Emergency'],
        ];

        foreach ($operationTypes as $operationType) {
            OperationTypeOption::query()->updateOrCreate(
                ['code' => $operationType['code']],
                ['label' => $operationType['label']]
            );
        }

        $operationRoles = [
            ['code' => 'assistant', 'label' => 'Assistant', 'counts_towards_progress' => false],
            ['code' => 'first_assistant', 'label' => 'First Assistant', 'counts_towards_progress' => true],
            ['code' => 'primary', 'label' => 'Primary Surgeon', 'counts_towards_progress' => true],
            ['code' => 'supervised_primary', 'label' => 'Supervised Primary Surgeon', 'counts_towards_progress' => true],
        ];

        foreach ($operationRoles as $operationRole) {
            OperationRoleOption::query()->updateOrCreate(
                ['code' => $operationRole['code']],
                [
                    'label' => $operationRole['label'],
                    'counts_towards_progress' => $operationRole['counts_towards_progress'],
                ]
            );
        }

        foreach (
            [
                ['code' => 'pending', 'label' => 'Pending'],
                ['code' => 'approved', 'label' => 'Approved'],
                ['code' => 'rejected', 'label' => 'Rejected'],
            ] as $status
        ) {
            ApprovalStatusOption::query()->updateOrCreate(
                ['code' => $status['code']],
                ['label' => $status['label']]
            );
        }
    }

    private function seedYearlyTargets(Procedure $procedure, int $requiredByEndOfProgram, int $expectedByR3): void
    {
        $distribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];

        $r1toR3Base = intdiv($expectedByR3, 3);
        $r1toR3Remainder = $expectedByR3 % 3;
        foreach ([1, 2, 3] as $index => $year) {
            $distribution[$year] = $r1toR3Base + ($index < $r1toR3Remainder ? 1 : 0);
        }

        $remaining = max(0, $requiredByEndOfProgram - $expectedByR3);
        $r4toR6Base = intdiv($remaining, 3);
        $r4toR6Remainder = $remaining % 3;
        foreach ([4, 5, 6] as $index => $year) {
            $distribution[$year] = $r4toR6Base + ($index < $r4toR6Remainder ? 1 : 0);
        }

        foreach ($distribution as $year => $requiredCases) {
            ProcedureYearlyTarget::query()->updateOrCreate(
                ['procedure_id' => $procedure->id, 'training_year' => $year],
                ['required_cases' => $requiredCases]
            );
        }
    }
}
