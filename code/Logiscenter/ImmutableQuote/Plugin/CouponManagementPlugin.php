<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\QuoteMutationGuard;

/**
 * Plugin to guard against direct modifications of coupon codes on quotes
 */
class CouponManagementPlugin
{
    /**
     * Constructor
     *
     * @param QuoteMutationGuard $guard
     */
    public function __construct(
        private readonly QuoteMutationGuard $guard
    ) {
    }

    /**
     * Checks if the coupon can be set for the given cart (quote)
     *
     * @param mixed $subject
     * @param mixed $cartId
     * @param mixed $couponCode
     * @return array
     */
    public function beforeSet(mixed $subject, mixed $cartId, mixed $couponCode): array
    {
        $this->guard->assertCanModify((int)$cartId, 'set_coupon');
        return [$cartId, $couponCode];
    }

    /**
     * Checks if the coupon can be removed for the given cart (quote)
     *
     * @param mixed $subject
     * @param mixed $cartId
     * @return array
     */
    public function beforeRemove(mixed $subject, mixed $cartId): array
    {
        $this->guard->assertCanModify((int)$cartId, 'remove_coupon');
        return [$cartId];
    }
}
