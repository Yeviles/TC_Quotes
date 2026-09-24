<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\NegotiableQuote\Api\NegotiableQuoteRepositoryInterface;
use Logiscenter\ImmutableQuote\Api\ImmutableQuoteManagementInterface;
use Logiscenter\ImmutableQuote\Api\Data\ImmutableQuoteInterface;
use Psr\Log\LoggerInterface;

/**
 * Toggles the immutable lock on an existing Negotiable Quote.
 *
 * This service intentionally does not create quotes, list them, enable them for
 * checkout, or add items: Magento_NegotiableQuote (Request for Quote, My Quotes,
 * checkout) already owns that lifecycle. This module only adds and enforces the
 * immutable lock on top of it.
 */
class ImmutableQuoteManagement implements ImmutableQuoteManagementInterface
{
    /**
     * Constant status locked
     * @var string
     */
    private const STATUS_LOCKED = 'locked';

    /**
     * Constant status unlocked
     * @var string
     */
    private const STATUS_UNLOCKED = 'unlocked';

    /**
     * Constant status success
     * @var string
     */
    private const STATUS_SUCCESS = 'success';

    /**
     * Constructor
     *
     * @param ImmutableQuoteRepository $repository
     * @param ImmutableQuoteFactory $factory
     * @param NegotiableQuoteRepositoryInterface $negotiableQuoteRepository
     * @param UserContextInterface $userContext
     * @param RateLimiter $rateLimiter
     * @param AuditLogger $auditLogger
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        private readonly ImmutableQuoteRepository $repository,
        private readonly ImmutableQuoteFactory $factory,
        private readonly NegotiableQuoteRepositoryInterface $negotiableQuoteRepository,
        private readonly UserContextInterface $userContext,
        private readonly RateLimiter $rateLimiter,
        private readonly AuditLogger $auditLogger,
        private readonly LoggerInterface $logger,
        private readonly TimezoneInterface $timezone,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function setImmutable(int $quoteId, bool $isImmutable): ImmutableQuoteInterface
    {
        $this->enforceRateLimit('set_immutable');
        if (empty($quoteId)) {
            $this->logger->error("ImmutableQuoteManagement: Invalid quote ID: {$quoteId}");
            throw new InputException(__('A valid quote ID is required.'));
        }
        $this->assertNegotiableQuoteExists($quoteId);

        /**
         * @var ImmutableQuote $extension
         * */
        $extension = $this->factory->create();
        $extension->setQuoteId($quoteId)->setIsImmutable($isImmutable);

        if ($isImmutable) {
            $lockedAt = $this->timezone->date(null, null, false)->format('Y-m-d H:i:s');
            $extension->setLockedAt($lockedAt)->setLockedByUserId((int)$this->userContext->getUserId());
        } else {
            $extension->setLockedAt(null)->setLockedByUserId(null);
        }

        $extension = $this->repository->save($extension);

        $action = $isImmutable ? self::STATUS_LOCKED : self::STATUS_UNLOCKED;
        $this->auditLogger->record($quoteId, $action, self::STATUS_SUCCESS);

        return $extension;
    }

    /**
     * @inheritDoc
     */
    public function get(int $quoteId): ImmutableQuoteInterface
    {
        $this->enforceRateLimit('get');
        $this->assertNegotiableQuoteExists($quoteId);
        return $this->repository->get($quoteId);
    }

    /**
     * Assert that a negotiable quote exists for the given quote ID
     *
     * @param integer $quoteId
     * @return void
     */
    private function assertNegotiableQuoteExists(int $quoteId): void
    {
        try {
            $this->negotiableQuoteRepository->getById($quoteId);
        } catch (NoSuchEntityException $exception) {
            $this->logger->error("ImmutableQuoteManagement: Negotiable quote not found for quote ID: {$quoteId}");
            throw new NoSuchEntityException(__('Quote %1 is not a Negotiable Quote.', $quoteId), $exception);
        }
    }

    /**
     * Enforce rate limit for the given operation
     *
     * @param string $operation
     * @return void
     */
    private function enforceRateLimit(string $operation): void
    {
        $this->rateLimiter->assertAllowed(
            (string)$this->userContext->getUserType() . ':' . (string)$this->userContext->getUserId() . ':' . $operation
        );
    }
}
