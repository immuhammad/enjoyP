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

namespace Webkul\MpServiceFee\Block\Element;

class Template extends \Magento\Framework\View\Element\Template
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->serviceHelper = $serviceHelper;
    }

    /**
     * Get service helper
     *
     * @return \Webkul\MpServiceFee\Helper\Servicehelper
     */
    public function getServiceHelper()
    {
        return $this->serviceHelper;
    }
}
