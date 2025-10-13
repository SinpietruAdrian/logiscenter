<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterfaceFactory;
use Logiscenter\ImmutableQuote\Api\ImmutableQuoteManagementInterface;
use Logiscenter\ImmutableQuote\Api\QuoteMetadataRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Framework\Event\ManagerInterface;

class ImmutableQuoteManagement  implements ImmutableQuoteManagementInterface
{
    public function __construct(
        private readonly QuoteMetadataRepositoryInterface $quoteMetadataRepository,
        private readonly QuoteMetadataInterfaceFactory $quoteMetadataInterfaceFactory,
        private readonly ResourceConnection $resourceConnection,
        private readonly StoreManagerInterface $storeManager,
        private readonly CartInterfaceFactory $quoteFactory,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ManagerInterface $eventManager
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function createEmptyCartForCustomer(int $customerId, string $quoteTitle): int
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $quote = $this->createEmptyCart($customerId);
            $quoteId = (int)$quote->getId();

            $quoteMetadata = $this->quoteMetadataInterfaceFactory->create();
            $quoteMetadata->setQuoteId($quoteId);
            $quoteMetadata->setTitle($quoteTitle);
            $this->quoteMetadataRepository->save($quoteMetadata);

            $connection->commit();
            $this->eventManager->dispatch('immutable_quote_created', ['quote' => $quote]);

            return $quoteId;
        } catch (CouldNotSaveException|LocalizedException $e) {
            $connection->rollBack();

            throw new CouldNotSaveException(
                __('Failed to create empty immutable cart for customer %1', $customerId),
                $e
            );
        }
    }


    /**
     * @param int $customerId
     * @return CartInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function createEmptyCart(int $customerId): CartInterface
    {
        $storeId = $this->storeManager->getStore()->getStoreId();

        $quote = $this->quoteFactory->create();
        $customer = $this->customerRepository->getById($customerId);

        $quote->setStoreId($storeId);
        $quote->setCustomer($customer);
        $quote->setCustomerIsGuest(0);

        $this->quoteRepository->save($quote);

        return $quote;
    }
}
