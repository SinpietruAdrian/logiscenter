<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model;

use Logiscenter\ImmutableQuote\Model\ImmutableQuoteManagement;
use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterfaceFactory;
use Logiscenter\ImmutableQuote\Api\QuoteMetadataRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\Store;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class ImmutableQuoteManagementTest extends TestCase
{
    private AdapterInterface $connection;
    private ResourceConnection $resourceConnection;
    private QuoteMetadataRepositoryInterface $quoteMetadataRepository;
    private QuoteMetadataInterfaceFactory $quoteMetadataFactory;
    private StoreManagerInterface $storeManager;
    private CartInterfaceFactory $quoteFactory;
    private CartRepositoryInterface $quoteRepository;
    private CustomerRepositoryInterface $customerRepository;
    private ManagerInterface $eventManager;
    private CollectionFactory $orderCollectionFactory;
    private LoggerInterface $logger;
    private Quote $quote;
    private Collection $orderCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->createMock(AdapterInterface::class);
        $this->connection->expects($this->any())->method('beginTransaction');
        $this->connection->expects($this->any())->method('rollBack');

        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->resourceConnection->method('getConnection')->willReturn($this->connection);

        $this->quoteMetadataRepository = $this->createMock(QuoteMetadataRepositoryInterface::class);
        $this->quoteMetadataFactory = $this->createMock(QuoteMetadataInterfaceFactory::class);
        $quoteMetadata = $this->createMock(QuoteMetadataInterface::class);
        $this->quoteMetadataFactory->method('create')->willReturn($quoteMetadata);

        $store = $this->createMock(Store::class);
        $this->storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManager->method('getStore')->willReturn($store);

        $this->quoteFactory = $this->createMock(CartInterfaceFactory::class);
        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->eventManager = $this->createMock(ManagerInterface::class);
        $this->orderCollectionFactory = $this->createMock(CollectionFactory::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);

        //Don't know how to get around this since getCustomerId is not defined on the object :)
        $this->quote = $this->getMockBuilder(Quote::class)
            ->addMethods(['getCustomerId'])
            ->onlyMethods(['getIsActive'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderCollection = $this->createMock(Collection::class);
    }

    public function testCreateEmptyCartForCustomerRollsBackOnError(): void
    {
        $customerId = 123;
        $quoteTitle = 'Test Quote';

        $this->customerRepository->method('getById')
            ->with($customerId)
            ->willThrowException(new NoSuchEntityException(__('Customer not found')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed to create immutable quote for customer id'));

        $management = new ImmutableQuoteManagement(
            $this->quoteMetadataRepository,
            $this->quoteMetadataFactory,
            $this->resourceConnection,
            $this->storeManager,
            $this->quoteFactory,
            $this->quoteRepository,
            $this->customerRepository,
            $this->eventManager,
            $this->orderCollectionFactory,
            $this->logger
        );

        $this->expectException(CouldNotSaveException::class);

        $management->createEmptyCartForCustomer($customerId, $quoteTitle);
    }

    public function testCreateEmptyCartForCustomerReturnsQuoteId(): void
    {
        $customerId = 123;
        $quoteTitle = 'Test Quote';

        $quote = $this->createMock(CartInterface::class);
        $quote->method('getId')->willReturn(999);

        $this->quoteFactory->method('create')->willReturn($quote);

        $customer = $this->createMock(CustomerInterface::class);
        $this->customerRepository->method('getById')
            ->with($customerId)
            ->willReturn($customer);

        $this->quoteRepository->expects($this->once())->method('save')->with($quote);

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('immutable_quote_created', ['quote' => $quote]);

        $management = new ImmutableQuoteManagement(
            $this->quoteMetadataRepository,
            $this->quoteMetadataFactory,
            $this->resourceConnection,
            $this->storeManager,
            $this->quoteFactory,
            $this->quoteRepository,
            $this->customerRepository,
            $this->eventManager,
            $this->orderCollectionFactory,
            $this->logger
        );

        $result = $management->createEmptyCartForCustomer($customerId, $quoteTitle);

        $this->assertSame(999, $result);
    }

    public function testActivateReturnsTrueWhenAllGood(): void
    {
        $quoteId = 101;
        $customerId = 123;


        $this->quote->method('getCustomerId')->willReturn($customerId);
        $this->quote->method('getIsActive')->willReturn(false);
        $this->quoteFactory->method('create')->willReturn($this->quote);

        $this->quoteRepository->method('get')->with($quoteId)->willReturn($this->quote);
        $this->quoteRepository->expects($this->once())->method('save')->with($this->quote);

        $this->orderCollectionFactory->method('create')->willReturn($this->orderCollection);

        $this->connection->expects($this->once())
            ->method('update')
            ->with(
                $this->anything(),
                ['is_active' => 0],
                ['customer_id = ? and is_active = 1' => $customerId]
            );
        $this->connection->expects($this->once())->method('beginTransaction');
        $this->connection->expects($this->once())->method('commit');

        $this->eventManager->expects($this->once())
            ->method('dispatch')
            ->with('immutable_quote_activated', ['quote' => $this->quote, 'customer_id' => $customerId]);

        $management = new ImmutableQuoteManagement(
            $this->quoteMetadataRepository,
            $this->quoteMetadataFactory,
            $this->resourceConnection,
            $this->storeManager,
            $this->quoteFactory,
            $this->quoteRepository,
            $this->customerRepository,
            $this->eventManager,
            $this->orderCollectionFactory,
            $this->logger
        );

        $result = $management->activate($quoteId, $customerId);

        $this->assertTrue($result);
    }

}
