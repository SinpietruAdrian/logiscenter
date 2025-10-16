<?php

declare(strict_types=1);

namespace Logiscenter\ImmutableQuote\Api\Data;

interface ActionLogInterface
{
    public const ID = 'id';
    public const ACTION = 'action';
    public const IP = 'ip';
    public const ADDITIONAL_INFORMATION = 'additional_information';
    public const CUSTOMER_ID = 'customer_id';
    public const ADMIN_USER_ID = 'admin_user_id';
    public const USER_TYPE = 'user_type';
    public const CREATED_AT = 'created_at';

    /**
     * Get action
     *
     * @return string
     */
    public function getAction(): string;

    /**
     * Set action
     *
     * @param string $action
     * @return $this
     */
    public function setAction(string $action): self;

    /**
     * Get IP
     *
     * @return string
     */
    public function getIp(): string;

    /**
     * Set IP
     *
     * @param string $ip
     * @return $this
     */
    public function setIp(string $ip): self;

    /**
     * Get additional information
     *
     * @return string|null
     */
    public function getAdditionalInformation(): ?string;

    /**
     * Set additional information
     *
     * @param string|null $additionalInformation
     * @return $this
     */
    public function setAdditionalInformation(?string $additionalInformation): self;

    /**
     * Get customer id
     *
     * @return int|null
     */
    public function getCustomerId(): ?int;

    /**
     * Set customer id
     *
     * @param int|null $customerId
     * @return $this
     */
    public function setCustomerId(?int $customerId): self;

    /**
     * Get admin user id
     *
     * @return int|null
     */
    public function getAdminUserId(): ?int;

    /**
     * Set admin user id
     *
     * @param int|null $adminUserId
     * @return $this
     */
    public function setAdminUserId(?int $adminUserId): self;

    /**
     * Get user type
     *
     * @return string
     */
    public function getUserType(): string;

    /**
     * Set user type
     *
     * @param string $userType
     * @return $this
     */
    public function setUserType(string $userType): self;

    /**
     * Get created at (MySQL timestamp)
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt(string $createdAt): self;
}
