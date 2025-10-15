<?php

namespace Logiscenter\ImmutableQuote\Controller\Quote;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Registry;

class View extends Action implements HttpGetActionInterface
{
    public function __construct(
        private readonly Session $customerSession,
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly Registry $registry,
        Context $context
    )
    {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setPath('customer/account/login');
        }

        if (!$this->canViewQuote()) {
            $this->messageManager->addErrorMessage(__('Quote not found'));
            return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                ->setPath('immutable_quote/customer/quotes');
        }

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('Quote Details'));

        return $resultPage;
    }

    /**
     * @return bool
     */
    public function canViewQuote(): bool
    {
        if (!$quoteId = $this->_request->getParam('quote_id')) {
            return false;
        }

        try {
            $quote = $this->quoteRepository->get((int)$quoteId);
            if ((int)$quote->getCustomerId() !== (int)$this->customerSession->getCustomerId()) {
                return false;
            }

            $this->registry->register('current_quote', $quote);
            return true;
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
