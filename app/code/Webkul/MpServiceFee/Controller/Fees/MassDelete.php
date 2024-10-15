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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
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
     * Class constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Filter $filter
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Filter $filter,
        \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
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
        $isServiceModuleEnable = $this->serviceHelper->isModuleEnable();
        if (!$isServiceModuleEnable) {
            $resultFactoryRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultFactoryRedirect->setUrl($this->getUrl('noroute'));
            return $resultFactoryRedirect;
        }

        try {
            $selectedServices = $this->filter->getCollection($this->attributeslistfactory->create()->getCollection());
            if ($selectedServices->getSize()) {
                foreach ($selectedServices as $services) {
                    $modelObject = $this->attributeslistfactory->create();
                    $serviceData = $this->serviceHelper->loadData($modelObject, $services['entity_id']);
                    $this->serviceHelper->deleteData($serviceData);
                }
                $this->messageManager->addSuccess(__('Service(s) Deleted Successfully'));
            } else {
                $this->messageManager->addError(__('No record selected'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Entries cannot be deleted. Something went wrong.');
            $this->logger->error($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            'servicefee/fees/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Create CSRF validation
     *
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }
    
    /**
     * Validate CSRF
     *
     * @param RequestInterface $request
     * @return boolean|null
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
