<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Stripe\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\Stripe\Api\Data\StripeOrderInterface;

class StripeOrder extends AbstractModel implements IdentityInterface, StripeOrderInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Order History Communication cache tag.
     */
    public const CACHE_TAG = 'wk_stripe_order';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_stripe_order';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_stripe_order';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Stripe\Model\ResourceModel\StripeOrder::class
        );
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSaleslist();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Saleslist.
     *
     * @return \Webkul\Stripe\Model\StripeOrder
     */
    public function noRouteSaleslist()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\Stripe\Model\StripeOrder
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\Stripe\Model\StripeOrderInterface
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
     * Set QuoteId
     *
     * @param int $quoteId
     * @return Webkul\Stripe\Model\StripeOrderInterface
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
    }

    /**
     * Get QuoteId
     *
     * @return int
     */
    public function getQuoteId()
    {
        return parent::getData(self::QUOTE_ID);
    }

    /**
     * Set Status
     *
     * @param string $status
     * @return Webkul\Stripe\Model\StripeOrderInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * Get Status
     *
     * @return string
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set PaymentIntent
     *
     * @param string $paymentIntent
     * @return Webkul\Stripe\Model\StripeOrderInterface
     */
    public function setPaymentIntent($paymentIntent)
    {
        return $this->setData(self::PAYMENT_INTENT, $paymentIntent);
    }

    /**
     * Get PaymentIntent
     *
     * @return string
     */
    public function getPaymentIntent()
    {
        return parent::getData(self::PAYMENT_INTENT);
    }
}
