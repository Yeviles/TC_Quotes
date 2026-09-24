<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Model\AuditLogger;
use Logiscenter\ImmutableQuote\Model\ImmutableQuote;
use Logiscenter\ImmutableQuote\Model\ImmutableQuoteFactory;
use Logiscenter\ImmutableQuote\Model\ImmutableQuoteManagement;
use Logiscenter\ImmutableQuote\Model\ImmutableQuoteRepository;
use Logiscenter\ImmutableQuote\Model\RateLimiter;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Magento\NegotiableQuote\Api\Data\NegotiableQuoteInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ImmutableQuoteManagementTest extends TestCase
{
    private ImmutableQuoteRepository&MockObject $repository;

    private ImmutableQuoteFactory&MockObject $factory;

    private NegotiableQuoteRepositoryInterface&MockObject $negotiableQuoteRepository;

    private UserContextInterface&MockObject $userContext;

    private RateLimiter&MockObject $rateLimiter;

    private AuditLogger&MockObject $auditLogger;

    private LoggerInterface&MockObject $logger;

    private TimezoneInterface&MockObject $timezone;

    private ImmutableQuoteManagement $management;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ImmutableQuoteRepository::class);
        $this->factory = $this->createMock(ImmutableQuoteFactory::class);
        $this->negotiableQuoteRepository = $this->createMock(NegotiableQuoteRepositoryInterface::class);
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->rateLimiter = $this->createMock(RateLimiter::class);
        $this->auditLogger = $this->createMock(AuditLogger::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->timezone = $this->createMock(TimezoneInterface::class);

        $this->userContext->method('getUserId')->willReturn(7);
        $this->userContext->method('getUserType')->willReturn(2);
        $this->negotiableQuoteRepository->method('getById')->willReturn(
            $this->createMock(NegotiableQuoteInterface::class)
        );

        $this->management = new ImmutableQuoteManagement(
            $this->repository,
            $this->factory,
            $this->negotiableQuoteRepository,
            $this->userContext,
            $this->rateLimiter,
            $this->auditLogger,
            $this->logger,
            $this->timezone
        );
    }

    public function testSetImmutableThrowsInputExceptionForEmptyQuoteId(): void
    {
        $this->expectException(InputException::class);
        $this->management->setImmutable(0, true);
    }

    public function testSetImmutableThrowsWhenNoNegotiableQuoteExists(): void
    {
        $this->negotiableQuoteRepository->method('getById')
            ->willThrowException(new NoSuchEntityException(__('not found')));

        $this->expectException(NoSuchEntityException::class);
        $this->management->setImmutable(10, true);
    }

    public function testSetImmutableEnforcesRateLimitBeforeAnythingElse(): void
    {
        $this->rateLimiter->expects(self::once())
            ->method('assertAllowed')
            ->with('2:7:set_immutable');
        $this->negotiableQuoteRepository->method('getById')
            ->willThrowException(new NoSuchEntityException(__('not found')));

        try {
            $this->management->setImmutable(10, true);
        } catch (NoSuchEntityException) {
            // expected, rate limit assertion already verified above
        }
    }

    public function testSetImmutableLocksQuoteAndRecordsAudit(): void
    {
        $extension = $this->createMock(ImmutableQuote::class);
        $extension->method('setQuoteId')->with(10)->willReturnSelf();
        $extension->method('setIsImmutable')->with(true)->willReturnSelf();
        $extension->method('setLockedAt')->with('2026-09-23 10:00:00')->willReturnSelf();
        $extension->method('setLockedByUserId')->with(7)->willReturnSelf();
        $this->factory->method('create')->willReturn($extension);
        $this->timezone->method('date')->willReturn(new \DateTime('2026-09-23 10:00:00'));
        $this->repository->method('save')->with($extension)->willReturn($extension);

        $this->auditLogger->expects(self::once())
            ->method('record')
            ->with(10, 'locked', 'success');

        self::assertSame($extension, $this->management->setImmutable(10, true));
    }

    public function testSetImmutableUnlocksQuoteAndRecordsAudit(): void
    {
        $extension = $this->createMock(ImmutableQuote::class);
        $extension->method('setQuoteId')->with(10)->willReturnSelf();
        $extension->method('setIsImmutable')->with(false)->willReturnSelf();
        $extension->method('setLockedAt')->with(null)->willReturnSelf();
        $extension->method('setLockedByUserId')->with(null)->willReturnSelf();
        $this->factory->method('create')->willReturn($extension);
        $this->repository->method('save')->with($extension)->willReturn($extension);

        $this->auditLogger->expects(self::once())
            ->method('record')
            ->with(10, 'unlocked', 'success');

        self::assertSame($extension, $this->management->setImmutable(10, false));
    }

    public function testGetEnforcesRateLimitAndDelegatesToRepository(): void
    {
        $extension = $this->createMock(ImmutableQuote::class);
        $this->rateLimiter->expects(self::once())->method('assertAllowed')->with('2:7:get');
        $this->repository->method('get')->with(10)->willReturn($extension);

        self::assertSame($extension, $this->management->get(10));
    }
}
