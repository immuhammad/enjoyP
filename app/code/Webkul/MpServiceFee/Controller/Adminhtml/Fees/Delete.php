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

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

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
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     */
    public function __construct(
        Filter $filter,
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpServiceFee\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->serviceHelper = $serviceHelper;
        $this->attributeslistfactory = $attributeslistfactory;
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
        $isEnabled = $this->serviceHelper->isModuleEnable();
        if (!$isEnabled) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->getUrl('noroute'));
            return $resultRedirect;
        }

        try {
            $selectedServices = $this->filter->getCollection($this->attributeslistfactory->create()->getCollection());
            if ($selectedServices->getSize()) {
                foreach ($selectedServices as $services) {
                    $modelObject = $this->attributeslistfactory->create();
                    $data = $this->serviceHelper->loadData($modelObject, $services['entity_id']);
                    $this->serviceHelper->deleteData($data);
                }
                $this->messageManager->addSuccess(__('Service(s) Deleted Successfully'));
            } else {
                $this->messageManager->addError(__('No record selected'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Entries cannot be deleted. Something went wrong.');
            $this->logger->error($e->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->getUrl('servicefee/fees/attributeslist'));
        return $resultRedirect;
    }
}
