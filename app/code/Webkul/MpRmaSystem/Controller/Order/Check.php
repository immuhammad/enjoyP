<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Controller\Order;

use Magento\Framework\App\Action\Context;

class Check extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @return void
     */
    public function __construct(
        Context $context,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        $this->mpRmaHelper       = $mpRmaHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Order check Action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $error = false;
        $helper = $this->mpRmaHelper;
        $data = $this->_request->getParams();
        if (array_key_exists("is_guest", $data)) {
            $isGuest = $data['is_guest'];
            if ($isGuest == 1) {
                if (!$helper->isGuestLoggedIn()) {
                    $error= true;
                }
            } else {
                if (!$helper->isLoggedIn()) {
                    $error= true;
                }
            }

            $info = ['error' => 0];
            if (!$error) {
                $info['isLoggedIn'] = 1;
                $itemId = $data['item_id'];
                $orderId = $data['order_id'];
                $qty = $data['qty'];
                if (!$helper->isRmaAllowed($itemId, $orderId, $qty)) {
                    $info['error'] = 1;
                }
            } else {
                $info['isLoggedIn'] = 0;
            }
        } else {
            $info['isLoggedIn'] = 0;
        }

        $result = $this->resultJsonFactory->create();
        $result->setData($info);
        return $result;
    }
}
