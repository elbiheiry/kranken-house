<?php

namespace App\Models;

use App\Models\CaseLog;
use App\Models\Procedure;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CaseApproval extends Model
{
  use HasFactory;

  protected $fillable = [
    'case_log_id',
    'supervisor_id',
    'status',
    'feedback',
    'approved_role',
    'approved_procedure_id',
    'decided_at',
  ];

  protected function casts(): array
  {
    return [
      'decided_at' => 'datetime',
    ];
  }

  public function caseLog(): BelongsTo
  {
    return $this->belongsTo(CaseLog::class);
  }

  public function supervisor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'supervisor_id');
  }

  public function approvedProcedure(): BelongsTo
  {
    return $this->belongsTo(Procedure::class, 'approved_procedure_id');
  }
}
