<?php

namespace Webkul\Mpsplitorder\Plugin\Quote\Model\ValidationRules;

use Magento\Framework\Validation\ValidationResultFactory;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Validator\MinimumOrderAmount\ValidationMessage;

/**
 * @inheritdoc
 */

class MinimumAmountValidationRule
{
     /**
      * @var string
      */
    private $generalMessage;

    /**
     * @var ValidationMessage
     */
    private $amountValidationMessage;

    /**
     * @var ValidationResultFactory
     */
    private $validationResultFactory;
    
    /**
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @param ValidationMessage $amountValidationMessage
     * @param ValidationResultFactory $validationResultFactory
     * @param string $generalMessage
     */

    public function __construct(
        ValidationMessage $amountValidationMessage,
        ValidationResultFactory $validationResultFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        SessionManager $coreSession,
        string $generalMessage = ''
    ) {

        $this->amountValidationMessage = $amountValidationMessage;
        $this->validationResultFactory = $validationResultFactory;
        $this->generalMessage = $generalMessage;

        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
    }

    public function afterValidate(
        \Magento\Quote\Model\ValidationRules\MinimumAmountValidationRule $subject,
        $result
    ) {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote) {
            return $result;
        }

        $storeId = $quote->getStoreId();
        $splitorder_enable = $this->_scopeConfig->getValue(
            'marketplace/mpsplitorder/mpsplitorder_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $status = $this->_scopeConfig->getValue(
            'sales/minimum_order/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $amount = $this->_scopeConfig->getValue(
            'sales/minimum_order/amount',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        $grandtotal=$this->_coreSession->getData('grand_total');
 
      //  $this->logger->info(" data  :".json_encode($quote->getData()));

        if ($splitorder_enable && $status && $grandtotal>=$amount) {
            return [];
        } else {
            return $result;
        }
    }
}
