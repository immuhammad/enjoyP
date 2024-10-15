<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Block\Seller;

use Webkul\Marketplace\Helper\Data as HelperData;

class ServiceData extends \Magento\Framework\View\Element\Template
{
    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param HelperData $helper
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $serviceCollectionFactory
     * @param \Webkul\MpServiceFee\Model\Source\Config\Enabledisable $options
     * @param \Webkul\MpServiceFee\Model\Source\Config\Options $serviceType
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        HelperData $helper,
        \Webkul\MpServiceFee\Model\AttributesListFactory $serviceCollectionFactory,
        \Webkul\MpServiceFee\Model\Source\Config\Enabledisable $options,
        \Webkul\MpServiceFee\Model\Source\Config\Options $serviceType
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->options = $options;
        $this->serviceType = $serviceType;
    }
    /**
     * Get seller id
     *
     * @return sellerId
     */
    public function getSellerId()
    {
        return $this->helper->getCustomerId();
    }
    /**
     * Get service fee data
     *
     * @param int $id
     * @return serviceFeeArray
     */
    public function getServiceFeeData($id)
    {
        return $this->serviceCollectionFactory->create()->load($id)->getData();
    }
    /**
     * Get status options
     *
     * @return optionArray
     */
    public function getStatusOptionArray()
    {
        return $this->options->getAllOptions();
    }
    /**
     * Get service type
     *
     * @return serviceTypeArray
     */
    public function getServiceType()
    {
        return $this->serviceType->getAllOptions();
    }
}
