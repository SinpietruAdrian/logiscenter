<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Observer;

use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;

class FrontendGuard implements ObserverInterface
{
    public function __construct(
        private readonly Session $checkoutSession,
        private readonly CartRepositoryInterface $cartRepository,
        private readonly Validator $validator,
        private readonly ManagerInterface $messageManager,
        private readonly ActionFlag $actionFlag,
        private readonly RedirectInterface $redirect
    ) {

    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        $request = $observer->getRequest();

        $immutablePaths = [
            'checkout_cart_add',
            'checkout_cart_delete',
            'checkout_cart_updateItemQty',
            'checkout_cart_updatePost'
        ];

        if (in_array($request->getFullActionName(), $immutablePaths)) {
            if ($quoteId = $this->checkoutSession->getQuoteId()) {
                $quote = $this->cartRepository->get((int)$quoteId);
                if ($this->validator->isImmutableQuote($quote)) {
                    $controller = $observer->getControllerAction();

                    $this->messageManager->addErrorMessage(__('Action not allowed for immutable quotes'));
                    $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                    $this->redirect->redirect($controller->getResponse(), '/');
                }
            }
        }
    }
}
