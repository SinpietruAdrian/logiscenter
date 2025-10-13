<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\AbstractExtensibleModel;

class QuoteMetadata extends AbstractExtensibleModel implements QuoteMetadataInterface
{
    /**
     * @inheritDoc
     */
    public function _construct(): void
    {
        $this->_init(ResourceModel\QuoteMetadata::class);
    }

    /**
     * @inheritdoc
     */
    public function getMetadataId(): ?int
    {
        $id = $this->getData(self::KEY_METADATA_ID);
        return $id ? (int) $id : null;
    }

    /**
     * @inheritdoc
     */
    public function setMetadataId(int $metadataId): self
    {
        return $this->setData(self::KEY_METADATA_ID, $metadataId);
    }

    /**
     * @inheritdoc
     */
    public function getIsImmutable(): bool
    {
        return (bool) $this->getData(self::KEY_IS_IMMUTABLE);
    }

    /**
     * @inheritdoc
     */
    public function setIsImmutable(bool $isImmutable): self
    {
        return $this->setData(self::KEY_IS_IMMUTABLE, $isImmutable);
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::KEY_TITLE);
    }

    /**
     * @inheritdoc
     */
    public function setTitle(string $title): self
    {
        return $this->setData(self::KEY_TITLE, $title);
    }

    /**
     * @inheritDoc
     */
    public function getQuoteId(): ?int
    {
        $id = $this->getData(self::KEY_QUOTE_ID);
        return $id ? (int) $id : null;
    }

    /**
     * @inheritDoc
     */
    public function setQuoteId(int $quoteId): QuoteMetadataInterface
    {
        return $this->setData(self::KEY_QUOTE_ID, $quoteId);
    }

    /**
     * @inheritDoc
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * @inheritDoc
     */
    public function setExtensionAttributes(
        \Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
}
