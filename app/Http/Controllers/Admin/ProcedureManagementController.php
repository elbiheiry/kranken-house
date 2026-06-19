<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Procedure;
use App\Models\ProcedureYearlyTarget;
use App\Support\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProcedureManagementController extends Controller
{
  public function index(): View
  {
    return view('admin.procedures.index', [
      'procedures' => Procedure::query()->with('yearlyTargets')->orderBy('name')->paginate(20),
    ]);
  }

  public function create(): View
  {
    return view('admin.procedures.form', [
      'procedure' => new Procedure(),
      'isEdit' => false,
      'yearlyTargets' => $this->emptyYearlyTargets(),
    ]);
  }

  public function store(Request $request, NotificationService $notificationService): RedirectResponse
  {
    $validated = $this->validatePayload($request);

    $procedure = Procedure::query()->create([
      'name' => $validated['name'],
      'slug' => $validated['slug'] ?: Str::slug($validated['name']),
      'is_major' => $request->boolean('is_major'),
    ]);

    $this->syncYearlyTargets($procedure, $validated['yearly_targets']);

    $notificationService->notifyAllExcept(
      $request->user()->id,
      'admin-procedure-created',
      'Procedure created',
      sprintf('%s created procedure: %s.', $request->user()->name, $procedure->name),
      ['procedure_id' => $procedure->id]
    );

    return redirect()->route('admin.procedures.index')->with('status', 'Procedure created successfully.');
  }

  public function edit(Procedure $procedure): View
  {
    $procedure->load('yearlyTargets');

    return view('admin.procedures.form', [
      'procedure' => $procedure,
      'isEdit' => true,
      'yearlyTargets' => $this->mappedYearlyTargets($procedure),
    ]);
  }

  public function update(Request $request, Procedure $procedure, NotificationService $notificationService): RedirectResponse
  {
    $validated = $this->validatePayload($request, $procedure);

    $procedure->update([
      'name' => $validated['name'],
      'slug' => $validated['slug'] ?: Str::slug($validated['name']),
      'is_major' => $request->boolean('is_major'),
    ]);

    $this->syncYearlyTargets($procedure, $validated['yearly_targets']);

    $notificationService->notifyAllExcept(
      $request->user()->id,
      'admin-procedure-updated',
      'Procedure updated',
      sprintf('%s updated procedure: %s.', $request->user()->name, $procedure->name),
      ['procedure_id' => $procedure->id]
    );

    return redirect()->route('admin.procedures.index')->with('status', 'Procedure updated successfully.');
  }

  public function destroy(Request $request, Procedure $procedure, NotificationService $notificationService): RedirectResponse
  {
    $name = $procedure->name;
    $procedure->delete();

    $notificationService->notifyAllExcept(
      $request->user()->id,
      'admin-procedure-deleted',
      'Procedure deleted',
      sprintf('%s deleted procedure: %s.', $request->user()->name, $name),
      ['procedure_name' => $name]
    );

    return redirect()->route('admin.procedures.index')->with('status', 'Procedure deleted successfully.');
  }

  private function validatePayload(Request $request, ?Procedure $procedure = null): array
  {
    return $request->validate([
      'name' => ['required', 'string', 'max:255'],
      'slug' => [
        'nullable',
        'string',
        'max:255',
        Rule::unique('procedures', 'slug')->ignore($procedure?->id),
      ],
      'yearly_targets' => ['required', 'array'],
      'yearly_targets.*' => ['required', 'integer', 'min:0'],
    ]);
  }

  private function syncYearlyTargets(Procedure $procedure, array $yearlyTargets): void
  {
    foreach (range(1, 6) as $year) {
      ProcedureYearlyTarget::query()->updateOrCreate(
        ['procedure_id' => $procedure->id, 'training_year' => $year],
        ['required_cases' => (int) ($yearlyTargets[$year] ?? 0)]
      );
    }
  }

  private function mappedYearlyTargets(Procedure $procedure): array
  {
    $targets = $this->emptyYearlyTargets();

    foreach ($procedure->yearlyTargets as $target) {
      $targets[$target->training_year] = $target->required_cases;
    }

    return $targets;
  }

  private function emptyYearlyTargets(): array
  {
    return collect(range(1, 6))->mapWithKeys(fn(int $year) => [$year => 0])->all();
  }
}
