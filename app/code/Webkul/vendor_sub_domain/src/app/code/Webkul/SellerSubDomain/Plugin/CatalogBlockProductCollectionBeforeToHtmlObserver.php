<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubDomain\Plugin;

use \Magento\Framework\App\Helper\Context;

class CatalogBlockProductCollectionBeforeToHtmlObserver
{
    /**
     *
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * Review model
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;

    /**
     * @param Context                                     $context
     * @param \Webkul\SellerSubDomain\Helper\Data         $data
     */
    public function __construct(
        Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Webkul\SellerSubDomain\Helper\Data $data
    ) {
        $this->_helper = $data;
        $this->_reviewFactory = $reviewFactory;
    }

    /**
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundExecute(
        \Magento\Review\Observer\CatalogBlockProductCollectionBeforeToHtmlObserver $subject,
        callable $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        $productCollection = $observer->getEvent()->getCollection();
        $seller = $this->_helper->getProfileDetail();
        if ($seller) {
            $allowedProList = $this->_helper->getProductIds();
            $productCollection->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
        }
        if ($productCollection instanceof \Magento\Framework\Data\Collection) {
            $productCollection->load();
            $this->_reviewFactory->create()->appendSummary($productCollection);
        }

        return $this;
    }
}
