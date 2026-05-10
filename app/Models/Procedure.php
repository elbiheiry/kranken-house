<?php

namespace App\Models;

use App\Models\Assignment;
use App\Models\CaseLog;
use App\Models\TrainingRequirement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Procedure extends Model
{
  use HasFactory;

  protected $fillable = [
    'name',
    'slug',
    'is_major',
  ];

  protected function casts(): array
  {
    return [
      'is_major' => 'bool',
    ];
  }

  public function trainingRequirement(): HasOne
  {
    return $this->hasOne(TrainingRequirement::class);
  }

  public function caseLogs(): HasMany
  {
    return $this->hasMany(CaseLog::class);
  }

  public function assignments(): HasMany
  {
    return $this->hasMany(Assignment::class);
  }
}
