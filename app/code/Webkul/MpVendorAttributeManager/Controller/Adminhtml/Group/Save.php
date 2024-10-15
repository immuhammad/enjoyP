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
use Magento\Backend\Model\SessionFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorGroupFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;

class Save extends Action
{
    /**
     * @var /Magento\Backend\Model\SessionFactory
     */
    protected $backendSessionFactory;

    /**
     * @var /Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $vendorAttributeFactory;

    /**
     * @var /Webkul\MpVendorAttributeManager\Model\VendorGroupFactory
     */
    protected $vendorGroupFactory;

    /**
     * @var /Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $vendorAssignGroupFactory;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $jsonDecoder;

    /**
     * Constructor
     *
     * @param Context $context
     * @param SessionFactory $backendSessionFactory
     * @param VendorAttributeFactory $vendorAttributeFactory
     * @param VendorGroupFactory $vendorGroupFactory
     * @param VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     */
    public function __construct(
        Context $context,
        SessionFactory $backendSessionFactory,
        VendorAttributeFactory $vendorAttributeFactory,
        VendorGroupFactory $vendorGroupFactory,
        VendorAssignGroupFactory $vendorAssignGroupFactory,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        parent::__construct($context);
        $this->backendSessionFactory = $backendSessionFactory;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorGroupFactory = $vendorGroupFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->jsonDecoder = $jsonDecoder;
    }

    /**
     * Check whether controller action is allowed or not.
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::group');
    }

    /**
     * Save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $formData = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        
        $attributeIds = $this->getRequest()->getParam('attr_ids', null);
        $attributeIds = $this->jsonDecoder->decode($attributeIds);
        $attributeIds = array_keys($attributeIds);
        
        $newAttributeIds = [];
        if (!empty($attributeIds)) {
            $vendorAttributeCollection = $this->vendorAttributeFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('attribute_id', ['in' => $attributeIds]);
                                            
            if ($vendorAttributeCollection->getSize()) {
                $newAttributeIds = $vendorAttributeCollection->getColumnValues('attribute_id');
            }
        }
        
        if ($formData) {
            try {
                $vendorGroupId = $this->getRequest()->getParam('entity_id');
                $vendorGroupModel = $this->vendorGroupFactory->create();
                if ($vendorGroupId) {
                    $this->deleteOldAssignRecords($vendorGroupId);
                    $vendorGroupModel->load($vendorGroupId);
                }
                $vendorGroupModel->setData($formData);
                $vendorGroupModel->save();

                $vendorGroupId = $vendorGroupModel->getEntityId();

                if (!empty($newAttributeIds)) {
                    $this->insertNewAssignRecords($vendorGroupId, $newAttributeIds);
                }
                $this->messageManager->addSuccess(__('You saved this group'));
                $this->backendSessionFactory->create()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $vendorGroupId,
                        '_current' => true
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Attribute Group.'));
            }

            $this->backendSessionFactory->create()->setFormData($formData);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('entity_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Delete old records for Vendor Assign Collection
     *
     * @param int $vendorGroupId
     */
    protected function deleteOldAssignRecords($vendorGroupId)
    {
        $vendorAssignGroupCollection = $this->vendorAssignGroupFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('group_id', ['eq' => $vendorGroupId]);
        foreach ($vendorAssignGroupCollection as $vendorAssignGroup) {
            $this->deleteObject($vendorAssignGroup);
        }
    }

    /**
     * Delete new records for Vendor Assign Collection
     *
     * @param int $vendorGroupId
     * @param int $attributeIds
     */
    protected function insertNewAssignRecords($vendorGroupId, $attributeIds)
    {
        if (!empty($attributeIds)) {
            foreach ($attributeIds as $attributeId) {
                $vendorAssignGroupCollection = $this->vendorAssignGroupFactory->create()
                                                    ->getCollection()
                                                    ->addFieldToFilter('attribute_id', ['eq' => $attributeId])
                                                    ->addFieldToFilter('group_id', ['eq' => $vendorGroupId]);

                if (!$vendorAssignGroupCollection->getSize()) {
                    $this->createVendorAssign($vendorGroupId, $attributeId);
                }
            }
        }
    }

    /**
     * Create new Vendor Assign Record
     *
     * @param int $vendorGroupId
     * @param int $attributeId
     */
    protected function createVendorAssign($vendorGroupId, $attributeId)
    {
        $vendorAssignModel = $this->vendorAssignGroupFactory->create();
        $vendorAssignModel->setGroupId($vendorGroupId);
        $vendorAssignModel->setAttributeId($attributeId);
        $vendorAssignModel->save();
    }

    /**
     * Delete Object
     *
     * @param [type] $object
     */
    protected function deleteObject($object)
    {
        $object->delete();
    }
}
