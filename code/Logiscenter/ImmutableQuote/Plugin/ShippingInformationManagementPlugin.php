<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\AddressComparator;
use Logiscenter\ImmutableQuote\Model\QuoteMutationGuard;
use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

/**
 * Plugin to guard against direct modifications of shipping information on quotes
 */
class ShippingInformationManagementPlugin
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
     * Before plugin for saving shipping information
     *
     * @param mixed $subject
     * @param mixed $cartId
     * @param ShippingInformationInterface $addressInformation
     * @return array
     */
    public function beforeSaveAddressInformation(mixed $subject, mixed $cartId, ShippingInformationInterface $addressInformation): array
    {
        /**
         *  @var Quote $quote
         */
        $quote = $this->cartRepository->get((int)$cartId);
        $currentShipping = $quote->getShippingAddress();

        $sameShippingAddress = $this->addressComparator->isSameAddress($addressInformation->getShippingAddress(), $currentShipping);

        $sameBillingAddress = $addressInformation->getBillingAddress() === null
            || $this->addressComparator->isSameAddress($addressInformation->getBillingAddress(), $quote->getBillingAddress());

        $sameMethod = $this->addressComparator->isSameShippingMethod(
            $currentShipping?->getShippingMethod(),
            (string)$addressInformation->getShippingCarrierCode(),
            (string)$addressInformation->getShippingMethodCode()
        );

        if (!$sameShippingAddress || !$sameBillingAddress || !$sameMethod) {
            $this->guard->assertCanModify((int)$cartId, 'shipping_address');
        }
        return [$cartId, $addressInformation];
    }
}
