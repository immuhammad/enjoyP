<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Block\Adminhtml\Rma\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Webkul\MpRmaSystem\Helper\Data as mpRmaHelper;

class Details extends \Magento\Backend\Block\Widget implements TabInterface
{
    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param mpRmaHelper $mpRmaHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        mpRmaHelper $mpRmaHelper,
        array $data = []
    ) {
        $this->mpRmaHelper = $mpRmaHelper;
        parent::__construct($context, $data);
    }
    
    /**
     * Set template
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('rma/view/tab/details.phtml');
    }

    /**
     * Function for getting tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * Function for getting tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('RMA Information');
    }

    /**
     * Function for checking tab show or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Function for checking tab is hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Function for accessing the module helper
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function helper()
    {
        return $this->mpRmaHelper;
    }
}
