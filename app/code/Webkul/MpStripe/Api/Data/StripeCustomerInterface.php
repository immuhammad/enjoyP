<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpStripe
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */


namespace Webkul\MpStripe\Api\Data;

/**
 * StripeCustomer Data Interface
 */
interface StripeCustomerInterface
{

    public const ENTITY_ID = 'entity_id';

    public const WEBSITE_ID = 'website_id';

    public const STRORE_ID = 'strore_id';

    public const IS_ACTIVE = 'is_active';

    public const SELLER_ID = 'seller_id';

    public const INTEGRATION_TYPE = 'integration_type';

    public const EMAIL = 'email';

    public const ACCESS_TOKEN = 'access_token';

    public const REFRESH_TOKEN = 'refresh_token';

    public const STRIPE_KEY = 'stripe_key';

    public const STRIPE_USER_ID = 'stripe_user_id';

    public const STRIPE_PERSON_ID = 'stripe_person_id';

    public const ISVERIFIED = 'isverified';

    public const USER_TYPE = 'user_type';

    public const PAYMENT_ENVIRONMENT = 'payment_environment';

    public const CREATED_AT = 'created_at';

    public const UPDATED_AT = 'updated_at';

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setEntityId($entityId);
    /**
     * Get EntityId
     *
     * @return int
     */
    public function getEntityId();
    /**
     * Set WebsiteId
     *
     * @param int $websiteId
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setWebsiteId($websiteId);
    /**
     * Get WebsiteId
     *
     * @return int
     */
    public function getWebsiteId();
    /**
     * Set StroreId
     *
     * @param int $stroreId
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setStroreId($stroreId);
    /**
     * Get StroreId
     *
     * @return int
     */
    public function getStroreId();
    /**
     * Set IsActive
     *
     * @param int $isActive
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setIsActive($isActive);
    /**
     * Get IsActive
     *
     * @return int
     */
    public function getIsActive();
    /**
     * Set SellerId
     *
     * @param int $sellerId
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setSellerId($sellerId);
    /**
     * Get SellerId
     *
     * @return int
     */
    public function getSellerId();
    /**
     * Set IntegrationType
     *
     * @param int $integrationType
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setIntegrationType($integrationType);
    /**
     * Get IntegrationType
     *
     * @return int
     */
    public function getIntegrationType();
    /**
     * Set Email
     *
     * @param string $email
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setEmail($email);
    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail();
    /**
     * Set AccessToken
     *
     * @param string $accessToken
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setAccessToken($accessToken);
    /**
     * Get AccessToken
     *
     * @return string
     */
    public function getAccessToken();
    /**
     * Set RefreshToken
     *
     * @param string $refreshToken
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setRefreshToken($refreshToken);
    /**
     * Get RefreshToken
     *
     * @return string
     */
    public function getRefreshToken();
    /**
     * Set StripeKey
     *
     * @param string $stripeKey
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setStripeKey($stripeKey);
    /**
     * Get StripeKey
     *
     * @return string
     */
    public function getStripeKey();
    /**
     * Set StripeUserId
     *
     * @param string $stripeUserId
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setStripeUserId($stripeUserId);
    /**
     * Get StripeUserId
     *
     * @return string
     */
    public function getStripeUserId();
    /**
     * Set StripePersonId
     *
     * @param string $stripePersonId
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setStripePersonId($stripePersonId);
    /**
     * Get StripePersonId
     *
     * @return string
     */
    public function getStripePersonId();
    /**
     * Set Isverified
     *
     * @param string $isverified
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setIsverified($isverified);
    /**
     * Get Isverified
     *
     * @return string
     */
    public function getIsverified();
    /**
     * Set UserType
     *
     * @param string $userType
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setUserType($userType);
    /**
     * Get UserType
     *
     * @return string
     */
    public function getUserType();
    /**
     * Set PaymentEnvironment
     *
     * @param string $paymentEnvironment
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setPaymentEnvironment($paymentEnvironment);
    /**
     * Get PaymentEnvironment
     *
     * @return string
     */
    public function getPaymentEnvironment();
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setCreatedAt($createdAt);
    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt();
    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return Webkul\MpStripe\Api\Data\StripeCustomerInterface
     */
    public function setUpdatedAt($updatedAt);
    /**
     * Get UpdatedAt
     *
     * @return string
     */
    public function getUpdatedAt();
}
