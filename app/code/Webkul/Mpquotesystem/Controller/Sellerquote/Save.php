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

namespace Webkul\Mpquotesystem\Controller\Sellerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var $utilsFactory
     */
    protected $utilsFactory;

    /**
     * @param Context $context
     * @param PageFactory $_resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Url $urlModel
     * @param \Webkul\Mpquotesystem\Helper\Data $helper
     * @param \Magento\Framework\Validator\IntUtilsFactory $utilsFactory
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Url $urlModel,
        \Webkul\Mpquotesystem\Helper\Data $helper,
        \Magento\Framework\Validator\IntUtilsFactory $utilsFactory
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        $this->_urlModel = $urlModel;
        $this->_customerSession = $customerSession;
        $this->helper = $helper;
        $this->utilsFactory = $utilsFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
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
     * MpAmazonConnector Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!is_array($params)) {
            $this->messageManager
                ->addError(__("Sorry!! Data can't be saved"));
            return $resultRedirect->setPath('*/*');
        }
        $validationResponse = $this->validateData($params);
        if (!$validationResponse['error']) {
            $data = [
                'seller_id'     => $this->helper->getCustomerId(),
                'categories'    => implode(',', $params['product']['category_ids']),
                'min_qty'       => $params['product']['min_quote_qty']
            ];
            $this->saveData($data);
            $this->messageManager->addSuccess(__('Saved quote settings data.'));
        } else {
            $this->messageManager->addError($validationResponse['msg']);
        }
        return $resultRedirect->setPath('*/*');
    }

    /**
     * Save the data
     *
     * @param array $data
     *
     * @return void
     */
    public function saveData($data)
    {
        try {
            $sellerId = $this->helper->getCustomerId();
            $collection = $this->helper->getQuoteconfig()
                ->getCollection()
                ->addFieldToFilter('seller_id', $sellerId)
                ->setPageSize(1)
                ->getFirstItem();
            if ($collection->getId()) {
                $this->helper->getQuoteconfig()
                    ->addData($data)
                    ->setId($collection->getId())
                    ->save();
            } else {
                $this->helper->getQuoteconfig()->setData($data)->save();
            }
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'Can not save the quote config data'
                )
            );
        }
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
        $msg = '';
        if (!isset($params['product']['category_ids'])) {
            $error = 1;
            $msg = __('Please select quote category');
        } else {
            $validator = $this->utilsFactory->create();
            if ($params['product']['min_quote_qty'] && !$validator->isValid($params["product"]['min_quote_qty'])) {
                $error = 1;
                $msg = __('Format of min qty is not correct');
            }
        }
        return [
            'error' => $error,
            'msg' => $msg
        ];
    }
}
