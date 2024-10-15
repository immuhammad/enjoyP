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
namespace Webkul\MpVendorAttributeManager\Plugin\Controller;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory as AttributeCollectionFactory;
use Magento\Store\Model\ScopeInterface;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory as
    VendorAttributeCollectionFactory;

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $vendorAttributeCollectionFactory;

    /**
     * Constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param RedirectInterface $redirect
     * @param VendorAttributeCollectionFactory $vendorAttributeCollectionFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        RedirectInterface $redirect,
        VendorAttributeCollectionFactory $vendorAttributeCollectionFactory
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->_storeManager = $storeManager;
        $this->redirect = $redirect;
        $this->vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
    }

    /**
     * Undocumented function
     *
     * @param object $subject
     * @param object $proceed
     * @param string $data
     * @param mixed $requestInfo
     * @return void
     */
    public function aroundExecute(
        $subject,
        $proceed,
        $data = "null",
        $requestInfo = false
    ) {
        $resultRedirect = $subject->resultRedirectFactory->create();
        $refererUrl = explode('?', $this->redirect->getRefererUrl())[0];

        $collection = $this->vendorAttributeCollectionFactory->create()->getVendorAttributeCollection();

        $error = [];
        $customData = $this->_request->getParams();
        foreach ($collection as $attribute) {
            foreach ($customData as $attributeCode => $attributeValue) {
                if ($attributeCode==$attribute->getAttributeCode()) {
                    if ($attribute->getIsRequired() && empty($attributeValue)) {
                        $error[] = $attribute->getAttributeCode();
                    }
                }
            }
        }
        if (!empty($error)) {
            $subject->messageManager->addError(
                __(
                    'Please Fill all the Required Fields.'
                )
            );
            $resultRedirect->setPath('customer/account/create', ['_secure' => true, 'v' => '1']);
            return $resultRedirect;
        }

        if ($this->getConfigData('enable_registration')) {
            $params = $this->_request->getParams();
            if (array_key_exists('account_create_privacy_condition', $params)) {
                if (!isset($params['account_create_privacy_condition']) ||
                  $params['account_create_privacy_condition'] == 0
                ) {
                    $subject->messageManager->addError(__('Check Term and Condition & Privacy & cookie Policy.'));
                    if (strrpos($refererUrl, 'marketplace') !== false) {
                        $resultRedirect->setPath('marketplace', ['_secure' => true]);
                    } else {
                        $resultRedirect->setPath('*/*', ['_secure' => true, 'v' => '1']);
                    }
                    return $resultRedirect;
                }
            }
        }
        return $proceed();
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    protected function getConfigData($field)
    {
        $path = 'marketplace/termcondition/'.$field;
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }
}
