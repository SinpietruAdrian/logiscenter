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
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Framework\Event\ManagerInterface;
use PayU\PaymentGateway\Model\Logger\Logger;

class ImmutableQuoteManagement implements ImmutableQuoteManagementInterface
{
    public function __construct(
        private readonly QuoteMetadataRepositoryInterface $quoteMetadataRepository,
        private readonly QuoteMetadataInterfaceFactory $quoteMetadataInterfaceFactory,
        private readonly ResourceConnection $resourceConnection,
        private readonly StoreManagerInterface $storeManager,
        private readonly CartInterfaceFactory $quoteFactory,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly CustomerRepositoryInterface $customerRepository,
        private readonly ManagerInterface $eventManager,
        private readonly CollectionFactory $orderCollectionFactory,
        private readonly Logger $logger
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
            $this->logger->error(
                sprintf(
                    'Failed to create immutable quote for customer id "%s". Error message: %s',
                    $customerId,
                    $e->getMessage()
                )
            );

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

    /**
     * @inheritDoc
     */
    public function activate(int $quoteId, int $customerId): bool
    {
        $quote = $this->quoteRepository->get($quoteId);
        if ((int)$quote->getCustomerId() !== $customerId) {
            throw new LocalizedException(__('Quote does not belong to the logged in customer'));
        }

        if ($this->isAssociatedToOrder($quoteId)) {
            throw new LocalizedException(__('Quote with id "%1" was already used to place an order', $quoteId));
        }

        if ($quote->getIsActive()) {
            return true;
        }

        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();

        try {
            $connection->update(
                $connection->getTableName('quote'),
                ['is_active' => 0],
                ['customer_id = ? and is_active = 1' => $customerId]
            );

            $quote->setIsActive(true);
            $this->quoteRepository->save($quote);

            $connection->commit();
        } catch (\Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Failed to activate quote with id "%s" for customer with id "%s". Error message: %s',
                    $quoteId,
                    $customerId,
                    $e->getMessage()
                )
            );

            $connection->rollBack();
            throw new CouldNotSaveException(
                __('There has been ar error while trying to active the quote. Message: %1', $e->getMessage()),
                $e
            );
        }

        $this->eventManager->dispatch(
            'immutable_quote_activated',
            [
                'quote' => $quote,
                'customer_id' => $customerId
            ]
        );

        return true;
    }

    /**
     * @param int $quoteId
     * @return bool
     */
    private function isAssociatedToOrder(int $quoteId): bool
    {
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('quote_id', $quoteId);
        $collection->setPageSize(1);

        return $collection->getSize() > 0;
    }
}
