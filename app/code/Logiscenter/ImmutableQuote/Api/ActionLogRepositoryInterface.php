<?php

namespace Logiscenter\ImmutableQuote\Api;

use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogSearchResultsInterface;
use Magento\Framework\Exception\NoSuchEntityException;

interface ActionLogRepositoryInterface
{
    /**
     * Save action log.
     *
     * @param \Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface $actionLog
     * @return \Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface
     * @throws CouldNotSaveException
     */
    public function save(ActionLogInterface $actionLog): ActionLogInterface;

    /**
     * Retrieve action log by id
     *
     * @param int $actionLogId
     * @return \Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $actionLogId): ActionLogInterface;

    /**
     * Retrieve a list of action logs
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Logiscenter\ImmutableQuote\Api\Data\ActionLogSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): ActionLogSearchResultsInterface;

    /**
     * Delete action log by ID.
     *
     * @param int $actionLogId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $actionLogId): bool;

    /**
     * Delete action log
     *
     * @param \Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface $actionLog
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(ActionLogInterface $actionLog): bool;
}
