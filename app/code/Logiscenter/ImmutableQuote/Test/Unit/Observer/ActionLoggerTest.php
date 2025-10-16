<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Observer;

use Logiscenter\ImmutableQuote\Model\ActionLog;
use Logiscenter\ImmutableQuote\Observer\ActionLogger;
use Logiscenter\ImmutableQuote\Api\ActionLogRepositoryInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterfaceFactory;
use Magento\Framework\Event;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;

class ActionLoggerTest extends TestCase
{
    private RemoteAddress $remoteAddress;
    private UserContextInterface $userContext;
    private ActionLogRepositoryInterface $repository;
    private ActionLogInterfaceFactory $factory;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;
    private ActionLog $actionLog;

    static public function userTypeProvider(): array
    {
        return [
            'admin user' => [
                UserContextInterface::USER_TYPE_ADMIN,
                42,
                ActionLogInterface::ADMIN_USER_ID
            ],
            'customer user' => [
                UserContextInterface::USER_TYPE_CUSTOMER,
                99,
                ActionLogInterface::CUSTOMER_ID
            ]
        ];
    }

    protected function setUp(): void
    {
        $this->remoteAddress = $this->createMock(RemoteAddress::class);
        $this->userContext = $this->createMock(UserContextInterface::class);
        $this->repository = $this->createMock(ActionLogRepositoryInterface::class);
        $this->factory = $this->createMock(ActionLogInterfaceFactory::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->actionLog = $this->createMock(ActionLog::class);
        $this->factory->method('create')->willReturn($this->actionLog);
        $this->serializer->method('serialize')->willReturn('serialized_data');

        $this->remoteAddress->method('getRemoteAddress')->willReturn('127.0.0.1');
    }

    /**
     * @dataProvider userTypeProvider
     */
    public function testUserActionLogging(int $userType, int $userId, string $idKey): void
    {
        $this->userContext->method('getUserType')->willReturn($userType);
        $this->userContext->method('getUserId')->willReturn($userId);

        $quoteMock = $this->createMock(Quote::class);
        $quoteMock->method('getId')->willReturn(123);
        $quoteMock->method('getItemsCount')->willReturn(7);

        $eventMock = $this->createMock(Event::class);
        $eventMock->method('getName')->willReturn('sample_event');

        $observerMock = $this->createMock(Observer::class);
        $observerMock->method('getEvent')->willReturn($eventMock);
        $observerMock->method('getData')->willReturn([
            'dummy' => 'data',
            'quote' => $quoteMock
        ]);

        $this->actionLog->expects($this->once())->method('setData')->with(
            $this->callback(function ($data) use ($userType, $userId, $idKey) {
                return $data[ActionLogInterface::USER_TYPE] === $userType
                    && $data[$idKey] === $userId
                    && $data[ActionLogInterface::IP] === '127.0.0.1'
                    && $data[ActionLogInterface::ACTION] === 'sample_event'
                    && $data[ActionLogInterface::ADDITIONAL_INFORMATION] === 'serialized_data';
            })
        );

        $this->repository->expects($this->once())->method('save')->with($this->actionLog);

        $loggerInstance = new ActionLogger(
            $this->remoteAddress,
            $this->userContext,
            $this->repository,
            $this->factory,
            $this->serializer,
            $this->logger
        );

        $loggerInstance->execute($observerMock);
    }

    public function testErrorIsLoggedWhenSaveFails(): void
    {
        $this->userContext->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_ADMIN);
        $this->userContext->method('getUserId')->willReturn(42);

        $this->repository->method('save')->willThrowException(new CouldNotSaveException(__('Test save error')));

        $this->logger->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed to save action log. Data: serialized_data. Error message: Test save error'));

        $observerMock = $this->createMock(Observer::class);
        $eventMock = $this->createMock(Event::class);
        $observerMock->method('getEvent')->willReturn($eventMock);
        $observerMock->method('getData')->willReturn([]);
        $eventMock->method('getName')->willReturn('sample_event');

        $loggerInstance = new ActionLogger(
            $this->remoteAddress,
            $this->userContext,
            $this->repository,
            $this->factory,
            $this->serializer,
            $this->logger
        );

        $loggerInstance->execute($observerMock);
    }
}
