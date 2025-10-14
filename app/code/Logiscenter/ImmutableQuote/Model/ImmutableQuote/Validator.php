<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ImmutableQuote;

use Magento\Quote\Api\Data\CartInterface;

class Validator
{
    /**
     * @param CartInterface $quote
     * @return bool
     */
    public function isImmutableQuote(CartInterface $quote): bool
    {
        return (bool)$quote->getExtensionAttributes()->getMetadata()->getIsImmutable();
    }
}
