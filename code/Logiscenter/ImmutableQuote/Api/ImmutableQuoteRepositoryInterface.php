<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Logiscenter\ImmutableQuote\Api\Data\ImmutableQuoteInterface;

/**
 * Repository contract for immutable quote extension records.
 * @api
 */
interface ImmutableQuoteRepositoryInterface
{
    /**
     * Save an immutable quote extension record.
     *
     * @param ImmutableQuoteInterface $immutableQuote
     * @return ImmutableQuoteInterface
     * @throws CouldNotSaveException
     */
    public function save(ImmutableQuoteInterface $immutableQuote): ImmutableQuoteInterface;

    /**
     * Retrieve an immutable quote extension record by quote identifier.
     *
     * @param int $quoteId
     * @return ImmutableQuoteInterface
     * @throws NoSuchEntityException
     */
    public function get(int $quoteId): ImmutableQuoteInterface;

    /**
     * Retrieve immutable quote extension records matching the criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResultsInterface;

    /**
     * Unlock (clear the immutable flag on) a negotiable quote. Never deletes the quote itself.
     *
     * @param ImmutableQuoteInterface $immutableQuote
     * @return bool True when the quote is unlocked.
     * @throws CouldNotDeleteException
     */
    public function delete(ImmutableQuoteInterface $immutableQuote): bool;

    /**
     * Unlock (clear the immutable flag on) a negotiable quote by quote identifier.
     *
     * @param int $quoteId
     * @return bool True when the quote is unlocked.
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $quoteId): bool;
}
