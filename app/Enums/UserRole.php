<?php

namespace App\Enums;

enum UserRole: string
{
  case Resident = 'resident';
  case Supervisor = 'supervisor';
  case Director = 'director';
}
