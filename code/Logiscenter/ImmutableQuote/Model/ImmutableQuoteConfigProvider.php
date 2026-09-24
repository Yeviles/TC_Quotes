<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Exposes the immutable lock to checkout JS (window.checkoutConfig)
 */
class ImmutableQuoteConfigProvider implements ConfigProviderInterface
{
    /**
     * Constructor
     *
     * @param CheckoutSession $checkoutSession
     * @param ImmutableQuoteRepository $repository
     * @param LoggerInterface $logger
     */
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly ImmutableQuoteRepository $repository,
        private readonly LoggerInterface $logger
    ) {
    }

    /**
     * Get the checkout configuration for the immutable quote
     *
     * @return array
     */
    public function getConfig(): array
    {
        return [
            'logiscenterIsImmutableQuote' => $this->isImmutable(),
        ];
    }

    /**
     * Is the current quote immutable
     *
     * @return boolean
     */
    private function isImmutable(): bool
    {
        $quoteId = (int)$this->checkoutSession->getQuoteId();
        if ($quoteId <= 0) {
            return false;
        }
        try {
            return $this->repository->get($quoteId)->isImmutable();
        } catch (NoSuchEntityException) {
            $this->logger->error("Quote with ID %d not found {$quoteId}");
            return false;
        }
    }
}
