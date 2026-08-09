<?php

namespace App\Enums;

enum TransactionType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
    case Hold = 'hold';
    case Release = 'release';
    case Fee = 'fee';
}

enum TransactionCategory: string
{
    case Commission = 'commission';
    case Withdrawal = 'withdrawal';
    case Interest = 'interest';
    case Purchase = 'purchase';
    case Bonus = 'bonus';
    case Adjustment = 'adjustment';
}
