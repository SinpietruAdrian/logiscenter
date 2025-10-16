<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Test\Unit\Plugin;

use Logiscenter\ImmutableQuote\Plugin\FilterShippingMethod;
use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use PHPUnit\Framework\TestCase;

class FilterShippingMethodTest extends TestCase
{
    private CartRepositoryInterface $quoteRepository;
    private Validator $validator;
    private ShippingMethodInterface $shippingMethodOne;
    private ShippingMethodInterface $shippingMethodTwo;
    private array $allMethods;
    private Quote $quote;
    private Address $address;
    private ShippingMethodManagementInterface $shippingManagement;

    protected function setUp(): void
    {
        $this->shippingMethodOne = $this->createMock(ShippingMethodInterface::class);
        $this->shippingMethodOne->method('getCarrierCode')->willReturn('flatrate');
        $this->shippingMethodOne->method('getMethodCode')->willReturn('flatrate');

        $this->shippingMethodTwo = $this->createMock(ShippingMethodInterface::class);
        $this->shippingMethodTwo->method('getCarrierCode')->willReturn('freeshipping');
        $this->shippingMethodTwo->method('getMethodCode')->willReturn('freeshipping');

        $this->allMethods = [$this->shippingMethodOne, $this->shippingMethodTwo];

        $this->address = $this->createMock(Address::class);

        $this->quote = $this->createMock(Quote::class);
        $this->quote->method('getShippingAddress')->willReturn($this->address);

        $this->quoteRepository = $this->createMock(CartRepositoryInterface::class);
        $this->validator = $this->createMock(Validator::class);

        $this->shippingManagement = $this->createMock(ShippingMethodManagementInterface::class);
    }

    public function testImmutableQuoteFiltersShippingMethods(): void
    {
        $cartId = 123;
        $quoteShippingMethod = 'flatrate_flatrate';

        $this->address->method('getShippingMethod')->willReturn($quoteShippingMethod);
        $this->quoteRepository->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->validator->method('isImmutableQuote')->willReturnCallback(fn($q) => $q === $this->quote);

        $plugin = new FilterShippingMethod($this->quoteRepository, $this->validator);

        $filtered = $plugin->afterEstimateByAddressId(
            $this->shippingManagement,
            $this->allMethods,
            $cartId
        );

        $this->assertCount(1, $filtered);
        $this->assertSame($this->shippingMethodOne, $filtered[0]);
    }

    public function testNonImmutableQuoteReturnsAllMethods(): void
    {
        $cartId = 123;

        $this->quote->method('getShippingAddress')->willReturn(null);
        $this->quoteRepository->method('getActive')->with($cartId)->willReturn($this->quote);
        $this->validator->method('isImmutableQuote')->willReturn(false);

        $plugin = new FilterShippingMethod($this->quoteRepository, $this->validator);

        $filtered = $plugin->afterEstimateByAddressId(
            $this->shippingManagement,
            $this->allMethods,
            $cartId
        );

        $this->assertCount(2, $filtered);
        $this->assertSame($this->shippingMethodOne, $filtered[0]);
        $this->assertSame($this->shippingMethodTwo, $filtered[1]);
    }
}
