<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ResourceModel\QuoteMetadata;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Logiscenter\ImmutableQuote\Model\QuoteMetadata;
use Logiscenter\ImmutableQuote\Model\ResourceModel\QuoteMetadata as QuoteMetadataResource;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(QuoteMetadata::class, QuoteMetadataResource::class);
    }
}
