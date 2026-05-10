<?php

namespace App\Models;

use App\Models\Procedure;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainingRequirement extends Model
{
  use HasFactory;

  protected $fillable = [
    'procedure_id',
    'required_by_end_of_program',
    'expected_by_r3',
  ];

  public function procedure(): BelongsTo
  {
    return $this->belongsTo(Procedure::class);
  }
}
