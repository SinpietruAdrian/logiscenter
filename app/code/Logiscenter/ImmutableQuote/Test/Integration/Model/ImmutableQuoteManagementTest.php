<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Integration\Model;

use Logiscenter\ImmutableQuote\Model\ImmutableQuoteManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class ImmutableQuoteManagementTest extends TestCase
{
    private ImmutableQuoteManagement $immutableQuoteManagement;
    private CartRepositoryInterface $quoteRepository;
    private CustomerRepositoryInterface $customerRepository;

    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->immutableQuoteManagement = $objectManager->create(ImmutableQuoteManagement::class);
        $this->quoteRepository = $objectManager->create(CartRepositoryInterface::class);
        $this->customerRepository = $objectManager->create(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testActivateQuoteSuccessfully(): void
    {
        $customer = $this->customerRepository->getById(1);

        $quoteId = $this->immutableQuoteManagement->createEmptyCartForCustomer(
            (int)$customer->getId(),
            'Test Quote'
        );
        $quote = $this->quoteRepository->get($quoteId);

        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);

        $result = $this->immutableQuoteManagement->activate((int)$quote->getId(), (int)$customer->getId());

        $this->assertTrue($result, 'Quote should be activated');

        $updatedQuote = $this->quoteRepository->get((int)$quote->getId());
        $this->assertTrue((bool)$updatedQuote->getIsActive(), 'Quote "is_active" flag should be true');
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testActivateThrowsExceptionIfQuoteNotBelongToCustomer(): void
    {
        $this->expectException(LocalizedException::class);

        $customer = $this->customerRepository->getById(1);
        $quoteId = $this->immutableQuoteManagement->createEmptyCartForCustomer((int)$customer->getId(), 'Test Quote');
        $quote = $this->quoteRepository->get($quoteId);

        $wrongCustomerId = $customer->getId() + 1000;

        $this->immutableQuoteManagement->activate((int)$quote->getId(), $wrongCustomerId);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     */
    public function testActivateThrowsExceptionIfQuoteAssociatedToOrder(): void
    {
        $this->expectException(LocalizedException::class);

        $customer = $this->customerRepository->getById(1);
        $quoteId = $this->immutableQuoteManagement->createEmptyCartForCustomer(
            (int)$customer->getId(),
            'Test Quote'
        );
        $quote = $this->quoteRepository->get($quoteId);

        $quote->setIsActive(false);
        $this->quoteRepository->save($quote);

        $connection = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\App\ResourceConnection::class)
            ->getConnection();
        $connection->insert($connection->getTableName('sales_order'), [
            'quote_id' => $quote->getId(),
            'status' => 'processing',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'grand_total' => 100,
            'base_grand_total' => 100,
            'customer_id' => $customer->getId(),
        ]);

        $this->immutableQuoteManagement->activate((int)$quote->getId(), (int)$customer->getId());
    }
}
