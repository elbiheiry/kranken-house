<?php

namespace Tests\Feature;

use App\Models\ApprovalStatusOption;
use App\Models\CaseApproval;
use App\Models\CaseLog;
use App\Models\OperationRoleOption;
use App\Models\Procedure;
use App\Models\Resident;
use App\Models\TrainingRequirement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleFlowsFeatureTest extends TestCase
{
  use RefreshDatabase;

  public function test_resident_peers_progress_exposes_full_peer_details_in_view_data(): void
  {
    $this->seedLookupOptions();

    $currentResidentUser = User::factory()->create([
      'name' => 'Current Resident',
      'role' => 'resident',
    ]);
    $currentResident = Resident::create([
      'user_id' => $currentResidentUser->id,
      'training_year' => 3,
    ]);

    $peerUser = User::factory()->create([
      'name' => 'Peer Resident',
      'role' => 'resident',
    ]);
    $peerResident = Resident::create([
      'user_id' => $peerUser->id,
      'training_year' => 4,
    ]);

    $supervisor = User::factory()->create([
      'name' => 'Supervisor One',
      'role' => 'supervisor',
    ]);

    $procedureA = $this->createProcedureWithRequirement('Appendectomy', 'appendectomy');
    $procedureB = $this->createProcedureWithRequirement('Cholecystectomy', 'cholecystectomy');

    $this->createCaseLogWithApproval(
      resident: $peerResident,
      procedure: $procedureA,
      supervisor: $supervisor,
      role: 'primary',
      operationDate: '2026-06-29',
      status: 'approved',
      isExternal: false
    );

    $this->createCaseLogWithApproval(
      resident: $peerResident,
      procedure: $procedureA,
      supervisor: $supervisor,
      role: 'supervised_primary',
      operationDate: '2026-06-25',
      status: 'approved',
      isExternal: true
    );

    $this->createCaseLogWithApproval(
      resident: $peerResident,
      procedure: $procedureB,
      supervisor: $supervisor,
      role: 'assistant',
      operationDate: '2026-06-20',
      status: 'pending',
      isExternal: false
    );

    $this->createCaseLogWithApproval(
      resident: $peerResident,
      procedure: $procedureB,
      supervisor: $supervisor,
      role: 'primary',
      operationDate: '2026-06-15',
      status: 'approved',
      isExternal: false
    );

    $this->actingAs($currentResidentUser);

    $response = $this->get(route('resident.peers-progress'));

    $response->assertOk();

    $peerRows = $response->viewData('peerRows');

    $this->assertNotNull($peerRows);
    $this->assertCount(1, $peerRows);

    $row = $peerRows->first();

    $this->assertArrayHasKey('peer_info', $row);
    $this->assertArrayHasKey('operation_breakdown', $row);
    $this->assertArrayHasKey('recent_cases', $row);

    $this->assertSame(4, $row['peer_info']['total_case_logs']);
    $this->assertSame(3, $row['peer_info']['internal_cases']);
    $this->assertSame(1, $row['peer_info']['external_cases']);
    $this->assertSame(3, $row['peer_info']['operations_count']);
    $this->assertSame(1, $row['peer_info']['assistance_count']);

    $this->assertSame('Appendectomy', $row['operation_breakdown'][0]['procedure_name']);
    $this->assertSame(2, $row['operation_breakdown'][0]['count']);
    $this->assertSame('Cholecystectomy', $row['operation_breakdown'][1]['procedure_name']);
    $this->assertSame(1, $row['operation_breakdown'][1]['count']);

    $this->assertCount(4, $row['recent_cases']);
    $this->assertSame('2026-06-29', $row['recent_cases'][0]['operation_date']);
    $this->assertSame('Appendectomy', $row['recent_cases'][0]['procedure_name']);
  }

  public function test_supervisor_approvals_page_contains_case_details_and_decision_actions(): void
  {
    $this->seedLookupOptions();

    $supervisor = User::factory()->create([
      'name' => 'Supervisor One',
      'role' => 'supervisor',
    ]);

    $residentUser = User::factory()->create([
      'name' => 'Resident One',
      'role' => 'resident',
    ]);
    $resident = Resident::create([
      'user_id' => $residentUser->id,
      'training_year' => 3,
    ]);

    $procedure = $this->createProcedureWithRequirement('Hernia Repair', 'hernia-repair');

    $caseLog = CaseLog::create([
      'resident_id' => $resident->id,
      'procedure_id' => $procedure->id,
      'case_code' => 'CASE-REJ-001',
      'is_external' => false,
      'operation_type' => 'elective',
      'difficulty_level' => 2,
      'role' => 'primary',
      'operation_date' => '2026-06-10',
      'supervisor_id' => $supervisor->id,
      'note' => null,
    ]);

    CaseApproval::create([
      'case_log_id' => $caseLog->id,
      'supervisor_id' => $supervisor->id,
      'status' => 'pending',
    ]);

    $this->actingAs($supervisor);

    $response = $this->get(route('supervisor.approvals.index'));

    $response->assertOk();
    $response->assertSee('data-current-role="primary"', false);
    $response->assertSee('data-current-procedure="Hernia Repair"', false);
    $response->assertSee(__('app.col_details'));
    $response->assertSee(__('app.view_details'));
    $response->assertSee(__('app.approve'));
    $response->assertSee(__('app.reject'));
    $response->assertSee(__('app.operation_type'));
    $response->assertSee(__('app.difficulty_level'));
    $response->assertSee('name="status" value="approved"', false);
    $response->assertSee('name="status" value="rejected"', false);
  }

  public function test_supervisor_can_reject_case_log_and_update_role_procedure_and_feedback(): void
  {
    $this->seedLookupOptions();

    $supervisor = User::factory()->create([
      'name' => 'Supervisor One',
      'role' => 'supervisor',
    ]);

    $director = User::factory()->create([
      'name' => 'Director One',
      'role' => 'director',
    ]);

    $residentUser = User::factory()->create([
      'name' => 'Resident One',
      'role' => 'resident',
    ]);
    $resident = Resident::create([
      'user_id' => $residentUser->id,
      'training_year' => 3,
    ]);

    $sourceProcedure = $this->createProcedureWithRequirement('Colorectal', 'colorectal');
    $targetProcedure = $this->createProcedureWithRequirement('Gastrectomy', 'gastrectomy');

    $caseLog = CaseLog::create([
      'resident_id' => $resident->id,
      'procedure_id' => $sourceProcedure->id,
      'case_code' => 'CASE-UPD-001',
      'is_external' => false,
      'operation_type' => 'elective',
      'difficulty_level' => 3,
      'role' => 'primary',
      'operation_date' => '2026-06-12',
      'supervisor_id' => $supervisor->id,
      'note' => null,
    ]);

    $approval = CaseApproval::create([
      'case_log_id' => $caseLog->id,
      'supervisor_id' => $supervisor->id,
      'status' => 'pending',
    ]);

    $this->actingAs($supervisor);

    $response = $this->patch(route('supervisor.approvals.update', $approval), [
      'status' => 'rejected',
      'feedback' => 'Role and procedure corrected during rejection.',
      'approved_role' => 'assistant',
      'approved_procedure_id' => $targetProcedure->id,
    ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('case_approvals', [
      'id' => $approval->id,
      'status' => 'rejected',
      'feedback' => 'Role and procedure corrected during rejection.',
      'approved_role' => 'assistant',
      'approved_procedure_id' => $targetProcedure->id,
    ]);

    $this->assertDatabaseHas('case_logs', [
      'id' => $caseLog->id,
      'role' => 'assistant',
      'procedure_id' => $targetProcedure->id,
    ]);

    $approval->refresh();
    $this->assertNotNull($approval->decided_at);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $residentUser->id,
      'type' => 'case-approval-updated',
    ]);

    $this->assertDatabaseHas('notifications', [
      'user_id' => $director->id,
      'type' => 'case-approval-updated',
    ]);
  }

  public function test_director_dashboard_exposes_activity_details_with_operation_breakdown(): void
  {
    $this->seedLookupOptions();

    $director = User::factory()->create([
      'name' => 'Director One',
      'role' => 'director',
    ]);

    $residentAUser = User::factory()->create([
      'name' => 'Resident A',
      'role' => 'resident',
    ]);
    $residentA = Resident::create([
      'user_id' => $residentAUser->id,
      'training_year' => 2,
    ]);

    $residentBUser = User::factory()->create([
      'name' => 'Resident B',
      'role' => 'resident',
    ]);
    $residentB = Resident::create([
      'user_id' => $residentBUser->id,
      'training_year' => 4,
    ]);

    $supervisor = User::factory()->create([
      'name' => 'Supervisor One',
      'role' => 'supervisor',
    ]);

    $procedureA = $this->createProcedureWithRequirement('Whipple', 'whipple');
    $procedureB = $this->createProcedureWithRequirement('Liver Resection', 'liver-resection');

    $this->createCaseLogWithApproval(
      resident: $residentA,
      procedure: $procedureA,
      supervisor: $supervisor,
      role: 'primary',
      operationDate: now()->subDays(3)->toDateString(),
      status: 'approved'
    );

    $this->createCaseLogWithApproval(
      resident: $residentA,
      procedure: $procedureA,
      supervisor: $supervisor,
      role: 'primary',
      operationDate: now()->subDays(5)->toDateString(),
      status: 'approved'
    );

    $this->createCaseLogWithApproval(
      resident: $residentA,
      procedure: $procedureB,
      supervisor: $supervisor,
      role: 'supervised_primary',
      operationDate: now()->subDays(6)->toDateString(),
      status: 'approved'
    );

    $this->createCaseLogWithApproval(
      resident: $residentA,
      procedure: $procedureB,
      supervisor: $supervisor,
      role: 'assistant',
      operationDate: now()->subDays(2)->toDateString(),
      status: 'approved'
    );

    $this->createCaseLogWithApproval(
      resident: $residentB,
      procedure: $procedureB,
      supervisor: $supervisor,
      role: 'assistant',
      operationDate: now()->subDays(1)->toDateString(),
      status: 'pending'
    );

    $this->actingAs($director);

    $response = $this->get(route('director.dashboard'));

    $response->assertOk();
    $response->assertSee('Operating Activity Details');

    $details = $response->viewData('activityDetails');

    $this->assertIsArray($details);
    $this->assertCount(2, $details);

    $residentARow = collect($details)->firstWhere('resident_name', 'Resident A');
    $this->assertNotNull($residentARow);
    $this->assertSame(4, $residentARow['total_cases']);
    $this->assertSame(3, $residentARow['operations_count']);
    $this->assertSame(1, $residentARow['assistance_count']);
    $this->assertSame('Whipple', $residentARow['operation_breakdown'][0]['procedure_name']);
    $this->assertSame(2, $residentARow['operation_breakdown'][0]['count']);

    $residentBRow = collect($details)->firstWhere('resident_name', 'Resident B');
    $this->assertNotNull($residentBRow);
    $this->assertSame(1, $residentBRow['total_cases']);
    $this->assertSame(0, $residentBRow['operations_count']);
    $this->assertSame(1, $residentBRow['assistance_count']);
  }

  private function seedLookupOptions(): void
  {
    ApprovalStatusOption::insert([
      ['code' => 'approved', 'label' => 'Approved', 'created_at' => now(), 'updated_at' => now()],
      ['code' => 'rejected', 'label' => 'Rejected', 'created_at' => now(), 'updated_at' => now()],
      ['code' => 'pending', 'label' => 'Pending', 'created_at' => now(), 'updated_at' => now()],
    ]);

    OperationRoleOption::insert([
      ['code' => 'assistant', 'label' => 'Assistant', 'counts_towards_progress' => false, 'created_at' => now(), 'updated_at' => now()],
      ['code' => 'first_assistant', 'label' => 'First Assistant', 'counts_towards_progress' => true, 'created_at' => now(), 'updated_at' => now()],
      ['code' => 'primary', 'label' => 'Primary Surgeon', 'counts_towards_progress' => true, 'created_at' => now(), 'updated_at' => now()],
      ['code' => 'supervised_primary', 'label' => 'Supervised Primary Surgeon', 'counts_towards_progress' => true, 'created_at' => now(), 'updated_at' => now()],
    ]);
  }

  private function createProcedureWithRequirement(string $name, string $slug): Procedure
  {
    $procedure = Procedure::create([
      'name' => $name,
      'slug' => $slug,
      'is_major' => true,
    ]);

    TrainingRequirement::create([
      'procedure_id' => $procedure->id,
      'required_by_end_of_program' => 12,
      'expected_by_r3' => 6,
    ]);

    return $procedure;
  }

  private function createCaseLogWithApproval(
    Resident $resident,
    Procedure $procedure,
    User $supervisor,
    string $role,
    string $operationDate,
    string $status,
    bool $isExternal = false
  ): CaseLog {
    $caseLog = CaseLog::create([
      'resident_id' => $resident->id,
      'procedure_id' => $procedure->id,
      'case_code' => 'CASE-' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 8)),
      'is_external' => $isExternal,
      'operation_type' => 'elective',
      'difficulty_level' => 3,
      'role' => $role,
      'operation_date' => $operationDate,
      'supervisor_id' => $supervisor->id,
      'note' => null,
    ]);

    CaseApproval::create([
      'case_log_id' => $caseLog->id,
      'supervisor_id' => $supervisor->id,
      'status' => $status,
      'decided_at' => $status === 'pending' ? null : now(),
    ]);

    return $caseLog;
  }
}
