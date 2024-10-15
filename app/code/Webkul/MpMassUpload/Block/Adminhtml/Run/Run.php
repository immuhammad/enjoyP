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
namespace Webkul\MpMassUpload\Block\Adminhtml\Run;

class Run extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_massUploadHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\MpMassUpload\Helper\Data $massUploadHelper,
        array $data = []
    ) {
        $this->_massUploadHelper = $massUploadHelper;
        $this->_jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Initialize MpMassUpload Run Run block.
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_MpMassUpload';
        $this->_controller = 'adminhtml_run';
        parent::_construct();
        $this->buttonList->remove('save');
        $this->buttonList->remove('delete');
        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
    }

    /**
     * Get Header Text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Manage Mass Upload');
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * encodes data in json format
     *
     * @param array $data
     * @return json array
     */
    public function jsonEncode($data)
    {
        return $this->_jsonHelper->jsonEncode($data);
    }

    /**
     * Get Total Product to Upload
     *
     * @param int $profileId
     *
     * @return int
     */
    public function getTotalCount($profileId = 0)
    {
        return $this->_massUploadHelper->getTotalCount($profileId);
    }

    /**
     * Get Current Profile Id
     *
     * @return int
     */
    public function getProfileId()
    {
        return $this->_massUploadHelper->getProfileId();
    }

    /**
     * Get Seller Id
     *
     * @return int
     */
    public function getSellerId()
    {
        return $this->_massUploadHelper->getSellerId();
    }
    
    /**
     * Save Product
     *
     * @param int $profileId
     *
     * @return array
     */
    public function getProductPostData($profileId, $row)
    {
        return $this->_massUploadHelper->getProductPostData($profileId, $row);
    }
}
