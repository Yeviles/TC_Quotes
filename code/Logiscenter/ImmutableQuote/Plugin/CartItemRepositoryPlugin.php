<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\QuoteMutationGuard;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Blocks genuine item additions and quantity edits on a locked quote
 */
class CartItemRepositoryPlugin
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
     * Before plugin for saving a cart item
     *
     * @param mixed $subject
     * @param CartItemInterface $cartItem
     * @return array
     */
    public function beforeSave(mixed $subject, CartItemInterface $cartItem): array
    {
        $isNewItem = !$cartItem->getItemId();
        $qtyChanged = $this->hasQtyChanged($cartItem);

        if ($isNewItem || $qtyChanged) {
            $this->guard->assertCanModify(
                (int) $cartItem->getQuoteId(),
                $isNewItem ? 'add_item' : 'update_item_qty'
            );
        }

        return [$cartItem];
    }

    /**
     * Checks if the quantity of the cart item has changed
     *
     * @param CartItemInterface $cartItem
     * @return boolean
     */
    private function hasQtyChanged(CartItemInterface $cartItem): bool
    {
        if (!$cartItem instanceof AbstractModel) {
            return true;
        }

        return $cartItem->dataHasChangedFor('qty');
    }

    /**
     * Before plugin for deleting a cart item by ID
     *
     * @param mixed $subject
     * @param mixed $cartId
     * @param mixed $itemId
     * @return array
     */
    public function beforeDeleteById(mixed $subject, mixed $cartId, mixed $itemId): array
    {
        $this->guard->assertCanModify((int)$cartId, 'delete_item');
        return [$cartId, $itemId];
    }
}
