<?php

namespace App\Enums;

enum OperationRole: string
{
  case Assistant = 'assistant';
  case FirstAssistant = 'first_assistant';
  case Primary = 'primary';
  case SupervisedPrimary = 'supervised_primary';

  public function countsTowardsProgress(): bool
  {
    return in_array($this, [
      self::FirstAssistant,
      self::Primary,
      self::SupervisedPrimary,
    ], true);
  }
}
