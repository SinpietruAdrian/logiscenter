<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\ViewModel;

use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Helper\Session\CurrentCustomer;

class CustomerQuotes implements ArgumentInterface
{
    private array $quoteMetadataCache = [];

    public function __construct(
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly SearchCriteriaBuilder $searchCriteriaBuilder,
        private readonly FilterBuilder $filterBuilder,
        private readonly Validator $validator,
        private readonly CurrentCustomer $currentCustomer
    )
    {

    }

    /**
     * @return CartInterface[]
     */
    public function getCustomerQuotes(): array
    {
        $customerIdFilter = $this->filterBuilder
            ->setField('customer_id')
            ->setValue($this->currentCustomer->getCustomerId())
            ->setConditionType('eq')
            ->create();
        //Fetch only quotes that have not already been used to place an order
        $reservedOrderIdFilter = $this->filterBuilder
            ->setField('reserved_order_id')
            ->setConditionType('null')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$customerIdFilter]);
        $this->searchCriteriaBuilder->addFilters([$reservedOrderIdFilter]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        return $this->quoteRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @param CartInterface $quote
     * @return bool
     */
    public function isImmutableQuote(CartInterface $quote): bool
    {
        if ($isImmutable = $this->validator->isImmutableQuote($quote)) {
            $this->quoteMetadataCache[$quote->getId()] = $quote->getExtensionAttributes()->getMetadata();
        }

        return $isImmutable;
    }

    public function getQuoteMetadataByCode(int $quoteId, string $code): ?string
    {
        $method = 'get' . strtoupper($code);
        return $this->quoteMetadataCache[$quoteId]->$method();
    }
}
