<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Points to the negotiable_quote table in the database
 */
class ImmutableQuote extends AbstractDb
{
    /**
     * Initialize the resource model and specify the table and primary key field
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('negotiable_quote', 'quote_id');
    }
}
