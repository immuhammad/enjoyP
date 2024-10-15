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

class SaveEdit extends \Magento\Backend\App\Action
{
    
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\MpServiceFee\Helper\Servicehelper
     */
    protected $serviceHelper;

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Webkul_MpServiceFee::createfees';

    /**
     * @var \Webkul\MpServiceFee\Model\AttributesListFactory
     */
    protected $attributeslistfactory;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpServiceFee\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->attributeslistfactory = $attributeslistfactory;
        $this->serviceHelper = $serviceHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $isEnabled = $this->serviceHelper->isModuleEnable();
        if (!$isEnabled) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->getUrl('noroute'));
            return $resultRedirect;
        }

        try {
            $params = $this->getRequest()->getPost();
            $data = [
            'service_status' => $params['service_status'],
            'service_code' => $params['service_code'],
            'service_title' => $params['service_title'],
            'service_value' => $params['service_value'],
            'service_type' => $params['service_type'],
            'seller_id' => isset($params['seller_id']) && $params['seller_id'] != null? $params['seller_id']:0
            ];
            $serviceModel = $this->attributeslistfactory->create();
            if (isset($params['entity_id'])) {
                $dataRow = $serviceModel->load($params['entity_id']);
                $dataRow->setData($data);
                $dataRow->setEntityId($params['entity_id']);
                $dataRow->save();
                $this->messageManager->addSuccess(__('Service Updated Successfully'));
            } else {
                $isAvailable = $serviceModel->getCollection()
                                            ->addFieldToFilter('service_code', ['eq' => $data['service_code']]);
                if (!$isAvailable->getSize()) {
                    $dataRow = $this->attributeslistfactory->create();
                    $dataRow->addData($data);
                    $dataRow->save();
                    $this->messageManager->addSuccess(__('Service Created Successfully'));
                } else {
                    $this->messageManager->addError(__('Service code already exists'));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Entries cannot be save. Something went wrong.');
            $this->logger->error($e->getMessage());
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('servicefee/fees/attributeslist'));
        return $resultRedirect;
    }
}
