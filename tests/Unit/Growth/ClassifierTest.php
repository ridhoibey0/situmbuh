<?php

namespace Tests\Unit\Growth;

use App\Services\Growth\Classifier;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClassifierTest extends TestCase
{
    public static function cases(): array
    {
        return [
            ['TB/U', -3.01, 'Sangat Pendek'],
            ['TB/U', -3.0, 'Pendek'],
            ['TB/U', -2.0, 'Normal'],
            ['BB/U', -2.5, 'Gizi Kurang'],
            ['BB/U', 1.0, 'Gizi Baik'],
            ['BB/U', 2.5, 'Gizi Lebih'],
            ['LK/U', -2.5, 'Mikrosefali'],
            ['LK/U', 2.5, 'Makrosefali'],
            ['LL/U', -3.5, 'Gizi Buruk'],
            ['XX/U', 0.0, 'Tidak Diketahui'],
            ['TB/U', null, null],
        ];
    }

    #[DataProvider('cases')]
    public function test_classify(string $parameter, ?float $z, ?string $expected): void
    {
        $this->assertSame($expected, (new Classifier())->classify($parameter, $z));
    }
}
