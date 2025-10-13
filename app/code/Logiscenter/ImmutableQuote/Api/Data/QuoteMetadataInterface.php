<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface QuoteMetadataInterface extends ExtensibleDataInterface
{
    const KEY_METADATA_ID = 'metadata_id';
    const KEY_QUOTE_ID = 'quote_id';
    const KEY_IS_IMMUTABLE = 'is_immutable';
    const KEY_TITLE = 'title';


    /**
     * Get Metadata ID
     *
     * @return int|null
     */
    public function getMetadataId(): ?int;

    /**
     * Set Metadata ID
     *
     * @param int $metadataId
     * @return self
     */
    public function setMetadataId(int $metadataId): self;

    /**
     * Get Quote ID
     *
     * @return int|null
     */
    public function getQuoteId(): ?int;

    /**
     * Set Quote ID
     *
     * @param int $quoteId
     * @return self
     */
    public function setQuoteId(int $quoteId): self;

    /**
     * Get Immutable Flag
     *
     * @return bool
     */
    public function getIsImmutable(): bool;

    /**
     * Set Immutable Flag
     *
     * @param bool $isImmutable
     * @return self
     */
    public function setIsImmutable(bool $isImmutable): self;

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Set Title
     *
     * @param string $title
     * @return self
     */
    public function setTitle(string $title): self;

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Logiscenter\ImmutableQuote\Api\Data\QuoteMetadataExtensionInterface $extensionAttributes
    );
}
