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
namespace Webkul\MpStripe\Controller\Seller\Connect;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Account extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\MpStripe\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param RemoteAddress $remoteAddress
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpStripe\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        RemoteAddress $remoteAddress,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->dateTime = $dateTime;
        $this->driver = $driver;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->remoteAddress = $remoteAddress;
        $this->customerUrl = $customerUrl;
        parent::__construct($context);
    }

    /**
     * Retrieve customer session object.
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getSession()
    {
        return $this->customerSession;
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
        $loginUrl =
        $this->customerUrl
        ->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Connect to stripe.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->marketplaceHelper->isSeller();
        if ($isPartner == 1) {
            $this->helper->setUpDefaultDetails();
            // process data
            $postRequest = $this->getRequest()->getParam('stripe_user');
            
            //get general data
            $wholdData = $this->getGeneralData($postRequest);
            
            try {
                $response = \Stripe\Account::create($wholdData);

                $response['user_id'] = $postRequest['user_id'];
                $res = $this->helper->saveCustomStripeSeller($response);
                if (!$res) {
                    $this
                        ->messageManager
                        ->addError(
                            __('There some error, not able connect you with stripe, please contact admin')
                        );
                } else {
                    $this->messageManager->addSuccess(__('You are successfully connected to stripe'));
                }
            } catch (\Exception $e) {
                $this
                ->messageManager
                ->addError(
                    $e->getMessage()
                );
            }
            return $this->resultRedirectFactory->create()->setPath(
                'mpstripe/seller/connect',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    /**
     * Manage general data for seller
     *
     * @param array $postRequest
     * @return array
     */
    public function getGeneralData($postRequest)
    {
        return [
            "type" => "custom",
            "country" => $postRequest['country'],
            "email" => $postRequest['email'],
            "requested_capabilities" => ["card_payments", "transfers"],
            "tos_acceptance" => [
                "date" => strtotime($this->dateTime->gmtDate()),
                "ip" => $this->remoteAddress->getRemoteAddress(),
                'service_agreement' => $postRequest['country'] === 'US' ? 'full' : 'recipient'
            ],
            "external_account" => [
                "object" => $postRequest['external_accounts']['object'],
                "country" => $postRequest['external_accounts']['country'],
                "currency" => $postRequest['external_accounts']['currency'],
                "account_holder_name" => $postRequest['external_accounts']['account_holder_name'],
                "account_holder_type" => $postRequest['external_accounts']['account_holder_type'],
                "routing_number" => $postRequest['external_accounts']['routing_number'],
                "account_number" => $postRequest['external_accounts']['account_number']
            ]
        ];
    }
}
