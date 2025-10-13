<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Plugin;

use Logiscenter\ImmutableQuote\Api\QuoteMetadataRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartExtensionInterface;
use Magento\Quote\Api\Data\CartExtensionInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataExtensionInterfaceFactory;

class SetQuoteMetadata
{
    public function __construct(
        private readonly CartExtensionInterfaceFactory $cartExtensionFactory,
        private readonly QuoteMetadataRepositoryInterface $quoteMetadataRepository,
        private readonly QuoteMetadataExtensionInterfaceFactory $quoteMetadataExtensionFactory
    )
    {

    }

    public function afterGetExtensionAttributes(
        CartInterface $subject,
        ?CartExtensionInterface $cartExtension = null
    ): CartExtensionInterface
    {
        if ($cartExtension === null) {
            $cartExtension = $this->cartExtensionFactory->create();
        }

        $quoteMetadata = $cartExtension->getMetadata();
        if (!$quoteMetadata && $subject->getId()) {
            try {
                $metadata = $this->quoteMetadataRepository->getByQuoteId((int)$subject->getId());
                $metadataExtension = $this->quoteMetadataExtensionFactory->create();

                foreach ($metadata->getData() as $code => $value) {
                    $method = 'set' . str_replace('_', '', ucwords($code, '_'));
                    if (method_exists($metadataExtension, $method)) {
                        $metadataExtension->$method($value);
                    }
                }

                $metadata->setExtensionAttributes($metadataExtension);
                $cartExtension->setMetadata($metadata);

                $subject->setExtensionAttributes($cartExtension);
            } catch (NoSuchEntityException $e) {
                return $cartExtension;
            }
        }

        return $cartExtension;
    }
}
