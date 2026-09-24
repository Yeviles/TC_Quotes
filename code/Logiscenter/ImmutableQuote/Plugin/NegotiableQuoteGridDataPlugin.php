<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\ImmutableQuoteRepository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\NegotiableQuote\Ui\DataProvider\DataProvider;

/**
 * Surfaces the immutable lock flag in the native "My Quotes" grid
 */
class NegotiableQuoteGridDataPlugin
{
    /**
     * Constructor
     *
     * @param ImmutableQuoteRepository $repository
     */
    public function __construct(
        private readonly ImmutableQuoteRepository $repository
    ) {
    }

    /**
     * Adds the immutable lock flag to each quote in the "My Quotes" grid
     *
     * @param DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetData(DataProvider $subject, array $result): array
    {
        if (empty($result['items'])) {
            return $result;
        }
        foreach ($result['items'] as &$item) {
            $quoteId = (int)($item['entity_id'] ?? $item['quote_id'] ?? 0);
            $item['is_immutable'] = $quoteId ? $this->isImmutable($quoteId) : 0;
        }
        unset($item);
        return $result;
    }

    /**
     * Checks if the given quote is immutable
     *
     * @param int $quoteId
     * @return int
     */
    private function isImmutable(int $quoteId): int
    {
        try {
            return (int)$this->repository->get($quoteId)->isImmutable();
        } catch (NoSuchEntityException) {
            return 0;
        }
    }
}
