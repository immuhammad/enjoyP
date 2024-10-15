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

namespace Webkul\B2BMarketplace\Controller\Supplier;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerFactory as CustomerResourceFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Helper\Email as MpEmailHelper;

class Register extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Helper\Address
     */
    protected $addressHelper;

    /**
     * @var \Magento\Customer\Model\Metadata\FormFactory
     */
    protected $formFactory;

    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var \Magento\Customer\Api\Data\RegionInterfaceFactory
     */
    protected $regionDataFactory;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressDataFactory;

    /**
     * @var \Magento\Customer\Model\Registration
     */
    protected $registration;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    protected $customerDataFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @var \Magento\Customer\Model\CustomerExtractor
     */
    protected $customerExtractor;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlModel;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\Account\Redirect
     */
    private $accountRedirect;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    private $formKeyValidator;
    
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customerModel;
    
    /**
     * @var \Magento\Customer\Model\ResourceModel\CustomerFactory
     */
    private $customerResourceFactory;

    private $mpHelper;

    private $email;

    private $mpEmailHelper;

    /**
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Helper\Address $addressHelper
     * @param \Magento\Framework\UrlFactory $urlFactory
     * @param \Magento\Customer\Model\Metadata\FormFactory $formFactory
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory
     * @param \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory
     * @param \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\Registration $registration
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Customer\Model\CustomerExtractor $customerExtractor
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param \Magento\Customer\Model\Account\Redirect $accountRedirect
     * @param \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Webkul\B2BMarketplace\Helper\Data $helper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Customer\Helper\Address $addressHelper,
        \Magento\Framework\UrlFactory $urlFactory,
        \Magento\Customer\Model\Metadata\FormFactory $formFactory,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Api\Data\RegionInterfaceFactory $regionDataFactory,
        \Magento\Customer\Api\Data\AddressInterfaceFactory $addressDataFactory,
        \Magento\Customer\Api\Data\CustomerInterfaceFactory $customerDataFactory,
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Registration $registration,
        \Magento\Framework\Escaper $escaper,
        \Magento\Customer\Model\CustomerExtractor $customerExtractor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\Account\Redirect $accountRedirect,
        \Webkul\B2BMarketplace\Model\SupplierFactory $supplierFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator = null,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Customer $customerModel,
        CustomerResourceFactory $customerResourceFactory,
        MpHelper $mpHelper,
        \Webkul\B2BMarketplace\Helper\Email $email,
        MpEmailHelper $mpEmailHelper
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->accountManagement = $accountManagement;
        $this->addressHelper = $addressHelper;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerUrl = $customerUrl;
        $this->registration = $registration;
        $this->urlModel = $urlFactory->create();
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountRedirect = $accountRedirect;
        $this->_supplierFactory = $supplierFactory;
        $this->_encryptor = $encryptor;
        $this->_helper = $helper;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->customerModel = $customerModel;
        $this->customerResourceFactory = $customerResourceFactory;
        $this->customerRepository = $customerRepository;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(
            \Magento\Framework\Data\Form\FormKey\Validator::class
        );
        $this->mpHelper = $mpHelper;
        $this->email = $email;
        $this->mpEmailHelper = $mpEmailHelper;
        parent::__construct(
            $context,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $accountManagement,
            $addressHelper,
            $urlFactory,
            $formFactory,
            $subscriberFactory,
            $regionDataFactory,
            $addressDataFactory,
            $customerDataFactory,
            $customerUrl,
            $registration,
            $escaper,
            $customerExtractor,
            $dataObjectHelper,
            $accountRedirect,
            $customerRepository,
            $formKeyValidator
        );
    }

    /**
     * Create Supplier account action
     *
     * @return Json
     */
    public function execute()
    {
        if ($this->_helper->isUserLoggedIn()) {
            $url = $this->urlModel->getUrl('b2bmarketplace/supplier/account', ['_secure' => true]);
            $result["error"] = true;
            $result["reload"] = true;
            $result["redirect"] = $url;
            $resultJson = $this->_resultJsonFactory->create();
            return $resultJson->setData($result);
        }
        $result = [];
        $result["error"] = false;
        $result["msg"] = "";
        $result["reload"] = false;

        if (!$this->getRequest()->isPost() || !$this->formKeyValidator->validate($this->getRequest())) {
            $result["error"] = true;
            $result["reload"] = true;
            $result["msg"] =__("Invalid request.");
            $resultJson = $this->_resultJsonFactory->create();
            return $resultJson->setData($result);
        }

        try {
            $data = $this->getRequest()->getParams();
            $validateResult = $this->_helper->validateSupplierRegistrationData($data);

            if (!$validateResult['error']) {
                // For saving supplier deafult address
                $this->getRequest()->setPostValue('create_address', 1);
                $this->getRequest()->setPostValue('default_billing', 1);
                $this->getRequest()->setPostValue('default_shipping', 1);
                $this->customerSession->regenerateId();
                $address = $this->extractAddress();
                $addresses = $address === null ? [] : [$address];

                $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
                $customer->setAddresses($addresses);

                // Set Supplier Group Id
                $groupId = $this->_helper->getSupplierCustomerGroupId();
                if ($this->addressHelper->isVatValidationEnabled($this->storeManager->getStore())) {
                    $customer->setData('disable_auto_group_change', true);
                }
                $customer->setGroupId($groupId);

                $password = $this->getRequest()->getParam('password');
                $confirmation = $this->getRequest()->getParam('password_confirmation');
                $redirectUrl = $this->customerSession->getBeforeAuthUrl();

                $this->checkPasswordConfirmation($password, $confirmation);

                $shopTitle = trim($this->getRequest()->getParam('company'));

                $name = $data['firstname']." ".$data['lastname'];
                /*New Account email confirmation */
                $adminStoremail = $this->_helper->getAdminEmail();
                $adminEmail = $adminStoremail?
                $adminStoremail:$this->mpHelper->getDefaultTransEmailId();
                $adminUsername = $this->_helper->getAdminName();
                $emailTempVariables['name'] = $name;
                $emailTempVariables['email'] = $data['email'];
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];
                $receiverInfo = [
                    'name' => $name,
                    'email' => $data['email'],
                ];
                $this->email->newAccountEmail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );

                $customer = $this->accountManagement
                    ->createAccount($customer, $password, $redirectUrl);

                if ($this->getRequest()->getParam('is_subscribed', false)) {
                    $this->subscriberFactory->create()
                    ->subscribeCustomerById($customer->getId());
                }

                // For Creating Supplier Account
                $shopUrl = trim($this->getRequest()->getParam("company_url"));
                $this->getRequest()->setPostValue('create_address', 1);
                $this->getRequest()->setPostValue('is_seller', 1);
                $this->getRequest()->setPostValue('profileurl', $shopUrl);
                $token = $this->_helper->getToken();
                $this->getRequest()->setPostValue('token', $token);
                $this->getRequest()->setPostValue('default_address_id', $address->getId());
                $this->getRequest()->setPostValue('shop_title', $shopTitle);

                $this->_eventManager->dispatch(
                    'customer_register_success',
                    ['account_controller' => $this, 'customer' => $customer]
                );

                // Sent Verify Supplier Mail
                // $name = $data['firstname']." ".$data['lastname'];
                $this->_helper->sendVerifySupplierEmail($name, $data['email'], $token);

                $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
                if ($confirmationStatus ===
                \Magento\Customer\Api\AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED
                ) {
                    $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                    $this->messageManager->addSuccess(
                        __(
                            'You must confirm your account. Please check your email for the confirmation link or
                             <a href="%1">click here</a> for a new link.',
                            $email
                        )
                    );
                    $url = $this->urlModel->getUrl('b2bmarketplace/supplier/login', ['_secure' => true]);
                    $result["error"] = true;
                    $result["reload"] = true;
                    $result["redirect"] = $url;
                    $resultJson = $this->_resultJsonFactory->create();
                    return $resultJson->setData($result);
                } else {
                    $this->customerSession->setCustomerDataAsLoggedIn($customer);

                    /** Save the supplier company name **/
                    try {
                        $customerId = $this->customerSession->getCustomer()->getId();
                        $customerNew = $this->customerModel->load($customerId);
                        $customerData = $customerNew->getDataModel();
                        $companyName = $this->getRequest()->getParam('company')?:"";
                        $customerData->setCustomAttribute('wk_supplier_company_name', $companyName);
                        $customerNew->updateData($customerData);
                        $customerResource = $this->customerResourceFactory->create();
                        $customerResource->saveAttribute($customerNew, 'wk_supplier_company_name');
                    } catch (\Exception $e) {
                        $result["error"] = true;
                        $result["reload"] = true;
                        $result["msg"] = $e->getMessage();
                        $resultJson = $this->_resultJsonFactory->create();
                        return $resultJson->setData($result);
                    }
                    $result["error"] = false;
                    $result["reload"] = false;
                    $result["msg"] = $this->getSuccessMessage();
                }
                if ($this->getCookieMetaDataManager()->getCookie('mage-cache-sessid')) {
                    $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                    $metadata->setPath('/');
                    $this->getCookieMetaDataManager()->deleteCookie('mage-cache-sessid', $metadata);
                }
            } else {
                $msg = implode("\n", $validateResult["error_list"]);
                $result = [];
                $result["error"] = true;
                $result["msg"] = $msg;
                $resultJson = $this->_resultJsonFactory->create();
                return $resultJson->setData($result);
            }

            if ($this->scopeConfig->getValue("marketplace/general_settings/seller_approval") == 0) {
                $adminStoremail = $this->_helper->getAdminEmail();
                $adminEmail=$adminStoremail? $adminStoremail:$this->mpHelper->getDefaultTransEmailId();
                $adminUsername = $this->_helper->getAdminName();
                $emailTempVariables['myvar1'] = $name;
                $emailTempVariables['myvar2'] = $this->storeManager
                ->getStore()->getBaseUrl().'marketplace/account/login';
                $senderInfo = [
                    'name' => $adminUsername,
                    'email' => $adminEmail,
                ];
                $receiverInfo = [
                    'name' => $name,
                    'email' => $data['email'],
                ];
                $this->mpEmailHelper->sendSellerApproveMail(
                    $emailTempVariables,
                    $senderInfo,
                    $receiverInfo
                );
            }

        } catch (\Magento\Framework\Exception\StateException $e) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            $message = __(
                'There is already an account with this email address. 
                If you are sure that it is your email address, 
                <a href="%1">click here</a> to get your password and access your account.',
                $url
            );

            $result["error"] = true;
            $result["reload"] = true;
            $result["msg"] = $message;
        } catch (\Magento\Framework\Exception\InputException $e) {
            $messageArr[] = $this->escaper->escapeHtml($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $messageArr[] = $this->escaper->escapeHtml($error->getMessage());
            }

            $result["error"] = true;
            $result["reload"] = true;
            $result["msg"] = implode('\n', $messageArr);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result["error"] = true;
            $result["reload"] = true;
            $result["msg"] = $this->escaper->escapeHtml($e->getMessage());
        } catch (\Exception $e) {
            $result["error"] = true;
            $result["reload"] = false;
            $result["msg"] = $e->getMessage();
        }

        $this->customerSession->setCustomerFormData($this->getRequest()->getPostValue());

        $resultJson = $this->_resultJsonFactory->create();
        return $resultJson->setData($result);
    }

    /**
     * Retrieve cookie manager
     *
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieMetaDataManager()
    {
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }
}
