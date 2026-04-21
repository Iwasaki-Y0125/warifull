<?php

namespace App\Enums;

enum TaskSubstitutionStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
}
