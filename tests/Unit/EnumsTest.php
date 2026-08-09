<?php

namespace Tests\Unit;

use App\Enums\CommissionType;
use App\Enums\KycStatus;
use App\Enums\PinStatus;
use App\Enums\PurchaseStatus;
use App\Enums\TransactionCategory;
use App\Enums\TransactionType;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Enums\WithdrawalStatus;
use PHPUnit\Framework\TestCase;

class EnumsTest extends TestCase
{
    public function test_user_role_values_and_labels(): void
    {
        $this->assertSame('admin', UserRole::Admin->value);
        $this->assertSame('agent', UserRole::Agent->value);
        $this->assertSame('member', UserRole::Member->value);
        $this->assertSame('Admin', UserRole::Admin->label());
    }

    public function test_user_status_values(): void
    {
        $this->assertSame('pending', UserStatus::Pending->value);
        $this->assertSame('active', UserStatus::Active->value);
        $this->assertSame('inactive', UserStatus::Inactive->value);
        $this->assertSame('suspended', UserStatus::Suspended->value);
    }

    public function test_purchase_status_values(): void
    {
        $this->assertSame('pending', PurchaseStatus::Pending->value);
        $this->assertSame('paid', PurchaseStatus::Paid->value);
        $this->assertSame('activated', PurchaseStatus::Activated->value);
        $this->assertSame('cancelled', PurchaseStatus::Cancelled->value);
    }

    public function test_commission_type_values_and_labels(): void
    {
        $this->assertSame('sponsor', CommissionType::Sponsor->value);
        $this->assertSame('matching', CommissionType::Matching->value);
        $this->assertSame('level', CommissionType::Level->value);
        $this->assertSame('royalty', CommissionType::Royalty->value);
        $this->assertSame('Sponsor Bonus', CommissionType::Sponsor->label());
        $this->assertSame('Matching Bonus', CommissionType::Matching->label());
    }

    public function test_pin_status_values(): void
    {
        $this->assertSame('available', PinStatus::Available->value);
        $this->assertSame('assigned', PinStatus::Assigned->value);
        $this->assertSame('used', PinStatus::Used->value);
        $this->assertSame('expired', PinStatus::Expired->value);
    }

    public function test_kyc_status_values(): void
    {
        $this->assertSame('not_submitted', KycStatus::NotSubmitted->value);
        $this->assertSame('pending', KycStatus::Pending->value);
        $this->assertSame('approved', KycStatus::Approved->value);
        $this->assertSame('rejected', KycStatus::Rejected->value);
    }

    public function test_transaction_types_and_categories(): void
    {
        $this->assertSame('credit', TransactionType::Credit->value);
        $this->assertSame('debit', TransactionType::Debit->value);
        $this->assertSame('hold', TransactionType::Hold->value);
        $this->assertSame('release', TransactionType::Release->value);
        $this->assertSame('commission', TransactionCategory::Commission->value);
        $this->assertSame('interest', TransactionCategory::Interest->value);
        $this->assertSame('withdrawal', TransactionCategory::Withdrawal->value);
    }

    public function test_withdrawal_status_values(): void
    {
        $this->assertSame('pending', WithdrawalStatus::Pending->value);
        $this->assertSame('approved', WithdrawalStatus::Approved->value);
        $this->assertSame('processing', WithdrawalStatus::Processing->value);
        $this->assertSame('completed', WithdrawalStatus::Completed->value);
        $this->assertSame('rejected', WithdrawalStatus::Rejected->value);
        $this->assertSame('cancelled', WithdrawalStatus::Cancelled->value);
    }
}
