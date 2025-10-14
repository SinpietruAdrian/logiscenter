<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;
use Magento\Quote\Api\Data\ShippingMethodInterface;

class FilterShippingMethod
{
    public function __construct(
        private readonly CartRepositoryInterface $quoteRepository,
        private readonly Validator $validator
    )
    {

    }

    /**
     * @param ShippingMethodManagementInterface $subject
     * @param array $result
     * @param $cartId
     * @throws NoSuchEntityException
     * @return ShippingMethodInterface[]
     */
    public function afterEstimateByAddressId(
        ShippingMethodManagementInterface $subject,
        array $result,
        $cartId
    ): array
    {
        $quote = $this->quoteRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        if ($this->validator->isImmutableQuote($quote) && $shippingAddress && $shippingAddress->getShippingMethod() ) {
            /** @var ShippingMethodInterface $shippingMethod */
            foreach ($result as $shippingMethod) {
                $formattedShippingMethod = $shippingMethod->getCarrierCode() . '_' . $shippingMethod->getMethodCode();
                if ($formattedShippingMethod == $shippingAddress->getShippingMethod()) {
                    return [$shippingMethod];
                }
            }
        }

        return $result;
    }
}
