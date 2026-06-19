<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationRoleOption extends Model
{
  use HasFactory;

  protected $table = 'operation_roles';

  protected $fillable = [
    'code',
    'label',
    'counts_towards_progress',
  ];

  protected function casts(): array
  {
    return [
      'counts_towards_progress' => 'bool',
    ];
  }
}
