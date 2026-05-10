<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Assignment extends Model
{
  use HasFactory;

  protected $fillable = [
    'resident_id',
    'procedure_id',
    'recommended_by_id',
    'priority_score',
    'reason',
    'scheduled_for',
    'status',
  ];

  protected function casts(): array
  {
    return [
      'priority_score' => 'float',
      'scheduled_for' => 'date',
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

  public function recommendedBy(): BelongsTo
  {
    return $this->belongsTo(User::class, 'recommended_by_id');
  }
}
