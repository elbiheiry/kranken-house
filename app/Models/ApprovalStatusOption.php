<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalStatusOption extends Model
{
  use HasFactory;

  protected $table = 'approval_statuses';

  protected $fillable = [
    'code',
    'label',
  ];
}
