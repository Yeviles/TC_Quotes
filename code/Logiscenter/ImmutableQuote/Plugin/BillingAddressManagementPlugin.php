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
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Model\Quote;

/**
 * Allows checkout to resubmit the already-negotiated billing address unchanged
 */
class BillingAddressManagementPlugin
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
    ) {
    }

    /**
     * Before plugin for assigning the billing address
     *
     * @param mixed $subject
     * @param mixed $cartId
     * @param AddressInterface $address
     * @param mixed $useForShipping
     * @return array
     */
    public function beforeAssign(mixed $subject, mixed $cartId, AddressInterface $address, mixed $useForShipping = false): array
    {
        /**
         * @var Quote $quote
         */
        $quote = $this->cartRepository->get((int)$cartId);

        $sameBilling = $this->addressComparator->isSameAddress($address, $quote->getBillingAddress());
        $sameShipping = !$useForShipping || $this->addressComparator->isSameAddress($address, $quote->getShippingAddress());

        if (!$sameBilling || !$sameShipping) {
            $this->guard->assertCanModify((int)$cartId, 'billing_address');
        }
        return [$cartId, $address, $useForShipping];
    }
}
