<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProcedureYearlyTarget extends Model
{
  use HasFactory;

  protected $fillable = [
    'procedure_id',
    'training_year',
    'required_cases',
  ];

  public function procedure(): BelongsTo
  {
    return $this->belongsTo(Procedure::class);
  }
}
