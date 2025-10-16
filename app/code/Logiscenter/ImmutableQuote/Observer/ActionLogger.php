<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Observer;

use Logiscenter\ImmutableQuote\Api\ActionLogRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterfaceFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Api\Data\CartInterface;
use Psr\Log\LoggerInterface;

class ActionLogger implements ObserverInterface
{
    public function __construct(
        private readonly RemoteAddress $remoteAddress,
        private readonly UserContextInterface $userContext,
        private readonly ActionLogRepositoryInterface $actionLogRepository,
        private readonly ActionLogInterfaceFactory $actionLogFactory,
        private readonly SerializerInterface $serializer,
        private readonly LoggerInterface $logger
    )
    {

    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $logData = [
            ActionLogInterface::ACTION => $observer->getEvent()->getName(),
            ActionLogInterface::IP => $this->remoteAddress->getRemoteAddress(),
            ActionLogInterface::ADDITIONAL_INFORMATION => $this->getEventDataSerialized($observer->getData()),
            ...$this->getUserData()
        ];

        $actionLog = $this->actionLogFactory->create();
        $actionLog->setData($logData);

        try {
            $this->actionLogRepository->save($actionLog);
        } catch (CouldNotSaveException $e) {
            $this->logger->error(
                sprintf(
                    'Failed to save action log. Data: %s. Error message: %s',
                    $this->serializer->serialize($logData),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * @return array
     */
    public function getUserData(): array
    {
        $userType = $this->userContext->getUserType();
        $userId = $this->userContext->getUserId();
        $userData = [ActionLogInterface::USER_TYPE => $userType];

        if ($userType === UserContextInterface::USER_TYPE_ADMIN) {
            $userData[ActionLogInterface::ADMIN_USER_ID] = $userId;
        } elseif ($userType === UserContextInterface::USER_TYPE_CUSTOMER) {
            $userData[ActionLogInterface::CUSTOMER_ID] = $userId;
        }

        return $userData;
    }

    public function getEventDataSerialized(array $eventData): string
    {
        $logData = [];
        //We log all event data for primitives and extract data if we find a quote
        foreach ($eventData as $key => $data) {
            if (is_object($data)) {
                if ($data instanceof CartInterface) {
                    $logData['quote_info'] = [
                        'quote_id' => $data->getId(),
                        'customer_id' => $data->getCustomerId(),
                        'items_count' => $data->getItemsCount()
                    ];
                }
                continue;
            }
            $logData[$key] = $data;
        }

        return $this->serializer->serialize($logData);
    }
}
