<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Logiscenter\ImmutableQuote\Api\Data\ActionLogSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class ActionLogSearchResults extends SearchResults implements ActionLogSearchResultsInterface
{
}
