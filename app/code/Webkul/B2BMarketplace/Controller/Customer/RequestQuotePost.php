<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use Webkul\B2BMarketplace\Helper\Data as HelperData;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

/**
 * Webkul B2BMarketplace Customer RequestQuotePost Controller.
 */
class RequestQuotePost extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var HelperData
     */
    private $helperData;

    /**
     * @var QuoteManagementInterface
     */
    private $b2bQuoteManagement;

    /**
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param HelperData       $helperData
     * @param QuoteManagementInterface $b2bQuoteManagement
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        HelperData $helperData,
        QuoteManagementInterface $b2bQuoteManagement
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->helperData = $helperData;
        $this->b2bQuoteManagement = $b2bQuoteManagement;
        parent::__construct(
            $context
        );
    }

    /**
     * Retrieve customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    private function _getSession()
    {
        return $this->customerSession;
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->_getSession()->authenticate()) {
            $this->_actionFlag->set('', 'no-dispatch', true);
        }
        return parent::dispatch($request);
    }

    /**
     * RequestQuotePost action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        /**
         * @var \Magento\Framework\Controller\Result\Redirect
         */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->isPost()) {
            $itemIds = [];
            try {
                $params = $this->getRequest()->getParams();

                if (!$this->formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'b2bmarketplace/customer/requestQuote',
                        [
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    );
                }
                if (!isset($params['rfq_product']) || !count($params['rfq_product'])) {
                    $this->messageManager->addError(
                        __('Please select product to quote.')
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        'b2bmarketplace/customer/requestQuote',
                        [
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    );
                }

                $customerId = $this->helperData->getCustomerId();
                $params["customer_id"] = $customerId;
                $params["is_buying_lead"] = 1;
                $params['supplier_id'] = '';
                $params["created_at"] = date("Y-m-d h:i:sa");
                $params["updated_at"] = date("Y-m-d h:i:sa");

                $this->b2bQuoteManagement->saveQuote($params);

                $this->messageManager->addSuccess(__('Request for quote is successfully raised.'));
                return $this->resultRedirectFactory->create()->setPath(
                    'b2bmarketplace/customer/quotes',
                    [
                        '_secure' => $this->getRequest()->isSecure()
                    ]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            return $this->resultRedirectFactory->create()->setPath(
                'b2bmarketplace/customer/requestquote',
                [
                    '_secure' => $this->getRequest()->isSecure()
                ]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'b2bmarketplace/customer/requestquote',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
