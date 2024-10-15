<?php
/**
 * Save quote at admin end.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Adminhtml\Buyerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\ProductFactory;
use Webkul\Mpquotesystem\Helper;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Customer\Model\Url;
use Webkul\Mpquotesystem\Api\QuoteRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Validator;

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
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Webkul\Mpquotesystem\Model\QuotesFactory
     */
    protected $_quoteFactory;

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
     * @var Webkul\Mpquotesystem\Api\QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneinterface;

    /**
     * @var Magento\Framework\Session\SessionManager
     */
    private $_session;

    /**
     * @var [StockItemRepository]
     */
    private $_stockItemRepository;

    /**
     * @var [Configurable]
     */
    private $_configurableProduct;

    /**
     * @param Context $context
     * @param Validator\FloatUtils $floatUtils
     * @param Validator\IntUtils $intUtils
     * @param ProductFactory $catalogProduct
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory $quotes
     * @param Helper\Mail $helperMail
     * @param Helper\Data $helper
     * @param QuoteRepositoryInterface $quoteRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param TimezoneInterface $timezoneinterface
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository
     * @param Configurable $configurableProduct
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        Validator\FloatUtils $floatUtils,
        Validator\IntUtils $intUtils,
        ProductFactory $catalogProduct,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Mpquotesystem\Model\QuotesFactory $quotes,
        Helper\Mail $helperMail,
        Helper\Data $helper,
        QuoteRepositoryInterface $quoteRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        TimezoneInterface $timezoneinterface,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        Configurable $configurableProduct,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->floatUtils = $floatUtils;
        $this->intUtils = $intUtils;
        $this->_customerSession = $customerSession;
        $this->_catalogProduct = $catalogProduct;
        $this->_quoteFactory = $quotes;
        $this->_jsonHelper = $jsonHelper;
        $this->_mailHelper = $helperMail;
        $this->_helper = $helper;
        $this->_date = $date;
        $this->_timezoneinterface = $timezoneinterface;
        $this->_quoteRepository = $quoteRepository;
        $this->_stockItemRepository = $stockItemRepository;
        $this->_configurableProduct = $configurableProduct;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * Save quote from buyer.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $result = [];
        if (!$this->getRequest()->isPost()) {
            $redirectUrl = $this->_url->getUrl('mpquotesystem/managequotes/index/');
            $this->messageManager->addError(
                __(
                    "Sorry some error occured!!!"
                )
            );
            return;
        }
        $params = $this->getRequest()->getParams();

        if (!is_array($params)) {
            $this->messageManager->addError(
                __("Sorry!! Quote can't be saved.")
            );
            return;
        }

        $errors = $this->validateData($params);
        if (empty($errors)) {
            $result = $this->saveQuoteData($params, 0);
            if (!$result) {
                $this->messageManager
                ->addSuccess(__("Your Quote has been successfully sent"));
            }
        } else {
            foreach ($errors as $message) {
                $this->messageManager->addError($message);
            }
            return;
        }
    }

    /**
     * Save quote data
     *
     * @param array $params
     * @param int   $i
     * @return void
     */
    public function saveQuoteData($params, $i)
    {
        $productId = $this->productIdByProductType($params);
        $product = $this->_catalogProduct->create()->load($productId);
        $productQty = $this->selectedProductQty($product, $params);
        $mainProductId = 0;
        $count = 0;
        if ($productId != $params['product']) {
            $mainProductId = $params['product'];
        }
        if ($productQty==0 && $product->getTypeId() != 'downloadable') {
            $this->messageManager
                ->addError(__("Sorry!! Quantity of this product in stock is zero."));
            return;
        }
        $finalPrice = $this->getProductPrice($params, $product);
        $productOptions = [];
        $fileNames = [];
        $lastQuoteId = 0;
        $bundleOption = [];
        $request = new \Magento\Framework\DataObject($params);
        $cartCandidates = $product->getTypeInstance()->prepareForCartAdvanced(
            $request,
            $product
        );
        if (is_string($cartCandidates) || $cartCandidates instanceof \Magento\Framework\Phrase) {
            $result['error'] = 1;
            $result['message'] = (string)__($cartCandidates);
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
        if ($mainProductId != 0) {
            $mainProduct = $this->_helper->getProduct($mainProductId);
            $quoteMinimumQty = $mainProduct->getMinQuoteQty();
        } else {
            $quoteMinimumQty = $product->getMinQuoteQty();
        }
        // get config quote qty
        if (!$quoteMinimumQty) {
            $quoteMinimumQty = $this->_helper->getConfigMinQty();
        }
        if ($quoteMinimumQty <= $params['quote_qty']) {
            if (array_key_exists('bundle_option', $params) && $params['bundle_option']) {
                $bundleOption = $this->setBundleOption($params, $product);
                $params['bundle_option_to_calculate'] = $bundleOption;
            }
            $productOptions = $this->setProductOption($params);
            $params['quote_description'] = trim($params['quote_description']);
            $params['quote_description'] = strip_tags($params['quote_description']);
            $attachments = '';
            if (isset($params['attachments']) && is_array($params['attachments'])) {
                $attachments = implode(',', $params['attachments']);
            }
            $quotePrice = $this->_helper->getBaseCurrencyPrice($params['quote_price']);
            $customerId = (int)$params['customer_id'];
            try {
                $quote = $this->_quoteFactory->create()
                    ->setCustomerId((int)$params['customer_id'])
                    ->setProductId($params['product'])
                    ->setProductName($product->getName())
                    ->setProductPrice($finalPrice)
                    ->setProductOption($this->_helper->convertStringAccToVersion($productOptions, 'encode'))
                    ->setQuoteQty($params['quote_qty'])
                    ->setQuotePrice($params['quote_price'])
                    ->setQuoteDesc($params['quote_description'])
                    ->setStatus(\Webkul\Mpquotesystem\Model\Quotes::STATUS_UNAPPROVED)//set pending status
                    ->setCreatedAt(time())
                    ->setAttachment($attachments)
                    ->setQuoteCurrencyCode($this->_helper->getCurrentCurrency())
                    ->setQuoteCurrencySymbol($this->_helper->getCurrentCurrencyCodesymbol());
                if (isset($params['links'])) {
                    $quote->setLinks($this->_helper->convertStringAccToVersion($params['links'], 'encode'));
                }
                if (isset($bundleOption)) {
                    $quote->setBundleOption($this->_helper->convertStringAccToVersion($bundleOption, 'encode'));
                }
                if (isset($params['super_attribute'])) {
                    $quote->setSuperAttribute(
                        $this->_helper->convertStringAccToVersion($params['super_attribute'], 'encode')
                    );
                }
                $lastQuoteId = $quote->save()->getEntityId();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            
            // send mail
            $this->_mailHelper->newQuote($lastQuoteId, $product);
            
            return $count;
        } else {
            $this->messageManager
                ->addError(__("Sorry you are not allowed to quote such a low quantity for ". $product->getName()));
            $count ++;
            return $count;
        }
    }

    /**
     * Get ProductPrice According to SpecialPrice, TierPrice and Custom Option.
     *
     * @param array  $params
     * @param object $product
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
     * SetProductOption
     *
     * @param  array $params
     * @return mixed
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
     * SetBundleOption
     *
     * @param  array                          $params
     * @param  \Magento\Catalog\Model\Product $product
     * @return array
     */
    protected function setBundleOption($params, $product)
    {
        if (isset($params['bundle_option'])) {
            $bundleOption = $this->getBundleProductData($params, $product);
            return $bundleOption;
        }
    }

    /**
     * ProductIdByProductType
     *
     * @param  array $params
     * @return mixed
     */
    protected function productIdByProductType($params)
    {
        if (array_key_exists('selected_configurable_option', $params)
        && $params['selected_configurable_option'] != "") {
            return $params['selected_configurable_option'];
        } elseif (array_key_exists('super_attribute', $params) && $params['super_attribute'] != "") {
            $product = $this->_catalogProduct->create()->load($params['product']);
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
     * Validates quote's data added by customer.
     *
     * @param array $paramsData
     * @return bool
     */
    public function validateData(&$paramsData)
    {
        $errors = [];
        $data = [];
        foreach ($paramsData as $code => $value) {
            switch ($code) {
                case 'quote_qty':
                    if (!$this->intUtils->isValid($value)) {
                        $errors[] = __('Quote Quantity can contain only integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $paramsData[$code] = $value;
                    }
                    break;
                case 'quote_price':
                    if (!$this->floatUtils->isValid($value)) {
                        $errors[] = __('Quote Price can contain only decimal or integer value');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $paramsData[$code] = $value;
                    }
                    break;
                case 'quote_description':
                    if (trim($value) == '') {
                        $errors[] = __('Please enter the quote description');
                    } else {
                        $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
                        $paramsData[$code] = $value;
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * SelectedProductQty
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @param  array                          $params
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
        if (isset($params['links'])) {
            $quantity = $params['quote_qty'];
            return $quantity;
        }
        $quantity = $product->getQuantityAndStockStatus()['qty'];
        return $quantity;
    }
}
