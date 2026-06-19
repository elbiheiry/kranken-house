<?php

namespace App\Models;

use App\Models\CaseApproval;
use App\Models\Procedure;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CaseLog extends Model
{
  use HasFactory;

  protected $fillable = [
    'resident_id',
    'procedure_id',
    'case_code',
    'operation_type',
    'difficulty_level',
    'role',
    'operation_date',
    'supervisor_id',
    'note',
  ];

  protected function casts(): array
  {
    return [
      'operation_date' => 'date',
    ];
  }

  public function resident(): BelongsTo
  {
    return $this->belongsTo(Resident::class);
  }

  public function procedure(): BelongsTo
  {
    return $this->belongsTo(Procedure::class);
  }

  public function supervisor(): BelongsTo
  {
    return $this->belongsTo(User::class, 'supervisor_id');
  }

  public function approval(): HasOne
  {
    return $this->hasOne(CaseApproval::class);
  }
}
