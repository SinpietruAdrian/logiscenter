<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Observer;

use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Serialize\SerializerInterface;

class FrontendGuard implements ObserverInterface
{
    public function __construct(
        private readonly Validator $validator,
        private readonly ManagerInterface $messageManager,
        private readonly ActionFlag $actionFlag,
        private readonly RedirectInterface $redirect,
        private readonly SerializerInterface $serializer,
        private readonly array $restrictedActionsList = []
    )
    {
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer): void
    {
        if (!$this->restrictedActionsList) {
            return;
        }

        $request = $observer->getRequest();
        if (in_array($request->getFullActionName(), $this->restrictedActionsList)) {
            if ($this->validator->isLockedQuote()) {
                $message = __('Action not allowed for immutable quotes');
                $response = $observer->getControllerAction()->getResponse();

                $this->messageManager->addErrorMessage($message);
                $this->actionFlag->set('', Action::FLAG_NO_DISPATCH, true);
                if ($request->isAjax()) {
                    $response->representJson($this->serializer->serialize([
                        'error' => true,
                        'message' => (string)$message
                    ]));
                } else {
                    $this->redirect->redirect($response, '/');
                }
            }
        }
    }
}
