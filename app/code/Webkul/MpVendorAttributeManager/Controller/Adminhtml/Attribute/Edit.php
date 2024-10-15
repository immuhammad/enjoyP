<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Registry;
use Magento\Eav\Model\Entity;
use Magento\Customer\Model\AttributeMetadataDataProviderFactory;
use Magento\Customer\Model\AttributeFactory;
use Magento\Backend\Model\SessionFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;
use Magento\Customer\Model\Customer;

class Edit extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $eavEntity;

    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProviderFactory
     */
    protected $_attributeMetaData;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Magento\Backend\Model\SessionFactory
     */
    protected $backendSessionFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $vendorAttributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $vendorAssignGroupFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Registry $registry
     * @param Entity $eavEntity
     * @param AttributeMetadataDataProviderFactory $attributeMetaData
     * @param AttributeFactory $attributeFactory
     * @param SessionFactory $backendSessionFactory
     * @param VendorAttributeFactory $vendorAttributeFactory
     * @param VendorAssignGroupFactory $vendorAssignGroupFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        Entity $eavEntity,
        AttributeMetadataDataProviderFactory $attributeMetaData,
        AttributeFactory $attributeFactory,
        SessionFactory $backendSessionFactory,
        VendorAttributeFactory $vendorAttributeFactory,
        VendorAssignGroupFactory $vendorAssignGroupFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->eavEntity = $eavEntity;
        $this->_attributeMetaData = $attributeMetaData;
        $this->_attributeFactory = $attributeFactory;
        $this->backendSessionFactory = $backendSessionFactory;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
        parent::__construct($context);
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::index');
    }

    /**
     * Init actions.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpVendorAttributeManager::index')
            ->addBreadcrumb(__('Vendorattribute'), __('Vendorattribute'))
            ->addBreadcrumb(__('Manage Vendor Attribute'), __('Manage Vendor Attribute'));

        return $resultPage;
    }

    /**
     * Dispatch request.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_entityTypeId = $this->eavEntity->setType(Customer::ENTITY)->getTypeId();

        return parent::dispatch($request);
    }

    /**
     * Edit Custom fields page.
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $assignedGroups = [];
        $id = $this->getRequest()->getParam('id');

        $attributeCode = $this->getRequest()->getParam('attribute_code');

        $vendorAttributeModel = $this->vendorAttributeFactory->create();
        $attributeModel = $this->_attributeFactory->create()->setEntityTypeId($this->_entityTypeId);

        $class = '';
        if ($id) {
            $vendorAttributeModel->load($id);
            $attributeId = $this->_attributeMetaData->create()
                                ->getAttribute('customer', $attributeCode)
                                ->getAttributeId();

            $attributeModel->load($vendorAttributeModel->getAttributeId());

            $class = $attributeModel->getFrontendClass() ?? '';
            $attributeModel->setIsVisible($vendorAttributeModel->getStatus());
            if (!$attributeModel->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/vendorattribute/index');
            }

            // entity type check
            if ($attributeModel->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/vendorattribute/index');
            }

            $requiredCheck = $attributeModel->getFrontendClass() ?? '';
            $require = explode(' ', $requiredCheck);
            if (in_array('required', $require)) {
                $attributeModel->setIsRequired(1);
                $attributeModel->setFrontendClass($require[0]);
            }
            /*
                If attribute assigned to groups
            */
            $vendorAssignGroupCollection = $this->vendorAssignGroupFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('attribute_id', ['eq' =>
                                                $vendorAttributeModel->getAttributeId()]);
            $assignedGroups = $vendorAssignGroupCollection->getColumnValues('group_id');
        }

        // set entered data if was error when we do save
        $formData = $this->backendSessionFactory->create()->getAttributeData(true);
        if (!empty($formData)) {
            $attributeModel->addData($formData);
        }
        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $id === null) {
            $attributeModel->addData($attributeData);
        }
        $attributeModel->setFrontendClass($class);
        $this->_coreRegistry->register('entity_attribute', $attributeModel);
        $this->_coreRegistry->register('attribute_group', implode(',', $assignedGroups));
        $this->_coreRegistry->register('attribute_used_for', $vendorAttributeModel->getAttributeUsedFor());
        $this->_coreRegistry->register('wk_attribute_status', $vendorAttributeModel->getWkAttributeStatus());
        
        $item = $id ? __('Edit Vendor Attribute') : __('New Vendor Attribute');

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($id ? $attributeModel->getName() : __('New Attribute'));
        return $resultPage;
    }
}
