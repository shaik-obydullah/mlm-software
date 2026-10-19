<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_money_formats_amounts(): void
    {
        $this->assertSame('₹ 0.00', money(0));
        $this->assertSame('₹ 1,234.50', money(1234.5));
        $this->assertSame('₹ 1,000,000.00', money('1000000'));
        $this->assertSame('₹ 42.10', money('42.1'));
        $this->assertSame('₹ 0.00', money(null));
    }

    public function test_money_rounds_to_two_decimals(): void
    {
        $this->assertSame('₹ 3.33', money(3.333));
        $this->assertSame('₹ 9.99', money(9.991));
    }
}
