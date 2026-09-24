<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Api\Data\ImmutableQuoteInterface;
use Logiscenter\ImmutableQuote\Model\AuditLogger;
use Logiscenter\ImmutableQuote\Model\ImmutableQuoteRepository;
use Logiscenter\ImmutableQuote\Model\QuoteMutationGuard;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteMutationGuardTest extends TestCase
{
    private ImmutableQuoteRepository&MockObject $repository;

    private AuditLogger&MockObject $auditLogger;

    private QuoteMutationGuard $guard;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ImmutableQuoteRepository::class);
        $this->auditLogger = $this->createMock(AuditLogger::class);
        $this->guard = new QuoteMutationGuard($this->repository, $this->auditLogger);
    }

    public function testAssertCanModifyAllowsMutableQuote(): void
    {
        $extension = $this->createMock(ImmutableQuoteInterface::class);
        $extension->method('isImmutable')->willReturn(false);
        $this->repository->method('get')->with(10)->willReturn($extension);

        $this->auditLogger->expects(self::never())->method('record');

        $this->guard->assertCanModify(10, 'add_item');
        $this->addToAssertionCount(1);
    }

    public function testAssertCanModifyAllowsQuoteWithoutLockRecord(): void
    {
        $this->repository->method('get')->willThrowException(new NoSuchEntityException(__('not found')));

        $this->auditLogger->expects(self::never())->method('record');

        $this->guard->assertCanModify(10, 'add_item');
        $this->addToAssertionCount(1);
    }

    public function testAssertCanModifyBlocksImmutableQuote(): void
    {
        $extension = $this->createMock(ImmutableQuoteInterface::class);
        $extension->method('isImmutable')->willReturn(true);
        $this->repository->method('get')->with(10)->willReturn($extension);

        $this->auditLogger->expects(self::once())
            ->method('record')
            ->with(10, 'modification_blocked', 'denied', ['operation' => 'add_item']);

        $this->expectException(LocalizedException::class);
        $this->guard->assertCanModify(10, 'add_item');
    }

    public function testIsImmutableReturnsFlagFromRepository(): void
    {
        $extension = $this->createMock(ImmutableQuoteInterface::class);
        $extension->method('isImmutable')->willReturn(true);
        $this->repository->method('get')->with(10)->willReturn($extension);

        self::assertTrue($this->guard->isImmutable(10));
    }

    public function testIsImmutableReturnsFalseWhenQuoteHasNoLockRecord(): void
    {
        $this->repository->method('get')->willThrowException(new NoSuchEntityException(__('not found')));

        self::assertFalse($this->guard->isImmutable(10));
    }
}
