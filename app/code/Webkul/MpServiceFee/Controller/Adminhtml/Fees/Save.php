<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Controller\Adminhtml\Fees;

use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
    
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    
    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_MpServiceFee::createfees';

    /**
     * @var \Webkul\MpServiceFee\Helper\Servicehelper
     */
    protected $serviceHelper;

    /**
     * @var \Webkul\MpServiceFee\Model\AttributesListFactory
     */
    protected $attributeslistfactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     */
    public function __construct(
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpServiceFee\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->attributeslistfactory = $attributeslistfactory;
        $this->serviceHelper = $serviceHelper;
        $this->logger = $logger;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        if (!$this->serviceHelper->isModuleEnable()) {
            $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirectResult->setUrl($this->getUrl('noroute'));
            return $redirectResult;
        }

        try {
            $params = $this->getRequest()->getParams();
            $paramsVal = $this->getRequest()->getParam('servicefee_form_container');
            $serviceModel = $this->attributeslistfactory->create();
            if (isset($params["servicefee_form_container"]) && count($paramsVal) > 0) {
                $serviceModel->getCollection()->addFieldToFilter('seller_id', 0)->walk("delete");
                foreach ($paramsVal as $key => $params) {
                    # code...
                    $data = [
                        'service_status' => $params['service_status'],
                        'service_code' => $params['service_code'],
                        'service_title' => $params['service_title'],
                        'service_value' => $params['service_value'],
                        'service_type' => $params['service_type'],
                        'seller_id' => 0,
                        'position' => $params['position'],
                    ];

                    $this->serviceHelper->saveData($serviceModel, $data);
                }
            } else {
                $serviceModel->getCollection()->addFieldToFilter('seller_id', 0)->walk("delete");
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Entries cannot be save. Something went wrong.');
            $this->logger->error($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->getUrl('servicefee/fees/create'));
            return $resultRedirect;
        }
        $this->messageManager->addSuccess(__('Service Created Successfully'));
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('servicefee/fees/create'));
        return $resultRedirect;
    }
}
