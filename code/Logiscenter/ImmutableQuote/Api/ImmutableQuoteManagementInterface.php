<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Logiscenter\ImmutableQuote\Api\Data\ImmutableQuoteInterface;

/**
 * Application service contract for the immutable lock on Adobe Commerce B2B Negotiable Quotes.
 * @api
 */
interface ImmutableQuoteManagementInterface
{
    /**
     * Lock or unlock an existing Negotiable Quote against further modification.
     *
     * @param int $quoteId
     * @param bool $isImmutable
     * @return ImmutableQuoteInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function setImmutable(int $quoteId, bool $isImmutable): ImmutableQuoteInterface;

    /**
     * Retrieve the immutable lock state of a Negotiable Quote.
     *
     * @param int $quoteId
     * @return ImmutableQuoteInterface
     * @throws NoSuchEntityException
     */
    public function get(int $quoteId): ImmutableQuoteInterface;
}
