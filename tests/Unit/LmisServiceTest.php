<?php

namespace Tests\Unit;

use App\Services\LmisService;
use PHPUnit\Framework\TestCase;

class LmisServiceTest extends TestCase
{
    public function test_days_of_stock_from_amc(): void
    {
        $this->assertSame(0.0, LmisService::daysOfStock(0, 8000));
        $this->assertNull(LmisService::daysOfStock(2000, 0));
        $this->assertSame(7.5, LmisService::daysOfStock(2000, 8000));
    }

    public function test_suggested_quantity_uses_months_of_cover(): void
    {
        // Scenario: AMC 8000, SOH 2000, 3 months cover → need 24000 - 2000 = 22000
        $this->assertSame(22000, LmisService::suggestedQuantity(8000, 2000, 3));
        $this->assertSame(0, LmisService::suggestedQuantity(8000, 30000, 3));
        $this->assertSame(0, LmisService::suggestedQuantity(0, 100, 3));
    }

    public function test_status_from_days(): void
    {
        $this->assertSame('stock_out', LmisService::statusFromDays(0, 0.0));
        $this->assertSame('critical', LmisService::statusFromDays(100, 7.5));
        $this->assertSame('low', LmisService::statusFromDays(100, 25.0));
        $this->assertSame('adequate', LmisService::statusFromDays(100, 60.0));
    }
}
