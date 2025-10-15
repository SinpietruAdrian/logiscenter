<?php

namespace Logiscenter\ImmutableQuote\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;

class Quotes extends Action implements HttpGetActionInterface
{
    public function __construct(
        private readonly Session $customerSession,
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

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->set(__('My Quotes'));

        return $resultPage;
    }
}
