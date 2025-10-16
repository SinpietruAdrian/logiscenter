<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class ActionLog extends AbstractDb
{
    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init('immutable_quote_action_log', 'id');
    }
}
