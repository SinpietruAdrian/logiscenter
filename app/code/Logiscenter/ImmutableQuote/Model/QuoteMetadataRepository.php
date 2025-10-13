<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface;
use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterfaceFactory;
use Logiscenter\ImmutableQuote\Api\QuoteMetadataRepositoryInterface;
use Logiscenter\ImmutableQuote\Model\ResourceModel\QuoteMetadata;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteMetadataRepository implements QuoteMetadataRepositoryInterface
{
    public function __construct(
        private readonly QuoteMetadata $quoteMetadataResource,
        private readonly QuoteMetadataInterfaceFactory $quoteMetadataFactory
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function save(QuoteMetadataInterface $quoteMetadata): QuoteMetadataInterface
    {
        try {
            $this->quoteMetadataResource->save($quoteMetadata);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save quote metadata. Message: %1', $e->getMessage()),
                $e
            );
        }

        return $quoteMetadata;
    }

    /**
     * @inheritDoc
     */
    public function getByQuoteId(int $quoteId): QuoteMetadataInterface
    {
        $quoteMetadata = $this->quoteMetadataFactory->create();
        $this->quoteMetadataResource->load($quoteMetadata, $quoteId, 'quote_id');

        if (!$quoteMetadata->getId()) {
            throw new NoSuchEntityException(__('Quote metadata not found for quote with id "%1"', $quoteId));
        }

        return $quoteMetadata;
    }
}
