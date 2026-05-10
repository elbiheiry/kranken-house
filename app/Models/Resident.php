<?php

namespace App\Models;

use App\Models\Assignment;
use App\Models\CaseLog;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'training_year',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
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
