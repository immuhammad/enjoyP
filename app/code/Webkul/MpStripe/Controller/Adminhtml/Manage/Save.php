<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpStripe\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;

class Save extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Backend\Model\View\Result\Page
     */
    protected $resultPage;

    /**
     * @param Context $context
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param RemoteAddress $remoteAddress
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Filesystem\Driver\File $driver,
        RemoteAddress $remoteAddress,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->_coreSession = $coreSession;
        $this->driver = $driver;
        $this->dateTime = $dateTime;
        $this->remoteAddress = $remoteAddress;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Seller list page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $this->helper->setUpDefaultDetails();
        // process data
        $postRequest = $this->getRequest()->getParam('stripe_user');
        $request = $this->getRequest()->getParams();
        //get general data
        $wholeData = $this->getGeneralData($postRequest);
        
        try {
            if ($postRequest['account_id']) {
                $response = \Stripe\Account::update(
                    $postRequest['account_id'],
                    $wholeData
                );
                if ($response) {
    
                    $this->messageManager->addSuccess(__('You have successfully updated connected account on stripe'));
                    
                } else {
    
                    $this
                        ->messageManager
                        ->addError(
                            __('There some error, not able connect you with stripe, please contact admin')
                        );
                }
    
                return $this->resultRedirectFactory->create()->setPath(
                    'mpstripe/manage/account',
                    ['seller_id' => $postRequest['user_id'], '_secure' => $this->getRequest()->isSecure()]
                );
            }
            $response = \Stripe\Account::create($wholeData);

            $response['user_id'] = $postRequest['user_id'];
            $res = $this->helper->saveCustomStripeSeller($response);
            if ($res) {
                $this
                    ->messageManager
                    ->addNotice(
                        __('More Information Required, Please Connect To Stripe')
                    );
            } else {

                $this->_coreSession->setSellerCustomAccountData(null);
                $this->messageManager->addSuccess(__('You are successfully connected to stripe'));
            }
        } catch (\Exception $e) {
            $this->_coreSession->setSellerCustomAccountData($wholeData);
            $this
            ->messageManager
            ->addError(
                $e->getMessage()
            );
        }
        return $this->resultRedirectFactory->create()->setPath(
            'mpstripe/manage/account',
            ['seller_id' => $postRequest['user_id'], '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Generate general data
     *
     * @param array $postRequest
     * @return array
     */
    public function getGeneralData($postRequest)
    {
        if (empty($postRequest['account_id'])) {
            $defaultData["type"] = 'custom';
            $defaultData["country"] = $postRequest['country'];
            $defaultData["email"] = $postRequest['email'];
            $defaultData["external_account"] = [
                "object" => 'bank_account',
                "country" => $postRequest['external_accounts']['country'],
                "currency" => $postRequest['external_accounts']['currency'],
                "account_holder_name" => $postRequest['external_accounts']['account_holder_name'],
                "account_holder_type" => $postRequest['external_accounts']['account_holder_type'],
                "routing_number" => $postRequest['external_accounts']['routing_number'],
                "account_number" => $postRequest['external_accounts']['account_number']
            ];
        }
        
        $defaultData["requested_capabilities"] = ["card_payments", "transfers"];
        $defaultData["business_type"] = $postRequest['business_type'];

        $defaultData["tos_acceptance"] = [
            "date" => strtotime($this->dateTime->gmtDate()),
            "ip" => $this->remoteAddress->getRemoteAddress(),
            'service_agreement' => $postRequest['country'] === 'US' ? 'full' : 'recipient'
        ];
        return $defaultData;
    }

    /**
     * Check for is allowed.
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Marketplace::seller');
    }
}
