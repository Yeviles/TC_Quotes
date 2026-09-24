<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class AuditLogger
 */
class AuditLogger
{
    /**
     * Constructor
     *
     * @param ResourceConnection $resource
     * @param UserContextInterface $userContext
     * @param RemoteAddress $remoteAddress
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly ResourceConnection $resource,
        private readonly UserContextInterface $userContext,
        private readonly RemoteAddress $remoteAddress,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Records an audit log entry for the specified quote action and result.
     *
     * @param integer $quoteId
     * @param string $action
     * @param string $result
     * @param array $context
     * @return void
     */
    public function record(int $quoteId, string $action, string $result, array $context = []): void
    {
        $record = [
            'quote_id' => $quoteId,
            'action' => $action,
            'result' => $result,
            'actor_id' => $this->userContext->getUserId(),
            'actor_type' => (string)$this->userContext->getUserType(),
            'remote_ip' => $this->remoteAddress->getRemoteAddress(),
            'context' => $this->serializer->serialize($context)
        ];

        try {
            $this->resource->getConnection()->insert($this->resource->getTableName('logiscenter_immutable_quote_audit'), $record);
        } catch (Throwable $exception) {
            $this->logger->critical(
                'Immutable quote audit persistence failed. Quote ID: {quote_id}',
                [
                    'quote_id' => $quoteId,
                    'exception' => $exception,
                ]
            );
        }
        $this->logger->info(
            'immutable_quote_audit {record}',
            [
                'record' => $this->serializer->serialize($record),
            ]
        );
    }
}
