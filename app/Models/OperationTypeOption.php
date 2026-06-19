<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OperationTypeOption extends Model
{
  use HasFactory;

  protected $table = 'operation_types';

  protected $fillable = [
    'code',
    'label',
  ];
}
