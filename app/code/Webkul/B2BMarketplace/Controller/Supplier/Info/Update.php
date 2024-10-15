<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Supplier\Info;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;

class Update extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Webkul\B2BMarketplace\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @param Context $context
     * @param Session                                      $customerSession
     * @param FormKeyValidator                             $formKeyValidator
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param \Webkul\B2BMarketplace\Helper\Data           $helper
     * @param \Webkul\Marketplace\Helper\Data              $mpHelper
     * @param DataPersistorInterface                       $dataPersistor
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        DataPersistorInterface $dataPersistor
    ) {
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_supplierFactory = $supplierFactory;
        $this->_helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_objectManager->get(
            \Magento\Customer\Model\Url::class
        )->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            if ($this->getRequest()->isPost()) {
                try {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            'b2bmarketplace/supplier/settings',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $fields = $this->getRequest()->getParams();
                    $fields = $this->getSellerProfileFields($fields);
                    $sectionId = '';
                    $section = $this->getRequest()->getParam("section");
                    if ($section == "company_info") {
                        $sectionId = '#supplier.company.info';
                    } elseif ($section == "supplier_info") {
                        $sectionId = '#supplier.info';
                    } elseif ($section == "company_overview") {
                        $sectionId = '#supplier.company.overview';
                    } elseif ($section == "policies") {
                        $sectionId = '#supplier.policies';
                    } elseif ($section == "supplier_categories") {
                        $sectionId = '#supplier.categories';
                    }
                    list($errors, $fields) = $this->validateprofiledata($fields);

                    if (empty($errors)) {
                        if ($section == "company_info") {
                            $this->_helper->updateCompanyInfo($fields);
                        }

                        if ($section == "supplier_info") {
                            $this->_helper->updateSupplierInfo($fields);
                        }

                        if ($section == "company_overview") {
                            $this->_helper->updateCompanyData($fields);
                        }

                        if ($section == "policies") {
                            $this->_helper->updatePolicies($fields);
                        }

                        if ($section == "supplier_categories") {
                            $this->_helper->updateCategories();
                        }

                        $this->messageManager->addSuccess(__('Information was successfully saved.'));

                        return $this->resultRedirectFactory->create()->setPath(
                            'b2bmarketplace/supplier/settings',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    } else {
                        foreach ($errors as $message) {
                            $this->messageManager->addError($message);
                        }
                        $this->dataPersistor->set('seller_profile_data', $fields);

                        return $this->resultRedirectFactory->create()->setPath(
                            'b2bmarketplace/supplier/settings'.$sectionId,
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->dataPersistor->set('seller_profile_data', $fields);

                    return $this->resultRedirectFactory->create()->setPath(
                        'b2bmarketplace/supplier/settings'.$sectionId,
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'b2bmarketplace/supplier/settings',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    protected function validateprofiledata(&$fields)
    {
        $errors = [];
        foreach ($fields as $code => $value) {
            switch ($code):
                case 'twitter_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('
                        Twitter ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'facebook_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Facebook ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'instagram_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Instagram ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'gplus_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Google Plus ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'youtube_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Youtube ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'vimeo_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Vimeo ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'pinterest_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Pinterest ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'moleskine_id':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{~?><>, |=+¬]/', $value)
                    ) {
                        $errors[] = __('Moleskine ID cannot contain space and special characters, 
                        allowed special characters are @,#,_,-');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'taxvat':
                    if (trim($value) != '' &&
                        preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)
                    ) {
                        $errors[] = __('Tax/VAT Number cannot contain space and special characters');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
                    break;
                case 'shop_title':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'contact_number':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_locality':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    break;
                case 'company_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->mpHelper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'meta_keyword':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->mpHelper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'meta_description':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->mpHelper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'shipping_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->mpHelper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'privacy_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->mpHelper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'return_policy':
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $value = $this->mpHelper->validateXssString($value);
                        $fields[$code] = $value;
                    break;
                case 'background_width':
                    if (trim($value) != '' &&
                        strlen($value) != 6 &&
                        substr($value, 0, 1) != '#'
                    ) {
                        $errors[] = __('Invalid Background Color');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $fields[$code] = $value;
                    }
            endswitch;
        }

        return [$errors, $fields];
    }

    protected function getSellerProfileFields($fields = [])
    {
        if (!isset($fields['tw_active'])) {
            $fields['tw_active'] = 0;
        }
        if (!isset($fields['fb_active'])) {
            $fields['fb_active'] = 0;
        }
        if (!isset($fields['gplus_active'])) {
            $fields['gplus_active'] = 0;
        }
        if (!isset($fields['youtube_active'])) {
            $fields['youtube_active'] = 0;
        }
        if (!isset($fields['vimeo_active'])) {
            $fields['vimeo_active'] = 0;
        }
        if (!isset($fields['instagram_active'])) {
            $fields['instagram_active'] = 0;
        }
        if (!isset($fields['pinterest_active'])) {
            $fields['pinterest_active'] = 0;
        }
        if (!isset($fields['moleskine_active'])) {
            $fields['moleskine_active'] = 0;
        }
        return $fields;
    }
}
