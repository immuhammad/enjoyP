<?php
/**
 * Webkul Affiliate Sales.
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
use Webkul\Affiliate\Model\ResourceModel\Sale\CollectionFactory;
use Webkul\Affiliate\Ui\Component\Listing\AffiliateStatus\Options as AffStatusOptions;

class Sales extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var AffStatusOptions
     */
    private $affStatusOptions;

    /**
     * @var CollectionFactory
     */
    public $salesOrders;

    /**
     * @param Context                $context
     * @param Session                $customerSession,
     * @param PriceCurrencyInterface $priceCurrency,
     * @param AffDataHelper          $affDataHelper,
     * @param CollectionFactory      $collectionFactory,
     * @param AffStatusOptions       $affStatusOptions,
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        AffDataHelper $affDataHelper,
        CollectionFactory $collectionFactory,
        AffStatusOptions $affStatusOptions,
        array $data = []
    ) {
    
        $this->collectionFactory = $collectionFactory;
        $this->priceCurrency = $priceCurrency;
        $this->affStatusOptions = $affStatusOptions;
        parent::__construct($context, $customerSession, $affDataHelper, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllSalesOrder()
    {
        if (!($customerId = $this->getCustomerSession()->getCustomerId())) {
            return false;
        }

        if (!$this->salesOrders) {
            $this->salesOrders = $this->collectionFactory->create()->addFieldToFilter('aff_customer_id', $customerId)
                                        ->setOrder('entity_id', 'AESC');
        }
        return $this->salesOrders;
    }
    
    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllSalesOrder()) {
            $pager = $this->getLayout()
            ->createBlock(\Magento\Theme\Block\Html\Pager::class, 'affi.sales.order.list.pager')
            ->setCollection($this->getAllSalesOrder());
            $this->setChild('pager', $pager);
            $this->getAllSalesOrder()->load();
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
     *
     * @param   $price
     * @return  string
     */
    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->format($price, true, 2, null, $this->getCurrentCurrencyCode());
    }

    /**
     * getAffStatusLabel
     *
     * @param   int $status
     * @return  string
     */
    public function getAffStatusLabel($status)
    {
        $options = $this->affStatusOptions->toOptionArray();
        foreach ($options as $option) {
            if ($option['value'] == $status) {
                return $option['label'];
            }
        }
    }
}
