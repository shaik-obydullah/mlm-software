<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Activated = 'activated';
    case Cancelled = 'cancelled';
}
