<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ResourceModel\ActionLog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Logiscenter\ImmutableQuote\Model\ActionLog;
use Logiscenter\ImmutableQuote\Model\ResourceModel\ActionLog as ActionLogResource;

class Collection extends AbstractCollection
{
    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(ActionLog::class, ActionLogResource::class);
    }
}
