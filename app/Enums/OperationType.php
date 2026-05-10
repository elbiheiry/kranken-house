<?php

namespace App\Enums;

enum OperationType: string
{
  case Emergency = 'emergency';
  case Elective = 'elective';
}
