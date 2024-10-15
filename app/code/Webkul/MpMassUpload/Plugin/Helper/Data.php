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
namespace Webkul\MpMassUpload\Plugin\Helper;

class Data
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->_authSession = $authSession;
    }

    /**
     * function to run to change the retun data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterIsSeller(\Webkul\Marketplace\Helper\Data $helperData, $result)
    {
        if (!empty($this->_authSession->getUser())) {
            $result = 1;
        }
        return $result;
    }

    /**
     * function to run to change the retun data of afterIsRightSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterIsRightSeller(\Webkul\Marketplace\Helper\Data $helperData, $result)
    {
        if (!empty($this->_authSession->getUser())) {
            $result = 1;
        }
        return $result;
    }

    /**
     * function to run to change the return data of afterIsSeller.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['mpmassupload/product/finish'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/options'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/profile'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/run'] = 'mpmassupload/product/view';
        $result['mpmassupload/product/upload'] = 'mpmassupload/product/view';
        $result['mpmassupload/dataflow_profile/delete'] = 'mpmassupload/dataflow/profile';
        $result['mpmassupload/dataflow_profile/edit'] = 'mpmassupload/dataflow/profile';
        $result['mpmassupload/dataflow_profile/massDelete'] = 'mpmassupload/dataflow/profile';
        $result['mpmassupload/dataflow_profile/save'] = 'mpmassupload/dataflow/profile';
        return $result;
    }
}
