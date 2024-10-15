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
namespace Webkul\MpVendorAttributeManager\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\Registry;
use Magento\Backend\Model\SessionFactory;
use Magento\Customer\Model\AttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorGroupFactory;
use Magento\Customer\Model\Customer;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $entityFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Backend\Model\SessionFactory
     */
    protected $backendSessionFactory;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory
     */
    protected $vendorGroupFactory;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EntityFactory $entityFactory
     * @param Registry $coreRegistry
     * @param SessionFactory $backendSessionFactory
     * @param AttributeFactory $attributeFactory
     * @param VendorGroupFactory $vendorGroupFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EntityFactory $entityFactory,
        Registry $coreRegistry,
        SessionFactory $backendSessionFactory,
        AttributeFactory $attributeFactory,
        VendorGroupFactory $vendorGroupFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->entityFactory = $entityFactory;
        $this->coreRegistry = $coreRegistry;
        $this->backendSessionFactory = $backendSessionFactory;
        $this->attributeFactory = $attributeFactory;
        $this->vendorGroupFactory = $vendorGroupFactory;
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::group');
    }

    /**
     * Init actions.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_MpVendorAttributeManager::group')
                ->addBreadcrumb(__('Vendorgroup'), __('Vendorgroup'))
                ->addBreadcrumb(__('Manage Vendor Group'), __('Manage Vendor Group'));

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
        $this->_entityTypeId = $this->entityFactory->create()
                                    ->setType(Customer::ENTITY)
                                    ->getTypeId();

        return parent::dispatch($request);
    }

    /**
     * Edit Vendor Group Form Action
     *
     * @return Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $vendorGroupId = $this->getRequest()->getParam('id');

        $vendorGroupModel = $this->vendorGroupFactory->create();
        if ($vendorGroupId) {
            $vendorGroupModel->load($vendorGroupId);
            if (!$vendorGroupModel->getId()) {
                $this->messageManager->addError(__('This group no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/index');
            }
        }
        
        $formData = $this->backendSessionFactory->create()->getAttributeData(true);
        if (!empty($formData)) {
            $vendorGroupModel->addData($formData);
        }
        $this->coreRegistry->register('vendor_group', $vendorGroupModel);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(
            $vendorGroupId ? $vendorGroupModel->getGroupName() : __('New Group')
        );

        $block = \Webkul\MpVendorAttributeManager\Block\Adminhtml\Group\Edit::class;
        $content = $resultPage->getLayout()->createBlock($block);
        $resultPage->addContent($content);

        $block = \Webkul\MpVendorAttributeManager\Block\Adminhtml\Group\Edit\Tabs::class;
        $left = $resultPage->getLayout()->createBlock($block);
        $resultPage->addLeft($left);
        return $resultPage;
    }
}
