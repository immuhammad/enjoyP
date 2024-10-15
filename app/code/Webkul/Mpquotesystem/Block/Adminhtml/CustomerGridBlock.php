<?php
/**
 * Block for Customer list at admin end.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Block\Adminhtml;

class CustomerGridBlock extends \Magento\Framework\View\Element\Template
{

    /**
     * Construct
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param \Magento\Framework\Json\Helper\Data               $jsonHelper
     * @param \Magento\Framework\Data\Form\FormKey              $formKey
     * @param \Magento\Backend\Model\Session                    $backendSession
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Backend\Model\Session $backendSession,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->formKey = $formKey;
        $this->backendSession = $backendSession;
        parent::__construct($context, $data);
    }
    
    /**
     * Get Json Helper
     *
     * @return Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
         return $this->formKey->getFormKey();
    }

    /**
     * Check sorting or filter is applied on grid
     *
     * @return bool
     */
    public function isSortData()
    {
        return $this->backendSession->getIsSort();
    }
}
