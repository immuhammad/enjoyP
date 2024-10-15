<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Controller\Adminhtml\Product;

use Magento\Backend\App\Action\Context;

class Run extends \Magento\Backend\App\Action
{
    /**
     * @var \Webkul\MpMassUpload\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param Context $context
     * @param \Webkul\MpMassUpload\Helper\Data $massUploadHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        Context $context,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_massUploadHelper = $massUploadHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_massUploadHelper;
        $sellerId = $this->getRequest()->getParam('seller_id');
        $profileId = $this->getRequest()->getParam('id');
        $wholeData = $this->getRequest()->getParams();
        if (!empty($wholeData['row'])) {
            $row = $wholeData['row'];
            $result = $helper->saveProduct($sellerId, $row, $wholeData);
        } else {
            $result['error'] = 1;
            $result['msg'] = __('Product data not exists.');
        }
        if ($result['error']) {
            $result['msg'] = '<div class="wk-mu-error wk-mu-box">'.$result['msg'].'</div>';
        }
        $result = $this->_jsonHelper->jsonEncode($result);
        $this->getResponse()->representJson($result);
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpMassUpload::run');
    }
}
