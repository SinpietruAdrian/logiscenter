<?php
declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Model;

use Magento\Framework\Model\AbstractModel;
use Logiscenter\ImmutableQuote\Api\Data\ActionLogInterface;

class ActionLog extends AbstractModel implements ActionLogInterface
{
    /**
     * @inheritDoc
     */
    protected function _construct(): void
    {
        $this->_init(ResourceModel\ActionLog::class);
    }

    /**
     * @inheritDoc
     */
    public function getAction(): string
    {
        return (string)$this->getData(self::ACTION);
    }

    /**
     * @inheritDoc
     */
    public function setAction(string $action): self
    {
        return $this->setData(self::ACTION, $action);
    }

    /**
     * @inheritDoc
     */
    public function getIp(): string
    {
        return (string)$this->getData(self::IP);
    }

    /**
     * @inheritDoc
     */
    public function setIp(string $ip): self
    {
        return $this->setData(self::IP, $ip);
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalInformation(): ?string
    {
        return $this->getData(self::ADDITIONAL_INFORMATION);
    }

    /**
     * @inheritDoc
     */
    public function setAdditionalInformation(?string $additionalInformation): self
    {
        return $this->setData(self::ADDITIONAL_INFORMATION, $additionalInformation);
    }

    /**
     * @inheritDoc
     */
    public function getCustomerId(): ?int
    {
        return $this->getData(self::CUSTOMER_ID) !== null
            ? (int)$this->getData(self::CUSTOMER_ID)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId(?int $customerId): self
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @inheritDoc
     */
    public function getAdminUserId(): ?int
    {
        return $this->getData(self::ADMIN_USER_ID) !== null
            ? (int)$this->getData(self::ADMIN_USER_ID)
            : null;
    }

    /**
     * @inheritDoc
     */
    public function setAdminUserId(?int $adminUserId): self
    {
        return $this->setData(self::ADMIN_USER_ID, $adminUserId);
    }

    /**
     * @inheritDoc
     */
    public function getUserType(): string
    {
        return (string)$this->getData(self::USER_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setUserType(string $userType): self
    {
        return $this->setData(self::USER_TYPE, $userType);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(string $createdAt): self
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
