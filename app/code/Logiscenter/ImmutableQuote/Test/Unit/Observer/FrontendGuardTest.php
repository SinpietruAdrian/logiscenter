<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Observer;

use Logiscenter\ImmutableQuote\Observer\FrontendGuard;
use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Quote\Api\Data\CartInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\App\Action\Action;

class FrontendGuardTest extends TestCase
{
    private FrontendGuard $observer;
    private Validator|MockObject $validator;
    private ManagerInterface|MockObject $messageManager;
    private ActionFlag|MockObject $actionFlag;
    private RedirectInterface|MockObject $redirect;
    private SerializerInterface|MockObject $serializer;
    private EventManager|MockObject $eventManager;
    private RequestHttp|MockObject $request;
    private Http|MockObject $response;
    private Action|MockObject $controllerAction;
    private Observer|MockObject $eventObserver;
    private CartInterface|MockObject $mockQuote;
    private array $restrictedActions = ['checkout_index_index'];

    protected function setUp(): void
    {
        $this->validator = $this->createMock(Validator::class);
        $this->messageManager = $this->createMock(ManagerInterface::class);
        $this->actionFlag = $this->createMock(ActionFlag::class);
        $this->redirect = $this->createMock(RedirectInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->eventManager = $this->createMock(EventManager::class);

        $this->request = $this->createMock(RequestHttp::class);
        $this->response = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['representJson'])
            ->getMock();
        $this->controllerAction = $this->createMock(Action::class);
        $this->controllerAction->method('getResponse')->willReturn($this->response);

        $this->eventObserver = $this->getMockBuilder(Observer::class)
            ->addMethods(['getRequest', 'getControllerAction'])
            ->getMock();
        $this->eventObserver->method('getRequest')->willReturn($this->request);
        $this->eventObserver->method('getControllerAction')->willReturn($this->controllerAction);

        $this->mockQuote = $this->createMock(CartInterface::class);
        $this->validator->method('isLockedQuote')->willReturn(true);
        $this->validator->method('getLastValidatedQuote')->willReturn($this->mockQuote);

        $this->observer = new FrontendGuard(
            $this->validator,
            $this->messageManager,
            $this->actionFlag,
            $this->redirect,
            $this->serializer,
            $this->eventManager,
            $this->restrictedActions
        );
    }

    public function testAjaxRequestBlocked(): void
    {
        $this->request->method('getFullActionName')->willReturn('checkout_index_index');
        $this->request->method('isAjax')->willReturn(true);

        $this->eventManager->expects($this->once())->method('dispatch')
            ->with('immutable_quote_action_blocked', ['action' => 'checkout_index_index', 'quote' => $this->mockQuote]);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->actionFlag->expects($this->once())->method('set')
            ->with('', Action::FLAG_NO_DISPATCH, true);
        $this->serializer->expects($this->once())->method('serialize')
            ->with(['error' => true, 'message' => 'Action not allowed for immutable quotes'])
            ->willReturn('{"error":true,"message":"Action not allowed for immutable quotes"}');

        $this->response->expects($this->once())->method('representJson')
            ->with('{"error":true,"message":"Action not allowed for immutable quotes"}');

        $this->observer->execute($this->eventObserver);
    }

    public function testNormalRequestBlocked(): void
    {
        $this->request->method('getFullActionName')->willReturn('checkout_index_index');
        $this->request->method('isAjax')->willReturn(false);
        $this->response->method('representJson');

        $this->eventManager->expects($this->once())->method('dispatch')
            ->with('immutable_quote_action_blocked', ['action' => 'checkout_index_index', 'quote' => $this->mockQuote]);
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->actionFlag->expects($this->once())->method('set')
            ->with('', Action::FLAG_NO_DISPATCH, true);
        $this->redirect->expects($this->once())->method('redirect')
            ->with($this->response, '/');

        $this->observer->execute($this->eventObserver);
    }
}
