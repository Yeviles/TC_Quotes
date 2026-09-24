<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Model\AuditLogger;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\SerializerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class AuditLoggerTest extends TestCase
{
    private ResourceConnection&MockObject $resource;

    private UserContextInterface&MockObject $userContext;

    private RemoteAddress&MockObject $remoteAddress;

    private SerializerInterface&MockObject $serializer;

    private LoggerInterface&MockObject $logger;

    private AuditLogger $auditLogger;

    protected function setUp(): void
    {
        $this->resource = $this->createMock(ResourceConnection::class);
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->remoteAddress = $this->createMock(RemoteAddress::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->userContext->method('getUserId')->willReturn(5);
        $this->userContext->method('getUserType')->willReturn(2);
        $this->remoteAddress->method('getRemoteAddress')->willReturn('127.0.0.1');
        $this->serializer->method('serialize')->willReturnCallback(
            static fn (array $data): string => json_encode($data)
        );

        $this->auditLogger = new AuditLogger(
            $this->resource,
            $this->userContext,
            $this->remoteAddress,
            $this->serializer,
            $this->logger
        );
    }

    public function testRecordInsertsRowAndLogsInfo(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')->willReturn($connection);
        $this->resource->method('getTableName')
            ->with('logiscenter_immutable_quote_audit')
            ->willReturn('logiscenter_immutable_quote_audit');

        $connection->expects(self::once())
            ->method('insert')
            ->with(
                'logiscenter_immutable_quote_audit',
                self::callback(static function (array $record): bool {
                    return $record['quote_id'] === 10
                        && $record['action'] === 'locked'
                        && $record['result'] === 'success'
                        && $record['actor_id'] === 5
                        && $record['actor_type'] === '2'
                        && $record['remote_ip'] === '127.0.0.1';
                })
            );

        $this->logger->expects(self::never())->method('critical');
        $this->logger->expects(self::once())->method('info');

        $this->auditLogger->record(10, 'locked', 'success');
    }

    public function testRecordLogsCriticalWhenPersistenceFails(): void
    {
        $connection = $this->createMock(AdapterInterface::class);
        $this->resource->method('getConnection')->willReturn($connection);
        $this->resource->method('getTableName')->willReturn('logiscenter_immutable_quote_audit');
        $connection->method('insert')->willThrowException(new \RuntimeException('DB down'));

        $this->logger->expects(self::once())->method('critical');
        $this->logger->expects(self::once())->method('info');

        $this->auditLogger->record(10, 'locked', 'success');
    }
}
