<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\AddressComparator;
use Logiscenter\ImmutableQuote\Model\QuoteMutationGuard;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Plugin to guard against direct modifications of the shipping method on quotes.
 */
class ShippingMethodManagementPlugin
{
    /**
     * Constructor
     *
     * @param QuoteMutationGuard $guard
     * @param CartRepositoryInterface $cartRepository
     * @param AddressComparator $addressComparator
     */
    public function __construct(
        private readonly QuoteMutationGuard $guard,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly AddressComparator $addressComparator
    ) {}

    /**
     * Before plugin for setting the shipping method
     *
     * @param mixed $subject
     * @param mixed $cartId
     * @param mixed $carrierCode
     * @param mixed $methodCode
     * @return array
     */
    public function beforeSet(mixed $subject, mixed $cartId, mixed $carrierCode, mixed $methodCode): array
    {
        /**
         * @var Quote $quote
         * */
        $quote = $this->cartRepository->get((int)$cartId);
        $current = $quote->getShippingAddress()?->getShippingMethod();
        if (!$this->addressComparator->isSameShippingMethod($current, (string)$carrierCode, (string)$methodCode)) {
            $this->guard->assertCanModify((int)$cartId, 'shipping_method');
        }
        return [$cartId, $carrierCode, $methodCode];
    }
}
