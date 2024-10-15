<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Buyerquote;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\App\RequestInterface;
use \Magento\Catalog\Model\ProductFactory;
use \Webkul\Mpquotesystem\Model\QuotesFactory;
use \Webkul\Mpquotesystem\Helper;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Webkul\Marketplace\Helper\Data;

class Savequote extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_catalogProduct;

    /**
     * @var \Webkul\Mpquotesystem\Model\Quotes
     */
    protected $_mpquote;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Mail
     */
    protected $_mailHelper;

    /**
     * File Uploader factory.
     *
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var Magento\Framework\Session\SessionManager
     */
    private $_session;

    /**
     * @var [Configurable]
     */
    private $_configurableProduct;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_date;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $fileSystem;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $fileDriver;

    /**
     * @var $utilsFactory
     */
    private $utilsFactory;

    /**
     * @var $utilsFloatFactory
     */
    private $utilsFloatFactory;

    /**
     * @var $uploaderFactory
     */
    private $uploaderFactory;

    /**
     * @param Context $context
     * @param ProductFactory $catalogProduct
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
     * @param Helper\Mail $helperMail
     * @param Helper\Data $helper
     * @param Data $mpHelper
     * @param UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Session\SessionManager $session
     * @param Configurable $configurableProduct
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param \Magento\Customer\Model\Url $urlModel
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Validator\IntUtilsFactory $utilsFactory
     * @param \Magento\Framework\Validator\FloatUtilsFactory $utilsFloatFactory
     * @param \Magento\Framework\File\UploaderFactory $uploaderFactory
     */
    public function __construct(
        Context $context,
        ProductFactory $catalogProduct,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes,
        Helper\Mail $helperMail,
        Helper\Data $helper,
        Data $mpHelper,
        UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Session\SessionManager $session,
        Configurable $configurableProduct,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Customer\Model\Url $urlModel,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Validator\IntUtilsFactory $utilsFactory,
        \Magento\Framework\Validator\FloatUtilsFactory $utilsFloatFactory,
        \Magento\Framework\File\UploaderFactory $uploaderFactory
    ) {
        $this->_customerSession = $customerSession;
        $this->_catalogProduct = $catalogProduct;
        $this->_mpquote = $mpquotes;
        $this->_jsonHelper = $jsonHelper;
        $this->_urlModel = $urlModel;
        $this->_mailHelper = $helperMail;
        $this->_helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->_session = $session;
        $this->_configurableProduct = $configurableProduct;
        $this->_date = $date;
        $this->fileSystem = $fileSystem;
        $this->fileDriver = $fileDriver;
        $this->utilsFactory = $utilsFactory;
        $this->utilsFloatFactory = $utilsFloatFactory;
        $this->uploaderFactory = $uploaderFactory;
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
        $loginUrl = $this->_urlModel->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Save quote from buyer
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!is_array($params)) {
            $this->messageManager
                ->addError(__("Sorry!! Quote can't be saved"));
            return $this->goBack($params['current_url']);
        }
        $fileUploaded = true;
        $validFile = false;
        try {
            $uploader = $this->fileUploaderFactory->create(['fileId' => 'quote_attachment']);
        } catch (\Exception $e) {
            $fileUploaded = false;
        }
        if (isset($uploader)) {
            $validFile = $this->checkAndValidateFile($fileUploaded, $validFile, $uploader);
        }
       
        if ($validFile && $fileUploaded) {
            $this->messageManager
                ->addError(
                    __('Please check the file attached')
                );
            return $this->goBack($params['current_url']);
        }
        $params['attachment'] = $this->_helper->saveAttachment();
        $params['quote_currency_code'] = $this->_helper->getCurrentCurrencyCode();
        if ($this->validateData($params)) {
            $productId = $this->productIdByProductType($params);
            if (!$productId) {
                return $this->getRedirectBack($params);
            }
            $product = $this->_catalogProduct->create()->load($productId);
            $productQty = $this->selectedProductQty($product, $params);
            // remove quantity check as per requirement while quoting a product
            $finalPrice = $this->getProductPrice($params, $product);
            // remove price check because of unlimited price feature
            $productOptions = [];
            $fileNames = [];
            $lastQuoteId = 0;
            $bundleOption = [];
            $request = new \Magento\Framework\DataObject($params);
            $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced(
                $request,
                $product
            );
            $mainProductId = 0;
            if ($productId != $params['product']) {
                $mainProductId = $params['product'];
            } else {
                $mainProductId = $params['product'];
            }

            if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
                $result['error'] = 1;
                $result['message'] = __(string($cartCandidates));
                return $this->getResponse()->representJson(
                    $this->_jsonHelper->jsonEncode($result)
                );
            }
            if ($mainProductId != 0) {
                $mainProduct = $this->_helper->getProduct($mainProductId);
                $quoteQty = $this->_helper->checkProductHasQuote($mainProduct);
                $quoteMinimumQty = $quoteQty ? $quoteQty['min_qty'] : 0;
            } else {
                $quoteQty = $this->_helper->checkProductHasQuote($product);
                $quoteMinimumQty = $quoteQty ? $quoteQty['min_qty'] : 0;
            }
            if ($quoteMinimumQty <= $params['quote_qty']) {
                $bundleOption = $this->setBundleOption($params, $product);
                $params['bundle_option_to_calculate'] = $bundleOption;
                $productOptions = $this->setProductOption($params);
                $params['quote_description'] = trim($params['quote_description']);
                $params['quote_description'] = strip_tags($params['quote_description']);
                $sellerId = 0;
                $marketplaceProduct = $this->mpHelper->getSellerProductDataByProductId($product->getId());
                foreach ($marketplaceProduct as $value) {
                    $sellerId = $value['seller_id'];
                }

                $quote = $this->_mpquote->create()
                    ->setProductId($mainProductId)
                    ->setProductName($params['product_name'])
                    ->setProductPrice($finalPrice)
                    ->setProductOption($this->_helper->convertStringAccToVersion($productOptions, 'encode'))
                    ->setCustomerId($this->_customerSession->getCustomerId())
                    ->setSellerId($sellerId)
                    ->setQuoteQty($params['quote_qty'])
                    ->setQuotePrice($params['quote_price'])
                    ->setQuoteCurrencySymbol($params['quote_currency_symbol'])
                    ->setQuoteCurrencyCode($params['quote_currency_code'])
                    ->setQuoteDesc($params['quote_description'])
                    ->setAttachment($params['attachment'])
                    ->setStatus(\Webkul\Mpquotesystem\Model\Quotes::STATUS_UNAPPROVED)//set pending status
                    ->setCreatedAt($this->_date->date()->format('Y-m-d h:i:s'))
                    ->setSellerPendingNotification(1)
                    ->setAdminPendingNotification(1);
                $this->checkLinks($params, $quote);

                $this->checkBundleOptions($bundleOption, $quote);

                if (isset($params['super_attribute'])) {
                    $quote->setSuperAttribute(
                        $this->_helper->convertStringAccToVersion($params['super_attribute'], 'encode')
                    );
                }
                $lastQuoteId = $quote->save()->getEntityId();

                // send mail
                $this->_mailHelper->newQuote($lastQuoteId, $product);
                $this->messageManager
                    ->addSuccess(
                        __('Your Quote has been successfully saved')
                    );
                return $this->goBack($params['current_url']);
            } else {
                $this->messageManager
                    ->addError(__("Sorry you are not allowed to quote such a low quantity"));
                return $this->goBack($params['current_url']);
            }
        } else {
            $this->errorMsgdataValidation($params);
            $redirectbackUrl = $this->getRedirectBackUrl($params);
            return $this->goBack($redirectbackUrl);
        }
    }

    /**
     * Product Id By Product Type
     *
     * @param array $params
     *
     * @return array
     */
    protected function productIdByProductType($params)
    {
        if (array_key_exists('selected_configurable_option', $params)
            && $params['selected_configurable_option'] != ""
        ) {
            return $params['selected_configurable_option'];
        } elseif (array_key_exists('super_attribute', $params) && $params['super_attribute'] != "") {
            $product = $this->_catalogProduct->create()->load($params['product']);
            $validData = $this->validateParams($params['super_attribute']);
            if (!$validData) {
                return false;
            }
            return $this->_configurableProduct
                ->getProductByAttributes(
                    $params['super_attribute'],
                    $product
                )->getId();
        } else {
            if (array_key_exists('product', $params) && $params['product'] != "") {
                return $params['product'];
            }
        }
    }
    
    /**
     * Return $result with back url in json format
     *
     * @param string $backUrl
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    protected function goBack($backUrl = null)
    {
        $redirectUrl = '';
        if (!$this->getRequest()->isAjax()) {
            $redirectUrl = $this->_url->getUrl('customer/account');
            if ($backUrl || $backUrl = $redirectUrl) {
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setUrl($backUrl);
                return $resultRedirect;
            }
        }
        $result = [];
        if ($backUrl || $backUrl = $redirectUrl) {
            $result['backUrl'] = $backUrl;
        }
        $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($result)
        );
    }
    
    /**
     * Validates quote's data added by customer
     *
     * @param array $params
     *
     * @return boolean
     */
    public function validateData($params)
    {
        $error = 0;
        if (!array_key_exists('quote_qty', $params) && !array_key_exists('quote_price', $params)) {
            $error = 1;
        } else {
            $validator = $this->utilsFactory->create();
            if (!$validator->isValid($params["quote_qty"])) {
                $error = 1;
            }
            $validator = $this->utilsFloatFactory->create();
            if (!$validator->isValid($params["quote_price"])) {
                $error = 1;
            }
            if ($params["quote_description"] == "") {
                $error = 1;
            }
        }
        if ($error == 1) {
            return 0;
        } else {
            return 1;
        }
    }

    /**
     * Selected Product Qty
     *
     * @param object $product
     * @param array  $params
     *
     * @return int
     */
    public function selectedProductQty($product, $params)
    {
        if (isset($params['bundle_option']) && $params['bundle_option']) {
            $bundleOption = $this->setBundleOption($params, $product);
            $quantity = $this->_helper->getBundleProductQuatity(
                $product,
                $bundleOption
            );
            return $quantity;
        }
        $quantity = $product->getQuantityAndStockStatus()['qty'];
        return $quantity;
    }

    /**
     * Set Bundle Option
     *
     * @param array  $params
     * @param object $product
     *
     * @return object
     */
    protected function setBundleOption($params, $product)
    {
        if (isset($params['bundle_option'])) {
            $bundleOption = $this->getBundleProductData($params, $product);
            return $bundleOption;
        }
    }
    
    /**
     * Get Bundle Product Data
     *
     * @param array  $params
     * @param object $product
     *
     * @return array
     */
    public function getBundleProductData($params, $product)
    {
        $bundleOption = [];
        if (array_key_exists('bundle_option', $params)) {
            $bundleOption['bundle_option'] = $params['bundle_option'];
        }
        if ($product->getEntityId() && $product->getTypeId() == 'bundle') {
            $selectionCollection = $product
                ->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );
            foreach ($bundleOption['bundle_option'] as $optionkey => $optionValue) {
                foreach ($selectionCollection as $selectionValue) {
                    $selectedOption = $selectionValue->getOptionId();
                    $selectionId = $selectionValue->getSelectionId();
                    $selectionQty = $selectionValue->getSelectionQty();
                    if ($selectedOption == $optionkey && $selectionId == $optionValue) {
                        if (array_key_exists('bundle_option_qty', $params)
                            && isset($params['bundle_option_qty'])
                            && array_key_exists($optionkey, $params['bundle_option_qty'])
                        ) {
                            $bundleOption['bundle_option_qty'][$optionkey] = $params['bundle_option_qty'][$optionkey];
                        } else {
                            $bundleOption['bundle_option_qty'][$optionkey] = $selectionQty;
                        }
                        $bundleOption['bundle_option_price'][$optionkey] = $selectionValue->getPrice();
                        $bundleOption['bundle_option_product'][$optionkey] = $selectionValue->getEntityId();
                    }
                }
            }
        }
        return $bundleOption;
    }
    
    /**
     * Get ProductPrice According to SpecialPrice, TierPrice and Custom Option.
     *
     * @param array $params
     * @param object $product
     *
     * @return price
     */
    public function getProductPrice($params, $product)
    {
        if (array_key_exists('bundle_option', $params) && $params['bundle_option']) {
            $bundleOption = $this->setBundleOption($params, $product);
            $params['bundle_option_to_calculate'] = $bundleOption;
        }
        $productPrice = $this->_helper->calculateProductPrice(
            $params
        );
        return($productPrice);
    }
    
    /**
     * Set Product Option
     *
     * @param array $params
     *
     * @return array
     */
    protected function setProductOption($params)
    {
        if (isset($params['options'])) {
            foreach ($params['options'] as $key => $value) {
                if (empty($value)) {
                    unset($params['options'][$key]);
                }
            }
            return $params['options'];
        }
    }

    /**
     * Error Msg Data Validation
     *
     * @param array $params
     *
     * @return void
     */
    protected function errorMsgdataValidation($params)
    {
        if ($this->_session->getCurrentUrl()) {
            $params['current_url'] = $this->_session->getCurrentUrl();
            $this->_session->unsCurrentUrl();
        } else {
            $this->messageManager->addError(__('Data Validation Failed'));
        }
    }

    /**
     * Validate File
     *
     * @param array $file
     *
     * @return void
     */
    public function validateFile($file)
    {
        $mime = mime_content_type($file['tmp_name']);
        $ext = $this->uploaderFactory->create($file['name'], PATHINFO_EXTENSION)->getFileExtension();
        $mimeSet = $this->getMimeSet();
        if ($mimeSet[$ext] == $mime) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get Mime Set
     *
     * @return array
     */
    public function getMimeSet()
    {
        return $data = [
            'doc' => 'application/msword',
            'docx' => 'application/msword',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'zip' => 'application/zip',
            'pdf' => 'application/pdf'
        ];
    }
    
    /**
     * Validate Params
     *
     * @param array $data
     *
     * @return void
     */
    public function validateParams($data)
    {
        $dataValid = true;
        foreach ($data as $key => $value) {
            if (empty($value)) {
                $dataValid = false;
            }
        }
        return $dataValid;
    }

    /**
     * Check Links
     *
     * @param array  $params
     * @param object $quote
     *
     * @return void
     */
    public function checkLinks($params, $quote)
    {
        if (isset($params['links'])) {
            $quote->setLinks(
                $this->_helper->convertStringAccToVersion(
                    $params['links'],
                    'encode'
                )
            );
        }
    }

    /**
     * Get Redirect BackUrl
     *
     * @param array $params
     *
     * @return array
     */
    public function getRedirectBackUrl($params)
    {
        if (!array_key_exists('current_url', $params)) {
            return "";
        } else {
            $params['current_url'];
        }
    }

    /**
     * Check And Validate File
     *
     * @param bool $fileUploaded
     * @param file $validFile
     * @param object $uploader
     *
     * @return void
     */
    public function checkAndValidateFile($fileUploaded, $validFile, $uploader)
    {
        if ($fileUploaded) {
            $validFile = $uploader->checkMimeType($this->getMimeSet());
        }
    }

    /**
     * Get Redirect Back
     *
     * @param array $params
     *
     * @return void
     */
    public function getRedirectBack($params)
    {
        $this->errorMsgdataValidation($params);
        if (!array_key_exists('current_url', $params)) {
            return $this->goBack('');
        }
        return $this->goBack($params['current_url']);
    }
    
    /**
     * Check Bundle Options
     *
     * @param object $bundleOption
     * @param object $quote
     *
     * @return void
     */
    public function checkBundleOptions($bundleOption, $quote)
    {
        if (isset($bundleOption)) {
            $quote->setBundleOption($this->_helper->convertStringAccToVersion($bundleOption, 'encode'));
        }
    }
}
