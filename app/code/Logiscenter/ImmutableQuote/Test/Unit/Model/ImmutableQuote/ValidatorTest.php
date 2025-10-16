<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Model\ImmutableQuote;

use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface;
use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use PHPUnit\Framework\TestCase;

class ValidatorTest extends TestCase
{
    private Session $checkoutSession;
    private CartRepositoryInterface $cartRepository;
    private Validator $validator;

    private CartInterface $quote;
    private CartExtensionInterface $extensionAttributes;
    private QuoteMetadataInterface $metadata;

    protected function setUp(): void
    {
        $this->checkoutSession = $this->createMock(Session::class);
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);

        $this->validator = new Validator(
            $this->checkoutSession,
            $this->cartRepository
        );

        $this->quote = $this->createMock(CartInterface::class);
        $this->extensionAttributes = $this->createMock(CartExtensionInterface::class);
        $this->metadata = $this->createMock(QuoteMetadataInterface::class);
    }

    public function testIsImmutableQuoteReturnsFalseWhenNoMetadata(): void
    {
        $this->extensionAttributes->method('getMetadata')->willReturn(null);
        $this->quote->method('getExtensionAttributes')->willReturn($this->extensionAttributes);

        $this->assertFalse($this->validator->isImmutableQuote($this->quote));
        $this->assertSame($this->quote, $this->validator->getLastValidatedQuote());
    }

    public function testIsLockedQuoteReturnsTrue(): void
    {
        $quoteId = 123;

        $this->metadata->method('getIsImmutable')->willReturn(true);
        $this->extensionAttributes->method('getMetadata')->willReturn($this->metadata);
        $this->quote->method('getExtensionAttributes')->willReturn($this->extensionAttributes);

        $this->cartRepository->method('get')->with($quoteId)->willReturn($this->quote);

        $this->assertTrue($this->validator->isLockedQuote($quoteId));
        $this->assertSame($this->quote, $this->validator->getLastValidatedQuote());
    }

    public function testIsLockedQuoteReturnsFalseForNoSuchEntity(): void
    {
        $quoteId = 999;
        $this->cartRepository->method('get')->with($quoteId)
            ->willThrowException(new NoSuchEntityException(__('Quote not found')));

        $this->assertFalse($this->validator->isLockedQuote($quoteId));
        $this->assertNull($this->validator->getLastValidatedQuote());
    }

    public function testIsLockedQuoteUsesCheckoutSessionQuoteId(): void
    {
        $quoteId = 555;

        $this->metadata->method('getIsImmutable')->willReturn(true);
        $this->extensionAttributes->method('getMetadata')->willReturn($this->metadata);
        $this->quote->method('getExtensionAttributes')->willReturn($this->extensionAttributes);

        $this->checkoutSession->method('getQuoteId')->willReturn($quoteId);
        $this->cartRepository->method('get')->with($quoteId)->willReturn($this->quote);

        $this->assertTrue($this->validator->isLockedQuote());
        $this->assertSame($this->quote, $this->validator->getLastValidatedQuote());
    }
}
