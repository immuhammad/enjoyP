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
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;
use Webkul\MpVendorAttributeManager\Api\VendorGroupRepositoryInterface;

/**
 * AssignAttributesToGroup resolver, used for GraphQL request processing
 */
class AssignAttributesToGroup implements ResolverInterface
{
    public const ADMIN_USER_TYPE = 2;

    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $vendorAttributeCollectionFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $assignModelFactory;

    /**
     * @var VendorGroupRepositoryInterface
     */
    protected $vendorGroupRepo;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param CollectionFactory $vendorAttributeCollectionFactory
     * @param VendorAssignGroupFactory $assignModelFactory
     * @param VendorGroupRepositoryInterface $vendorGroupRepo
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     */
    public function __construct(
        CollectionFactory $vendorAttributeCollectionFactory,
        VendorAssignGroupFactory $assignModelFactory,
        VendorGroupRepositoryInterface $vendorGroupRepo,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper
    ) {
        $this->vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
        $this->assignModelFactory = $assignModelFactory;
        $this->vendorGroupRepo = $vendorGroupRepo;
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

        if (!isset($args['groupId'])) {
            throw new GraphQlInputException(
                __("'groupId' input argument is required.")
            );
        }
        // for checking if group exists or not
        $groupId = $this->vendorGroupRepo->getById($args['groupId'])->getId();
        try {
            $returnArray = [];
            if (empty($args['attributeIds']) || !is_array($args['attributeIds'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attribute id(s) cannot be empty.')
                );
            }

            $vendorAttributeCollection = $this->vendorAttributeCollectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $args['attributeIds']]);
            if ($vendorAttributeCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Attribute id(s) are not valid.')
                );
            }

            $vendorAttributes = $vendorAttributeCollection->getColumnValues("attribute_id");
            $vendorAssignGroupCollection = $this->assignModelFactory->create()->getCollection()
                                                ->addFieldToFilter("attribute_id", ['in' => $vendorAttributes]);

            foreach ($vendorAssignGroupCollection as $vendorAssignGroup) {
                $this->deleteObject($vendorAssignGroup);
            }

            foreach ($vendorAttributeCollection as $item) {
                $assignModel = $this->assignModelFactory->create();
                $assignCollection = $assignModel->getCollection()
                    ->addFieldToFilter('attribute_id', ['eq' => $item->getAttributeId()])
                    ->addFieldToFilter('group_id', ['eq' => $groupId]);
                if (!$assignCollection->getSize()) {
                    $assignModel->setAttributeId($item->getAttributeId());
                    $assignModel->setGroupId($groupId);
                    $this->saveObject($assignModel);
                }
            }

            $returnArray['message'] = __(
                'A total of %1 record(s) have been assigned.',
                $vendorAttributeCollection->getSize()
            );
            $returnArray['status'] = self::SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\Exception $e) {
            $returnArray['message'] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
        }
        return $returnArray;
    }

    /**
     * Save Object
     *
     * @param Object $object
     */
    protected function saveObject($object)
    {
        $object->save();
    }

    /**
     * Delete Object
     *
     * @param Object $object
     */
    protected function deleteObject($object)
    {
        $object->delete();
    }
}
