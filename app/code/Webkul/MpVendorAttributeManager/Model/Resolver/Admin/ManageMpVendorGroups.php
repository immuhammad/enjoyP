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
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup\CollectionFactory;

/**
 * ManageMpVendorGroups resolver, used for GraphQL request processing
 */
class ManageMpVendorGroups implements ResolverInterface
{
    public const ADMIN_USER_TYPE = 2;

    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup\CollectionFactory
     */
    protected $vendorGroupCollectionFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param CollectionFactory $vendorGroupCollectionFactory
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     */
    public function __construct(
        CollectionFactory $vendorGroupCollectionFactory,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper
    ) {
        $this->vendorGroupCollectionFactory = $vendorGroupCollectionFactory;
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
            $returnArray = [];
            if (empty($args['groupIds']) || !is_array($args['groupIds'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Group id(s) cannot be empty.')
                );
            }
            $vendorGroupCollection = $this->vendorGroupCollectionFactory->create()
                ->addFieldToFilter('entity_id', ['in' => $args['groupIds']]);
            if ($vendorGroupCollection->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Group id(s) are not valid.')
                );
            }
            switch ($args['action']) {
                case "DELETE":
                    $count = 0;
                    foreach ($vendorGroupCollection as $vendorGroup) {
                        $vendorGroup->setStatus(0);
                        $this->deleteObject($vendorGroup);
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been removed.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "ENABLE":
                    $count = 0;
                    foreach ($vendorGroupCollection as $vendorGroup) {
                        $vendorGroup->setStatus(1);
                        $this->saveObject($vendorGroup);
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been Saved.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "DISABLE":
                    $count = 0;
                    foreach ($vendorGroupCollection as $vendorGroup) {
                        $vendorGroup->setStatus(0);
                        $this->saveObject($vendorGroup);
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been Saved.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                default:
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __("'action' input argument is not valid.")
                    );
            }
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
