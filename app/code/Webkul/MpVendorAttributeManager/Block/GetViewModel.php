<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Block;

/**
 * Xtremo GetViewModel Block
 */
class GetViewModel extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpVendorAttributeManager\ViewModel\HelperViewModel
     */
    protected $helperViewModel;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Xtremo\ViewModel\HelperViewModel $helperViewModel
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpVendorAttributeManager\ViewModel\HelperViewModel $helperViewModel,
        array $data = []
    ) {
        $this->helperViewModel = $helperViewModel;
        parent::__construct($context, $data);
    }

    /**
     * Get Helper View Model
     *
     * @return object \Webkul\MpVendorAttributeManager\ViewModel\HelperViewModel
     */
    public function getHelperViewModel()
    {
        return $this->helperViewModel;
    }
}
