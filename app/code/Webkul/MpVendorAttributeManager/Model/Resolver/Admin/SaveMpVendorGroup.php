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
declare(strict_types=1);

namespace Webkul\MpVendorAttributeManager\Model\Resolver\Admin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorGroupFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;

/**
 * SaveMpVendorGroup resolver, used for GraphQL request processing
 */
class SaveMpVendorGroup implements ResolverInterface
{
    public const ADMIN_USER_TYPE = 2;

    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

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
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param VendorAttributeFactory $vendorAttributeFactory
     * @param VendorGroupFactory $vendorGroupFactory
     * @param VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     */
    public function __construct(
        VendorAttributeFactory $vendorAttributeFactory,
        VendorGroupFactory $vendorGroupFactory,
        VendorAssignGroupFactory $vendorAssignGroupFactory,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper
    ) {
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorGroupFactory = $vendorGroupFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if ($context->getUserType() != self::ADMIN_USER_TYPE) {
            throw new GraphQlAuthorizationException(__('Unauthorized access. Only admin can access this information.'));
        }

        try {
            if (empty($args['input']) || !is_array($args['input'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('"input" value should be specified')
                );
            }
            $groupData = $args['input'];
            $attributeIds = $groupData['attributeIds'] ?? [];

            $newAttributeIds = [];
            if (!empty($attributeIds)) {
                $vendorAttributeCollection = $this->vendorAttributeFactory->create()
                                                ->getCollection()
                                                ->addFieldToFilter('entity_id', ['in' => $attributeIds]);
                                                
                if ($vendorAttributeCollection->getSize()) {
                    $newAttributeIds = $vendorAttributeCollection->getColumnValues('attribute_id');
                }
            }

            $vendorGroupId = $groupData['entity_id'] ?? 0;
            $vendorGroupModel = $this->vendorGroupFactory->create();
            if ($vendorGroupId) {
                $this->deleteOldAssignRecords($vendorGroupId);
                $vendorGroupModel->load($vendorGroupId);
            } else {
                unset($groupData['entity_id']);
            }
            $vendorGroupModel->setData($groupData);
            $vendorGroupModel->save();

            $vendorGroupId = $vendorGroupModel->getEntityId();

            if (!empty($newAttributeIds)) {
                $this->insertNewAssignRecords($vendorGroupId, $newAttributeIds);
            }

            $returnArray['message'] = __('You saved this group');
            $returnArray['status'] = self::SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\RuntimeException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\Exception $e) {
            $returnArray['message'] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
        }
        return $returnArray;
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
