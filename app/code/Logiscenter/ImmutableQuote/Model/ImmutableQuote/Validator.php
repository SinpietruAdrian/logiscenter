<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model\ImmutableQuote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Checkout\Model\Session;

class Validator
{
    public function __construct(
        private readonly Session $checkoutSession,
        private readonly CartRepositoryInterface $cartRepository
    )
    {

    }
    /**
     * @param CartInterface $quote
     * @return bool
     */
    public function isImmutableQuote(CartInterface $quote): bool
    {
        if (!$metadata = $quote->getExtensionAttributes()->getMetadata()) {
            return false;
        }

        return (bool)$metadata->getIsImmutable();
    }

    /**
     * @param int|null $quoteId
     * @return bool
     */
    public function isLockedQuote(?int $quoteId = null): bool
    {
        $quoteId = $quoteId ?: $this->checkoutSession->getQuoteId();
        try {
            return $this->isImmutableQuote($this->cartRepository->get($quoteId));
        } catch (NoSuchEntityException $e) {
            return false;
        }
    }
}
