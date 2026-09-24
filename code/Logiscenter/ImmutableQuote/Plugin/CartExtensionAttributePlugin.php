<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\ImmutableQuoteRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartExtensionFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartSearchResultsInterface;

/**
 * Lifts the is_immutable lock onto the quote itself as an extension attribute
 */
class CartExtensionAttributePlugin
{
    /**
     * Constructor
     *
     * @param ImmutableQuoteRepository $repository
     * @param CartExtensionFactory $extensionFactory
     */
    public function __construct(
        private readonly ImmutableQuoteRepository $repository,
        private readonly CartExtensionFactory $extensionFactory
    ) {}

    /**
     * After plugin for retrieving a quote
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGet(CartRepositoryInterface $subject, CartInterface $quote): CartInterface
    {
        return $this->addImmutableFlag($quote);
    }

    /**
     * After plugin for retrieving the active quote
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGetActive(CartRepositoryInterface $subject, CartInterface $quote): CartInterface
    {
        return $this->addImmutableFlag($quote);
    }

    /**
     * After plugin for retrieving the active quote for a customer
     *
     * @param CartRepositoryInterface $subject
     * @param CartInterface $quote
     * @return CartInterface
     */
    public function afterGetActiveForCustomer(CartRepositoryInterface $subject, CartInterface $quote): CartInterface
    {
        return $this->addImmutableFlag($quote);
    }

    /**
     * After plugin for retrieving a list of quotes
     *
     * @param CartRepositoryInterface $subject
     * @param CartSearchResultsInterface $result
     * @return CartSearchResultsInterface
     */
    public function afterGetList(CartRepositoryInterface $subject, CartSearchResultsInterface $result): CartSearchResultsInterface
    {
        foreach ($result->getItems() as $quote) {
            $this->addImmutableFlag($quote);
        }
        return $result;
    }

    /**
     * Hydrates the quote with the immutable flag in its extension attributes
     *
     * @param CartInterface $quote
     * @return CartInterface
     */
    private function addImmutableFlag(CartInterface $quote): CartInterface
    {
        $extensionAttributes = $quote->getExtensionAttributes() ?? $this->extensionFactory->create();
        $extensionAttributes->setIsImmutable($this->isImmutable((int)$quote->getId()));
        $quote->setExtensionAttributes($extensionAttributes);
        return $quote;
    }

    /**
     * Checks if the quote is immutable
     *
     * @param integer $quoteId
     * @return boolean
     */
    private function isImmutable(int $quoteId): bool
    {
        if ($quoteId <= 0) {
            return false;
        }
        try {
            return $this->repository->get($quoteId)->isImmutable();
        } catch (NoSuchEntityException) {
            return false;
        }
    }
}
