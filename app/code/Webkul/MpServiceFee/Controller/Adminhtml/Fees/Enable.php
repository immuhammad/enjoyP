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
use Magento\Ui\Component\MassAction\Filter;

class Enable extends \Magento\Backend\App\Action
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
     * @var Filter
     */
    protected $filter;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Filter $filter
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     */
    public function __construct(
        Filter $filter,
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Webkul\MpServiceFee\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->attributeslistfactory = $attributeslistfactory;
        $this->serviceHelper = $serviceHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        $this->logger = $logger;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $isModuleEnable = $this->serviceHelper->isModuleEnable();
        if (!$isModuleEnable) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->getUrl('noroute'));
            return $resultRedirect;
        }

        try {
            $selectedServiceFee = $this->filter->getCollection($this->attributeslistfactory->create()->getCollection());
            if ($selectedServiceFee->getSize()) {
                foreach ($selectedServiceFee as $services) {
                    $modelObject = $this->attributeslistfactory->create();
                    $dataRow = $this->serviceHelper->loadData($modelObject, $services['entity_id']);
                    $dataRow->setServiceStatus(1);
                    $this->serviceHelper->saveData($dataRow);
                }
                $this->messageManager->addSuccess(__('Service(s) Enabled Successfully'));
            } else {
                $this->messageManager->addError(__('No record selected'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Selected fees cannot be enable. Something went wrong.');
            $this->logger->error($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('servicefee/fees/attributeslist'));
        return $resultRedirect;
    }
}
