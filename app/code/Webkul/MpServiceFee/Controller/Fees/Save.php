<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_MpServiceFee
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Controller\Fees;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Save extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var Webkul\MpGDPR\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelperData;
    /**
     * Class constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Session $customerSession
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $helper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Url $url
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelperData
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $serviceCollectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Session $customerSession,
        \Webkul\MpServiceFee\Helper\Servicehelper $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $marketplaceHelperData,
        \Webkul\MpServiceFee\Model\AttributesListFactory $serviceCollectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {

        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->coreRegistry = $coreRegistry;
        $this->url = $url;
        $this->marketplaceHelperData = $marketplaceHelperData;
        $this->serviceCollectionFactory = $serviceCollectionFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }
    /**
     * Execute the params
     *
     * @return void
     */
    public function execute()
    {
        $requestService = $this->getRequest()->getParams();
        if (isset($requestService["id"])) {
            $requestService["entity_id"] = $requestService["id"];
            $this->messageManager->addSuccess(__('Service fee updated successfully.'));
        } else {
            $this->messageManager->addSuccess(__('Service fee created successfully'));
        }
        $this->serviceCollectionFactory->create()->setData($requestService)->save();
        return $this->resultRedirectFactory->create()->setPath(
            'servicefee/fees/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}
