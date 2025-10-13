<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api;

use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface QuoteMetadataRepositoryInterface
{
    /**
     * @param \Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface $warranty
     * @return \Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface
     * @throws LocalizedException
     */
    public function save(QuoteMetadataInterface $quoteMetadata): QuoteMetadataInterface;

    /**
     * @param int $quoteId
     * @return QuoteMetadataInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId(int $quoteId): QuoteMetadataInterface;
}
