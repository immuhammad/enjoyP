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

namespace Webkul\Mpquotesystem\Helper;

use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

class Mail extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_EMAIL_NEW_QUOTE = 'mpquotesystem/email/new_quote';
    public const XML_PATH_EMAIL_QUOTE_STATUS = 'mpquotesystem/email/quote_status';
    public const XML_PATH_EMAIL_QUOTE_MESSAGE = 'mpquotesystem/email/quote_message';
    public const XML_PATH_EMAIL_QUOTE_EDIT = 'mpquotesystem/email/quote_edit';

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $_inlineTranslation;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var templateId
     */
    protected $_tempId;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @var \Webkul\Mpquotesystem\Model\Quotes
     */
    protected $_mpquote;

    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_mpproductCollection;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \Magento\Framework\Translate\Inline\StateInterface $_inlineTranslation
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Webkul\Mpquotesystem\Helper\Data                  $helper
     * @param \Magento\Framework\Mail\Template\TransportBuilder  $transportBuilder
     * @param \Magento\Framework\App\Helper\Context              $context
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory          $mpquote
     * @param CollectionFactory                                  $mpproductCollection
     * @param \Magento\Framework\Message\ManagerInterface        $messageManager
     */
    public function __construct(
        \Magento\Framework\Translate\Inline\StateInterface $_inlineTranslation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Mpquotesystem\Helper\Data $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquote,
        CollectionFactory $mpproductCollection,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->_inlineTranslation = $_inlineTranslation;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_transportBuilder = $transportBuilder;
        $this->_mpquote = $mpquote;
        $this->_mpproductCollection = $mpproductCollection;
        $this->_messageManager = $messageManager;
    }

    /**
     * Get seller id from product id from marketplace/product
     *
     * @param int $productId
     * @return int
     */
    public function getSellerId($productId)
    {
        $mpProductCollection = $this->_mpproductCollection->create()
            ->addFieldToFilter(
                'mageproduct_id',
                $productId
            );
        $sellerId = 0;
        if (!empty($mpProductCollection)) {
            foreach ($mpProductCollection as $product) {
                $sellerId = $product->getSellerId();
            }
        }

        return $sellerId;
    }
    
    /**
     * Send Mail to customer, seller and admin when new quote is processed
     *
     * @param int    $quoteId
     * @param object $product
     * @return void
     */
    public function newQuote($quoteId, $product)
    {
        try {
            $quote = $this->_mpquote->create()->load($quoteId);

            $sellerId = $this->getSellerId($quote->getProductId());

            // seller details
            if ($sellerId) {
                $seller = $this->_helper->getCustomerData($sellerId);
                $sellerinfo = [];
                $sellerinfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
            }
            // admin details
            $admininfo = [];
            $admininfo = [
                'name' => 'Admin',
                'email' => $this->_helper->getDefaultTransEmailId(),
            ];

            $customer = $this->_helper->getCustomerData($quote->getCustomerId());
            $customerinfo = [];
            $customerinfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $sku = $product->getTypeInstance()->getSku($product);
            // Product Options
            $optionAndPrice = $this->_helper->getOptionNPrice($product, $quote);
            $optionPriceArr = explode('~|~', $optionAndPrice);
            $productName = "<tbody><tr><td class='item-info'>";
            if ($this->_helper->checkProductCanShowOrNot($product)) {
                $productName .= "<a href='".$product->getProductUrl()."'>".$quote->getProductName().
                "</a>";
            } else {
                $productName .= $quote->getProductName();
            }
            $productName .= "<dl class='item-options'>".$optionPriceArr[0]."</dl>".
                "</td><td class='item-info'>".$sku.
                '</td><td class="item-info">'.
                $this->_helper->getformattedPrice($quote->getProductPrice()).'</td></tr></tbody>';
            // Email variables
            $templateVariable = [];
            $templateVariable['quote_id'] = $quoteId;
            $templateVariable['product_name'] = $productName;
            $templateVariable['quote_qty'] = $quote->getQuoteQty();
            $templateVariable['quote_price'] = $this->_helper
                ->getformattedPrice($quote->getQuotePrice());
            $templateVariable['store'] = $this->getStore();
            $templateVariable['quote_description'] = $quote->getQuoteDesc();
            $templateVariable['receiver_name'] = $customerinfo['name'];
            $templateVariable['title'] = __('Thanks for your quote, will contact you soon.');
            $senderInfo = [];
            if ($sellerId) {
                $senderInfo = $sellerinfo;
            } else {
                $senderInfo = $admininfo;
            }
            // mail template
            // Send mail to customer
            $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_NEW_QUOTE);
            $this->_inlineTranslation->suspend();
            $this->generateTemplate(
                $templateVariable,
                $senderInfo,
                $customerinfo
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
            //send Mail to seller
            if ($sellerId) {
                $templateVariable['receiver_name'] = $sellerinfo['name'];
                $templateVariable['title'] = __('New Quote has been created Please Check.');
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $sellerinfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            }
            // send Mail to admin
            $templateVariable['receiver_name'] = $admininfo['name'];
            $templateVariable['title'] = __('New Quote has been created Please Check.');
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $admininfo
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    /**
     * QuoteStatusMail
     *
     * @param int     $quoteId
     * @param string  $message
     * @param boolean $flag
     * @return void
     */
    public function quoteStatusMail($quoteId, $message, $flag)
    {
        try {
            $quote = $this->_mpquote->create()->load($quoteId);
            $sellerId = $this->getSellerId($quote->getProductId());

            if ($sellerId) {
                $seller = $this->_helper->getCustomerData($sellerId);
                $sellerinfo = [];
                $sellerinfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
            }
            // admin details
            $admininfo = [];
            $admininfo = [
                'name' => 'Admin',
                'email' => $this->_helper->getDefaultTransEmailId(),
            ];

            $customer = $this->_helper->getCustomerData($quote->getCustomerId());
            $customerinfo = [];
            $customerinfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            //status of Quote
            $status = '';
            if ($quote->getStatus() == \Webkul\Mpquotesystem\Model\Quotes::STATUS_UNAPPROVED) {
                $status = 'UnApproved';
            }
            if ($quote->getStatus() == \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED) {
                $status = 'Approved';
            }
            if ($quote->getStatus() == \Webkul\Mpquotesystem\Model\Quotes::STATUS_DECLINE) {
                $status = 'Declined';
            }

            $templateVariable['new_status'] = $status;
            $templateVariable['new_message'] = $message;
            $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_STATUS);
            $this->_inlineTranslation->suspend();
            if ($flag == 'admin') {
                $templateVariable['receiver_name'] = $customerinfo['name'];
                $templateVariable['title'] = __('Your Quote Status has been changed now.');
                // mail template
                // Send mail to customer
                $this->generateTemplate(
                    $templateVariable,
                    $admininfo,
                    $customerinfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();

                if ($sellerId) {
                    $templateVariable['receiver_name'] = $sellerinfo['name'];
                    $templateVariable['title'] = __('Quote Status has been changed now.');
                    $this->generateTemplate(
                        $templateVariable,
                        $admininfo,
                        $sellerinfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
                }

                $templateVariable['receiver_name'] = $admininfo['name'];
                $templateVariable['title'] = __('Quote Status changed successfully.');
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $admininfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            } else {
                if ($sellerId) {
                    // send mail to customer by seller
                    $templateVariable['receiver_name'] = $customerinfo['name'];
                    $templateVariable['title'] = __('Your Quote Status has been changed now.');
                    $this->generateTemplate(
                        $templateVariable,
                        $sellerinfo,
                        $customerinfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
                    //send mail to admin by seller
                    $templateVariable['receiver_name'] = $admininfo['name'];
                    $templateVariable['title'] = __('Quote Status has been changed now.');
                    $this->generateTemplate(
                        $templateVariable,
                        $sellerinfo,
                        $admininfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    /**
     * QuoteMessage
     *
     * @param int     $quoteId
     * @param string  $message
     * @param boolean $flag
     * @return void
     */
    public function quoteMessage($quoteId, $message, $flag)
    {
        try {
            $quote = $this->_mpquote->create()->load($quoteId);
            $sellerId = $this->getSellerId($quote->getProductId());

            if ($sellerId) {
                $seller = $this->_helper->getCustomerData($sellerId);
                $sellerinfo = [];
                $sellerinfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
            }
            // admin details
            $admininfo = [];
            $admininfo = [
                'name' => 'Admin',
                'email' => $this->_helper->getDefaultTransEmailId(),
            ];

            $customer = $this->_helper->getCustomerData($quote->getCustomerId());
            $customerinfo = [];
            $customerinfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];

            $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_MESSAGE);
            $this->_inlineTranslation->suspend();

            $templateVariable = [];
            $templateVariable['new_message'] = $message;

            if ($flag == 'customer') {
                $templateVariable['receiver_name'] = $customerinfo['name'];
                $templateVariable['title'] = __('Your Message send successfully.');
                if ($sellerId) {
                    $this->generateTemplate(
                        $templateVariable,
                        $sellerinfo,
                        $customerinfo
                    );
                } else {
                    $this->generateTemplate(
                        $templateVariable,
                        $admininfo,
                        $customerinfo
                    );
                }
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                if ($sellerId) {
                    //Mail To Seller
                    $templateVariable['receiver_name'] = $sellerinfo['name'];
                    $templateVariable['title'] = __(
                        'New Message has been appended to quote id : %1',
                        $quoteId
                    );
                    $this->generateTemplate(
                        $templateVariable,
                        $customerinfo,
                        $sellerinfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
                }
                //Mail To Admin
                //
                $templateVariable['receiver_name'] = $admininfo['name'];
                $templateVariable['title'] = __(
                    'New Message has been appended to quote id : %1',
                    $quoteId
                );
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $admininfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            } elseif ($flag == 'admin') {
                //Mail To Customer
                $templateVariable['receiver_name'] = $customerinfo['name'];
                $templateVariable['title'] = __('New Message has been appended to quote id : %1', $quoteId);
                $this->generateTemplate(
                    $templateVariable,
                    $admininfo,
                    $customerinfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();

                if ($sellerId) {
                    //Mail To Seller

                    $templateVariable['receiver_name'] = $sellerinfo['name'];
                    $templateVariable['title'] = __('New Message has been appended to quote id : %1', $quoteId);
                    $this->generateTemplate(
                        $templateVariable,
                        $admininfo,
                        $sellerinfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
                }
                //Mail To Admin
                $templateVariable['receiver_name'] = $admininfo['name'];
                $templateVariable['title'] = __('Your Message send successfully.');
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $admininfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            } else {
                //Mail To Customer
                $templateVariable['receiver_name'] = $customerinfo['name'];
                $templateVariable['title'] = __('New Message has been appended to quote id : %1', $quoteId);
                $this->generateTemplate(
                    $templateVariable,
                    $sellerinfo,
                    $customerinfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();

                //Mail To Seller

                $templateVariable['receiver_name'] = $sellerinfo['name'];
                $templateVariable['title'] = __('New Message has been appended to quote id : %1', $quoteId);
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $sellerinfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();

                //Mail To Admin
                $templateVariable['receiver_name'] = $admininfo['name'];
                $templateVariable['title'] = __('New Message has been appended to quote id : %1', $quoteId);
                $this->generateTemplate(
                    $templateVariable,
                    $sellerinfo,
                    $admininfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    /**
     * On Quote edited by buyer
     *
     * @param int    $quoteId
     * @param string $message
     */
    public function quoteEdited($quoteId, $message)
    {
        try {
            $quote = $this->_mpquote->create()->load($quoteId);
            $sellerId = $this->getSellerId($quote->getProductId());

            if ($sellerId) {
                $seller = $this->_helper->getCustomerData($sellerId);
                $sellerinfo = [];
                $sellerinfo = [
                    'name' => $seller->getName(),
                    'email' => $seller->getEmail(),
                ];
            }
            // admin details
            $admininfo = [];
            $admininfo = [
                'name' => 'Admin',
                'email' => $this->_helper->getDefaultTransEmailId(),
            ];

            $customer = $this->_helper->getCustomerData($quote->getCustomerId());
            $customerinfo = [];
            $customerinfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];

            $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_EDIT);
            $this->_inlineTranslation->suspend();

            $templateVariable = [];
            $product = $this->_helper->getProduct($quote->getProductId());
            // Product Options
            $optionAndPrice = $this->_helper->getOptionNPrice($product, $quote);
            $optionPriceArr = explode('~|~', $optionAndPrice);
            $productName = "<tbody><tr><td class='item-info'>";
            if ($this->_helper->checkProductCanShowOrNot($product)) {
                $productName .= "<a href='".$product->getProductUrl()."'>".$quote->getProductName().
                "</a>";
            } else {
                $productName .= $quote->getProductName();
            }
            $productName .= "<dl class='item-options'>".$optionPriceArr[0]."</dl>".
                "</td><td class='item-info'>".$product->getSku().
                '</td><td class="item-info">'.
                $this->_helper->getformattedPrice($quote->getProductPrice()).'</td></tr></tbody>';
            $templateVariable["quote_id"] = $quoteId;
            $templateVariable["product_name"] = $productName;
            $templateVariable["new_quote_qty"] = $quote->getQuoteQty();
            $templateVariable["new_quote_price"] = $this->_helper->getformattedPrice($quote->getQuotePrice());
            $templateVariable["new_message"] = $message;
            $templateVariable["edit_by"] = 'customer';
            //Mail To Customer
            $templateVariable["receiver_name"] = $customerinfo['name'];
            $templateVariable["title"] = __("You just Edited your previous quote, details are below.");
            if ($sellerId) {
                $this->generateTemplate(
                    $templateVariable,
                    $sellerinfo,
                    $customerinfo
                );
            } else {
                $this->generateTemplate(
                    $templateVariable,
                    $admininfo,
                    $customerinfo
                );
            }
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
            if ($sellerId) {
                //Mail To Seller
                $templateVariable["receiver_name"] = $sellerinfo['name'];
                $templateVariable["title"] = __("Customer just Edited his/her quote, details are below.");
                $this->generateTemplate(
                    $templateVariable,
                    $customerinfo,
                    $sellerinfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
            }
            //Mail To Admin
            $templateVariable["receiver_name"] = $admininfo['name'];
            $templateVariable["title"] = __("Customer just Edited his/her quote, details are below.");
            $this->generateTemplate(
                $templateVariable,
                $customerinfo,
                $admininfo
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    /**
     * Generate template
     *
     * @param array $emailTemplateVariables
     * @param array $senderInfo
     * @param array $receiverInfo
     * @return void
     */
    protected function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template = $this->_transportBuilder->setTemplateIdentifier($this->_tempId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->_storeManager->getStore()->getId(),
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo(
                $receiverInfo['email'],
                $receiverInfo['name']
            );
        return $this;
    }

    /**
     * QuoteEditedByAdmin
     *
     * @param int     $quoteId
     * @param string  $message
     * @param boolean $flag
     * @return void
     */
    public function quoteEditedByAdmin($quoteId, $message, $flag)
    {
        $quote = $this->_mpquote->create()->load($quoteId);
        // admin details
        $admininfo = [];
        $admininfo = [
            'name' => 'Admin',
            'email' => $this->_helper->getDefaultTransEmailId(),
        ];

        $customer = $this->_helper->getCustomerData($quote->getCustomerId());
        $customerinfo = [];
        $customerinfo = [
            'name' => $customer->getName(),
            'email' => $customer->getEmail(),
        ];
        $sellerinfo = [];
        $sellerId = $this->getSellerId($quote->getProductId());
        if ($sellerId) {
            $seller = $this->_helper->getCustomerData($sellerId);
            $sellerinfo = [
                'name' => $seller->getName(),
                'email' => $seller->getEmail(),
            ];
        }

        $this->_tempId = $this->getTemplateId(self::XML_PATH_EMAIL_QUOTE_EDIT);
        $this->_inlineTranslation->suspend();

        $templateVariable = [];
        $product = $this->_helper->getProduct($quote->getProductId());
        // Product Options
        $optionAndPrice = $this->_helper->getOptionNPrice($product, $quote);
        $optionPriceArr = explode('~|~', $optionAndPrice);
        $productName = '<tbody><tr>'.
            "<td class='item-info'>";
        if ($this->_helper->checkProductCanShowOrNot($product)) {
            $productName .= "<a href='".$product->getProductUrl()."'>".$quote->getProductName().
            "</a>";
        } else {
            $productName .= $quote->getProductName();
        }
        $productName .= "<dl class='item-options'>".$optionPriceArr[0]."</dl>".
            "</td><td class='item-info'>".$product->getSku().
            '</td><td class="item-info">'.
            $this->_helper->getformattedPrice($quote->getProductPrice()).'</td></tr></tbody>';
        $templateVariable["quote_id"] = $quoteId;
        $templateVariable["product_name"] = $productName;
        $templateVariable["new_quote_qty"] = $quote->getQuoteQty();
        $templateVariable["new_quote_price"] = $this->_helper->getformattedPrice($quote->getQuotePrice());
        $templateVariable["new_message"] = $message;
        $templateVariable["edit_by"] = $flag;
        try {
            if ($flag=='seller') {
                if ($sellerId) {
                    //Mail To Customer
                    $templateVariable["receiver_name"] = $customerinfo['name'];
                    $templateVariable["title"] = __(
                        "%1 have just Edited a quote, details are below.",
                        $sellerinfo['name']
                    );
                    $this->generateTemplate(
                        $templateVariable,
                        $sellerinfo,
                        $customerinfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();

                    $templateVariable["receiver_name"] = $admininfo['name'];
                    $this->generateTemplate(
                        $templateVariable,
                        $sellerinfo,
                        $admininfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
                    if ($sellerId) {
                        $templateVariable["receiver_name"] = $sellerinfo['name'];
                        $templateVariable["title"] = __("You have just Edited a quote, details are below.");
                        $this->generateTemplate(
                            $templateVariable,
                            $admininfo,
                            $sellerinfo
                        );
                        $transport = $this->_transportBuilder->getTransport();
                        $transport->sendMessage();
                        $this->_inlineTranslation->resume();
                    }
                }
            } else {
                //Mail To Customer
                $templateVariable["receiver_name"] = $customerinfo['name'];
                $templateVariable["title"] = __("%1 have just Edited a quote, details are below.", $admininfo['name']);
                $this->generateTemplate(
                    $templateVariable,
                    $admininfo,
                    $customerinfo
                );
                $transport = $this->_transportBuilder->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();
                if ($sellerId) {
                    $this->generateTemplate(
                        $templateVariable,
                        $admininfo,
                        $sellerinfo
                    );
                    $transport = $this->_transportBuilder->getTransport();
                    $transport->sendMessage();
                    $this->_inlineTranslation->resume();
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError(__($e->getMessage()));
        }
        $this->_inlineTranslation->resume();
    }
    
    /**
     * GetTemplateId
     *
     * @param string $xmlPath
     * @return string
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * GetConfigValue
     *
     * @param string $path
     * @param int    $storeId
     * @return void
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    
    /**
     * GetStore
     *
     * @return object
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }
}
