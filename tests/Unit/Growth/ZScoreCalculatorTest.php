<?php

namespace Tests\Unit\Growth;

use App\Services\Growth\ZScoreCalculator;
use PHPUnit\Framework\TestCase;

class ZScoreCalculatorTest extends TestCase
{
    private function calculator(): ZScoreCalculator
    {
        return new ZScoreCalculator([
            'male|TB/U|12' => ['sd_median' => 75.7, 'sd_1_positif' => 77.9, 'sd_1_negatif' => 73.4],
            'male|BB/U|0' => ['sd_median' => 3.3, 'sd_1_positif' => 3.7, 'sd_1_negatif' => 3.3],
        ]);
    }

    public function test_median_gives_zero(): void
    {
        $this->assertSame(0.0, $this->calculator()->calculate('male', 'TB/U', 12, 75.7));
    }

    public function test_below_median_uses_negative_sd(): void
    {
        // (71.0 - 75.7) / (75.7 - 73.4) = -2.04
        $this->assertSame(-2.04, $this->calculator()->calculate('male', 'TB/U', 12, 71.0));
    }

    public function test_above_median_uses_positive_sd(): void
    {
        // (77.9 - 75.7) / (77.9 - 75.7) = 1.0
        $this->assertSame(1.0, $this->calculator()->calculate('male', 'TB/U', 12, 77.9));
    }

    public function test_missing_reference_returns_null(): void
    {
        $this->assertNull($this->calculator()->calculate('male', 'TB/U', 99, 80));
        $this->assertNull($this->calculator()->calculate('female', 'TB/U', 12, 70));
    }

    public function test_null_value_or_gender_returns_null(): void
    {
        $this->assertNull($this->calculator()->calculate('male', 'TB/U', 12, null));
        $this->assertNull($this->calculator()->calculate(null, 'TB/U', 12, 70));
    }

    public function test_zero_sd_width_returns_null_instead_of_dividing_by_zero(): void
    {
        $this->assertNull($this->calculator()->calculate('male', 'BB/U', 0, 3.0));
    }
}
