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

namespace Webkul\MpServiceFee\Controller\Fees;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassEnable extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
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
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Filter $filter,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory,
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
        $isModuleEnable = $this->serviceHelper->isModuleEnable();
        if (!$isModuleEnable) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->getUrl('noroute'));
            return $resultRedirect;
        }

        try {
            $selectedService = $this->filter->getCollection($this->attributeslistfactory->create()->getCollection());
            if ($selectedService->getSize()) {
                foreach ($selectedService as $services) {
                    $modelObject = $this->attributeslistfactory->create();
                    $serviceData = $this->serviceHelper->loadData($modelObject, $services['entity_id']);
                    $serviceData->setServiceStatus(1);
                    $this->serviceHelper->saveData($serviceData);
                }
                $this->messageManager->addSuccess(__('Service(s) Enable Successfully'));
            } else {
                $this->messageManager->addError(__('No record selected'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Selected fees cannot be enable. Something went wrong.');
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
