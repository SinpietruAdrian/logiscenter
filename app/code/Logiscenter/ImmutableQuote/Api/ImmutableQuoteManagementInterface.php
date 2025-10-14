<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;

interface ImmutableQuoteManagementInterface
{
    /**
     * Create an empty immutable quote for the provided customer
     *
     * @param int $customerId
     * @param string $quoteTitle
     * @throws CouldNotSaveException
     * @return int
     */
    public function createEmptyCartForCustomer(int $customerId, string $quoteTitle): int;

    /**
     * Set the provided quote active for the currently logged in customer
     *
     * @param int $quoteId
     * @param int $customerId
     * @throws LocalizedException|CouldNotSaveException
     * @return bool
     */
    public function activate(int $quoteId, int $customerId): bool;
}
