<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRoleOption extends Model
{
  use HasFactory;

  protected $table = 'user_roles';

  protected $fillable = [
    'code',
    'label',
    'is_system',
  ];

  protected function casts(): array
  {
    return [
      'is_system' => 'bool',
    ];
  }
}
