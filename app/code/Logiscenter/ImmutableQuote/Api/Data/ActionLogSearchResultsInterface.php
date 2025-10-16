<?php

namespace Logiscenter\ImmutableQuote\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface;

interface ActionLogSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get action logs list.
     *
     * @return \Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface[]
     */
    public function getItems();

    /**
     * Set action logs list.
     *
     * @param \Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
