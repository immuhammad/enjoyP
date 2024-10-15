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


namespace Webkul\MpStripe\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * Stripe Seller Data Class
 */
class StripeSeller extends AbstractModel implements IdentityInterface, \Webkul\MpStripe\Api\Data\StripeSellerInterface
{

    public const NOROUTE_ENTITY_ID = 'no-route';

    public const CACHE_TAG = 'webkul_mpstripe_stripeseller';

    /**
     * @var string $_cacheTag
     */
    protected $_cacheTag = 'webkul_mpstripe_stripeseller';

    /**
     * @var string $_eventPrefix
     */
    protected $_eventPrefix = 'webkul_mpstripe_stripeseller';

    /**
     * Set resource model
     */
    public function _construct()
    {
        $this->_init(\Webkul\MpStripe\Model\ResourceModel\StripeSeller::class);
    }

    /**
     * Load No-Route Indexer.
     *
     * @return $this
     */
    public function noRouteReasons()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return []
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get EntityId
     *
     * @return int
     */
    public function getEntityId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set WebsiteId
     *
     * @param int $websiteId
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setWebsiteId($websiteId)
    {
        return $this->setData(self::WEBSITE_ID, $websiteId);
    }

    /**
     * Get WebsiteId
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return parent::getData(self::WEBSITE_ID);
    }

    /**
     * Set StroreId
     *
     * @param int $stroreId
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setStroreId($stroreId)
    {
        return $this->setData(self::STRORE_ID, $stroreId);
    }

    /**
     * Get StroreId
     *
     * @return int
     */
    public function getStroreId()
    {
        return parent::getData(self::STRORE_ID);
    }

    /**
     * Set IsActive
     *
     * @param int $isActive
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setIsActive($isActive)
    {
        return $this->setData(self::IS_ACTIVE, $isActive);
    }

    /**
     * Get IsActive
     *
     * @return int
     */
    public function getIsActive()
    {
        return parent::getData(self::IS_ACTIVE);
    }

    /**
     * Set SellerId
     *
     * @param int $sellerId
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get SellerId
     *
     * @return int
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }

    /**
     * Set IntegrationType
     *
     * @param int $integrationType
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setIntegrationType($integrationType)
    {
        return $this->setData(self::INTEGRATION_TYPE, $integrationType);
    }

    /**
     * Get IntegrationType
     *
     * @return int
     */
    public function getIntegrationType()
    {
        return parent::getData(self::INTEGRATION_TYPE);
    }

    /**
     * Set Email
     *
     * @param string $email
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setEmail($email)
    {
        return $this->setData(self::EMAIL, $email);
    }

    /**
     * Get Email
     *
     * @return string
     */
    public function getEmail()
    {
        return parent::getData(self::EMAIL);
    }

    /**
     * Set AccessToken
     *
     * @param string $accessToken
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setAccessToken($accessToken)
    {
        return $this->setData(self::ACCESS_TOKEN, $accessToken);
    }

    /**
     * Get AccessToken
     *
     * @return string
     */
    public function getAccessToken()
    {
        return parent::getData(self::ACCESS_TOKEN);
    }

    /**
     * Set RefreshToken
     *
     * @param string $refreshToken
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setRefreshToken($refreshToken)
    {
        return $this->setData(self::REFRESH_TOKEN, $refreshToken);
    }

    /**
     * Get RefreshToken
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return parent::getData(self::REFRESH_TOKEN);
    }

    /**
     * Set StripeKey
     *
     * @param string $stripeKey
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setStripeKey($stripeKey)
    {
        return $this->setData(self::STRIPE_KEY, $stripeKey);
    }

    /**
     * Get StripeKey
     *
     * @return string
     */
    public function getStripeKey()
    {
        return parent::getData(self::STRIPE_KEY);
    }

    /**
     * Set StripeUserId
     *
     * @param string $stripeUserId
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setStripeUserId($stripeUserId)
    {
        return $this->setData(self::STRIPE_USER_ID, $stripeUserId);
    }

    /**
     * Get StripeUserId
     *
     * @return string
     */
    public function getStripeUserId()
    {
        return parent::getData(self::STRIPE_USER_ID);
    }

    /**
     * Set StripePersonId
     *
     * @param string $stripePersonId
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setStripePersonId($stripePersonId)
    {
        return $this->setData(self::STRIPE_PERSON_ID, $stripePersonId);
    }

    /**
     * Get StripePersonId
     *
     * @return string
     */
    public function getStripePersonId()
    {
        return parent::getData(self::STRIPE_PERSON_ID);
    }

    /**
     * Set Isverified
     *
     * @param string $isverified
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setIsverified($isverified)
    {
        return $this->setData(self::ISVERIFIED, $isverified);
    }

    /**
     * Get Isverified
     *
     * @return string
     */
    public function getIsverified()
    {
        return parent::getData(self::ISVERIFIED);
    }

    /**
     * Set UserType
     *
     * @param string $userType
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setUserType($userType)
    {
        return $this->setData(self::USER_TYPE, $userType);
    }

    /**
     * Get UserType
     *
     * @return string
     */
    public function getUserType()
    {
        return parent::getData(self::USER_TYPE);
    }

    /**
     * Set PaymentEnvironment
     *
     * @param string $paymentEnvironment
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setPaymentEnvironment($paymentEnvironment)
    {
        return $this->setData(self::PAYMENT_ENVIRONMENT, $paymentEnvironment);
    }

    /**
     * Get PaymentEnvironment
     *
     * @return string
     */
    public function getPaymentEnvironment()
    {
        return parent::getData(self::PAYMENT_ENVIRONMENT);
    }
    
    /**
     * Set CreatedAt
     *
     * @param string $createdAt
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * Get CreatedAt
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return parent::getData(self::CREATED_AT);
    }

    /**
     * Set UpdatedAt
     *
     * @param string $updatedAt
     * @return Webkul\MpStripe\Model\StripeSellerInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Get UpdatedAt
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return parent::getData(self::UPDATED_AT);
    }
}
