<?php

/**
 * @package   Logiscenter_ImmutableQuote
 * @copyright Copyright © 2026
 */

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Logiscenter\ImmutableQuote\Api\Data\ImmutableQuoteInterface;
use Logiscenter\ImmutableQuote\Api\ImmutableQuoteRepositoryInterface;
use Logiscenter\ImmutableQuote\Model\ResourceModel\ImmutableQuote as ResourceImmutableQuote;
use Logiscenter\ImmutableQuote\Model\ResourceModel\ImmutableQuote\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Repository over the is_immutable lock projection of negotiable_quote.
 */
class ImmutableQuoteRepository implements ImmutableQuoteRepositoryInterface
{
    /**
     * Constructor
     *
     * @param ResourceImmutableQuote $resource
     * @param ImmutableQuoteFactory $factory
     * @param CollectionFactory $collectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     * @param SearchResultsFactory $searchResultsFactory
     * @param ImmutableQuoteCache $cache
     */
    public function __construct(
        private readonly ResourceImmutableQuote $resource,
        private readonly ImmutableQuoteFactory $factory,
        private readonly CollectionFactory $collectionFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly SearchResultsFactory $searchResultsFactory,
        private readonly ImmutableQuoteCache $cache
    ) {
    }

    /**
     * @inheritDoc
     */
    public function save(ImmutableQuoteInterface $immutableQuote): ImmutableQuoteInterface
    {
        if (!$immutableQuote instanceof ImmutableQuote) {
            throw new CouldNotSaveException(__('Unsupported immutable quote implementation.'));
        }
        try {
            $stored = $this->factory->create();
            $this->resource->load($stored, $immutableQuote->getQuoteId());
            if (!$stored->getQuoteId()) {
                throw new NoSuchEntityException(
                    __('Quote %1 is not a Negotiable Quote.', $immutableQuote->getQuoteId())
                );
            }
            $this->resource->save($immutableQuote);
            $this->cache->invalidate($immutableQuote->getQuoteId());
        } catch (NoSuchEntityException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(
                __('Unable to save the immutable lock for quote %1.', $immutableQuote->getQuoteId()),
                $exception
            );
        }
        return $immutableQuote;
    }

    /**
     * @inheritDoc
     */
    public function get(int $quoteId): ImmutableQuoteInterface
    {
        $cached = $this->cache->load($quoteId);
        if ($cached !== null) {
            return $cached;
        }
        /**
         * @var ImmutableQuote $entity
         */
        $entity = $this->factory->create();
        $this->resource->load($entity, $quoteId);
        if (!$entity->getQuoteId()) {
            throw new NoSuchEntityException(__('Negotiable quote %1 does not exist.', $quoteId));
        }
        $this->cache->save($entity);
        return $entity;
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria): SearchResultsInterface
    {
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect([
            ImmutableQuoteInterface::QUOTE_ID,
            ImmutableQuoteInterface::IS_IMMUTABLE,
            ImmutableQuoteInterface::LOCKED_AT,
            ImmutableQuoteInterface::LOCKED_BY_USER_ID,
        ]);

        $this->collectionProcessor->process($searchCriteria, $collection);
        $result = $this->searchResultsFactory->create();

        $result->setSearchCriteria($searchCriteria);
        $result->setItems($collection->getItems());
        $result->setTotalCount($collection->getSize());

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function delete(ImmutableQuoteInterface $immutableQuote): bool
    {
        if (!$immutableQuote instanceof ImmutableQuote) {
            throw new CouldNotDeleteException(__('Unsupported immutable quote implementation.'));
        }

        $immutableQuote->setIsImmutable(false)->setLockedAt(null)->setLockedByUserId(null);

        try {
            $this->save($immutableQuote);
        } catch (CouldNotSaveException $exception) {
            throw new CouldNotDeleteException(
                __('Unable to unlock quote %1.', $immutableQuote->getQuoteId()),
                $exception
            );
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $quoteId): bool
    {
        return $this->delete($this->get($quoteId));
    }
}
