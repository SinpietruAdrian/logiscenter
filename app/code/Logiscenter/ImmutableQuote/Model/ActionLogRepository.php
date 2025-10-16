<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Logiscenter\ImmutableQuote\Api\ActionLogRepositoryInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogSearchResultsInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Logiscenter\ImmutableQuote\Model\ResourceModel\ActionLog;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Logiscenter\ImmutableQuote\Model\ActionLogFactory;
use Logiscenter\ImmutableQuote\Model\ResourceModel\ActionLog\CollectionFactory;

class ActionLogRepository implements ActionLogRepositoryInterface
{
    public function __construct(
        private readonly ActionLog $actionLogResource,
        private readonly ActionLogFactory $actionLogFactory,
        private readonly CollectionFactory $actionLogCollectionFactory,
        private readonly CollectionProcessorInterface $collectionProcessor,
        private readonly ActionLogSearchResultsInterfaceFactory $actionLogSearchResultsFactory
    )
    {

    }

    /**
     * @inheritDoc
     */
    public function save(ActionLogInterface $actionLog): ActionLogInterface
    {
        try {
            $this->actionLogResource->save($actionLog);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save action log. Message: %1', $exception->getMessage())
            );
        }

        return $actionLog;
    }

    /**
     * @inheritDoc
     */
    public function getById(int $actionLogId): ActionLogInterface
    {
        $actionLog = $this->actionLogFactory->create();
        $this->actionLogResource->load($actionLog, $actionLogId);
        if (!$actionLog->getId()) {
            throw new NoSuchEntityException(__('Action log with id "%1" not found', $actionLogId));
        }

        return $actionLog;
    }

    public function getList(SearchCriteriaInterface $searchCriteria): ActionLogSearchResultsInterface
    {
        $collection = $this->actionLogCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult = $this->actionLogSearchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @inheritDoc
     */
    public function delete(ActionLogInterface $actionLog): bool
    {
        try {
            $this->actionLogResource->delete($actionLog);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(int $actionLogId): bool
    {
        return $this->delete($this->getById($actionLogId));
    }
}
