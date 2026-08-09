<?php

namespace App\Enums;

enum PinStatus: string
{
    case Available = 'available';
    case Assigned = 'assigned';
    case Used = 'used';
    case Expired = 'expired';
}
