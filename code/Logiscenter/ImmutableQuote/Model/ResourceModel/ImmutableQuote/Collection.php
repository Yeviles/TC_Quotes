<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ResourceModel\ImmutableQuote;

use Logiscenter\ImmutableQuote\Model\ImmutableQuote;
use Logiscenter\ImmutableQuote\Model\ResourceModel\ImmutableQuote as ResourceImmutableQuote;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Undocumented class for the collection of immutable quotes
 */
class Collection extends AbstractCollection
{
    /**
     * The primary key field for the collection
     *
     * @var string
     */
    protected $_idFieldName = 'quote_id';

    /**
     * Initialize the collection model and resource model
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(ImmutableQuote::class, ResourceImmutableQuote::class);
    }
}
