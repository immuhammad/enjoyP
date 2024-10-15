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

class MassDisable extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    
    /**
     * @var \Webkul\MpServiceFee\Helper\Servicehelper
     */
    protected $serviceHelper;
    
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\MpServiceFee\Model\AttributesListFactory
     */
    protected $attributeslistfactory;
    
        /**
         * @var Logger
         */
        protected $logger;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Filter $filter
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        Filter $filter,
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Webkul\MpServiceFee\Model\AttributesListFactory $attributeslistfactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\MpServiceFee\Logger\Logger $logger
    ) {
        parent::__construct($context);
        $this->attributeslistfactory = $attributeslistfactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->serviceHelper = $serviceHelper;
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
        if (!$this->serviceHelper->isModuleEnable()) {
            $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $redirectResult->setUrl($this->getUrl('noroute'));
            return $redirectResult;
        }

        try {
            $servicesSelected = $this->filter->getCollection($this->attributeslistfactory->create()->getCollection());
            if ($servicesSelected->getSize()) {
                foreach ($servicesSelected as $service) {
                    $modelObject = $this->attributeslistfactory->create();
                    $serviceDataRow = $this->serviceHelper->loadData($modelObject, $service['entity_id']);
                    $serviceDataRow->setServiceStatus(0);
                    $this->serviceHelper->saveData($serviceDataRow);
                }
                $this->messageManager->addSuccess(__('Service(s) Disabled Successfully'));
            } else {
                $this->messageManager->addError(__('No record selected'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError('Selected fees cannot be disable. Something went wrong.');
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
