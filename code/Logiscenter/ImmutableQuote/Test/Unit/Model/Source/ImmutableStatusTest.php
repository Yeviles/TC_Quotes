<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model\Source;

use Logiscenter\ImmutableQuote\Model\Source\ImmutableStatus;
use PHPUnit\Framework\TestCase;

class ImmutableStatusTest extends TestCase
{
    public function testToOptionArrayReturnsMutableAndImmutableOptions(): void
    {
        $options = (new ImmutableStatus())->toOptionArray();

        self::assertCount(2, $options);
        self::assertSame('0', $options[0]['value']);
        self::assertSame('1', $options[1]['value']);
    }
}
