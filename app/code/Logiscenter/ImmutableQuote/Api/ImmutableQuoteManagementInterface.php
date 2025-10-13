<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api;

use Magento\Framework\Exception\CouldNotSaveException;

interface ImmutableQuoteManagementInterface
{
    /**
     * @param int $customerId
     * @param string $quoteTitle
     *
     * @throws CouldNotSaveException
     *
     * @return int
     */
    public function createEmptyCartForCustomer(int $customerId, string $quoteTitle): int;
}
