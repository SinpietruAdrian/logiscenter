<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\ViewModel;

use Magento\Framework\Convert\ConvertArray;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Framework\Registry;
use Magento\Customer\Model\Address\Config;

class QuoteDetails implements ArgumentInterface
{
    public function __construct(
        private readonly Registry $registry,
        private readonly Config $addressConfig
    )
    {
    }

    /**
     * @param AddressInterface $address
     * @return mixed
     */
    public function renderAddress(AddressInterface $address): string
    {
        //If address has no data the renderer will return a chunk of template text
        if (!$address->getFirstname()) {
            return '';
        }

        $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
        $addressData = ConvertArray::toFlatArray($address->getData());

        return $renderer->renderArray($addressData);
    }

    /**
     * @return CartInterface
     * @throws LocalizedException
     */
    public function getQuote(): CartInterface
    {
        if (!$quote = $this->registry->registry('current_quote')) {
            throw new LocalizedException(__('Quote is not set'));
        }

        return $quote;
    }
}
