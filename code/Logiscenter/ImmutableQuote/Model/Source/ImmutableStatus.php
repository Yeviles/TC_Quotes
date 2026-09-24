<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Options for the "My Quotes" grid immutable column.
 */
class ImmutableStatus implements OptionSourceInterface
{
    /**
     * Retrieve the options for the immutable status column
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return
            [
                ['value' => '0', 'label' => __('Mutable')],
                ['value' => '1', 'label' => __('Immutable')],
            ];
    }
}
