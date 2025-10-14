<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Logiscenter\ImmutableQuote\Model\ImmutableQuote\Validator;
use Magento\Checkout\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class UpdateCheckoutConfig
{
    public function __construct(
        private readonly Validator $validator,
        private readonly Session $checkoutSession
    )
    {
    }

    /**
     * @param DefaultConfigProvider $subject
     * @param array $result
     * @throws NoSuchEntityException|LocalizedException
     * @return array
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result): array
    {
        $quote = $this->checkoutSession->getQuote();
        if ($this->validator->isImmutableQuote($quote)  && isset($result['customerData']['addresses'])) {
            $allowedAddress = [];
            foreach ($result['customerData']['addresses'] as $addressData) {
                $quoteShippingAddressId = (int)$quote->getShippingAddress()->getCustomerAddressId();
                $quoteBillingAddressId = (int)$quote->getBillingAddress()->getCustomerAddressId();
                $addressId = (int)$addressData['id'];

                if ($addressId == $quoteShippingAddressId || $addressId == $quoteBillingAddressId) {
                    $allowedAddress[] = $addressData;
                }
            }

            if ($allowedAddress) {
                $result['customerData']['addresses'] = $allowedAddress;
            }
        }

        // This is needed because there is a bug in Magento that doesn't convert extension attributes that are objects
        // to array
        $quoteMetadata = $quote->getExtensionAttributes()->getMetadata();
        if ($quoteMetadata && $quoteMetadata->getIsImmutable()) {
            $result['quoteData']['is_immutable'] = true;
        }

        return $result;
    }
}
