<?php
/**
 * Webkul Affiliate Payments.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Webkul\Affiliate\Model\ResourceModel\Payment\CollectionFactory;

class Payments extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * @var Webkul\Affiliate\Model\ResourceModel\Payment\CollectionFactory
     */
    public $payments;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Webkul\Affiliate\Model\ResourceModel\Payment\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context           $context
     * @param Session           $customerSession,
     * @param AffDataHelper     $affDataHelper,
     * @param CollectionFactory $collectionFactory,
     * @param array             $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        AffDataHelper $affDataHelper,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
    
        $this->collectionFactory = $collectionFactory;
        $this->priceCurrency =  $priceCurrency;
        parent::__construct($context, $customerSession, $affDataHelper, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllPayment()
    {
        if (!($customerId = $this->getCustomerSession()->getCustomerId())) {
            return false;
        }

        if (!$this->payments) {
            $this->payments = $this->collectionFactory->create()->addFieldToFilter('aff_customer_id', $customerId)
                                        ->setOrder('entity_id', 'AESC');
        }

        return $this->payments;
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllPayment()) {
            $pager = $this->getLayout()
                    ->createBlock(\Magento\Theme\Block\Html\Pager::class, 'affiliate.payment.list.pager')
                    ->setCollection($this->getAllPayment());
            $this->setChild('pager', $pager);
            $this->getAllPayment()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * getDateTimeAsLocale
     * @param string $data in base Time zone
     * @return string date in current Time zone
     */
    public function getDateTimeAsLocale($data)
    {
        if ($data) {
            return $this->_localeDate->date($data)->format('g:ia \o\\n l jS F Y');
        }
        return $data;
    }

    /**
     * Get formatted by price and currency
     * @param   $price
     * @return  string
     */
    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->format($price, true, 2, null, $this->getCurrentCurrencyCode());
    }
}
