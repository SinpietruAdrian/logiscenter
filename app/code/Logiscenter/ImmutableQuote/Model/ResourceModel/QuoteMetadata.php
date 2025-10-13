<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class QuoteMetadata extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init('quote_metadata', 'metadata_id');
    }
}
