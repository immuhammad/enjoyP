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
use Magento\Customer\Model\AttributeFactory;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

/**
 * ManageMpVendorAttributes resolver, used for GraphQL request processing
 */
class ManageMpVendorAttributes implements ResolverInterface
{
    public const ADMIN_USER_TYPE = 2;

    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $vendorAttributeCollectionFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param AttributeFactory $attributeFactory
     * @param CollectionFactory $vendorAttributeCollectionFactory
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        CollectionFactory $vendorAttributeCollectionFactory,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
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
            switch ($args['action']) {
                case "DELETE":
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        $attributeModel = $this->loadAttributeById($vendorAttribute->getAttributeId());
                        $this->deleteObject($attributeModel);
                        $this->deleteObject($vendorAttribute);
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been deleted.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "ENABLE":
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        $vendorAttribute->setWkAttributeStatus(1);
                        $this->saveObject($vendorAttribute);
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) Status has been Enabled.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "DISABLE":
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        $vendorAttribute->setWkAttributeStatus(0);
                        $this->saveObject($vendorAttribute);
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) Status have been Disabled.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "DISPLAY_ON_SELLER_PROFILE":
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        if (in_array($vendorAttribute->getAttributeUsedFor(), [0,2])) {
                            $vendorAttribute->setShowInFront(1);
                            $this->saveObject($vendorAttribute);
                            $count++;
                        }
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been enabled for Seller profile.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "HIDE_FROM_SELLER_PROFILE":
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        $vendorAttribute->setShowInFront(0);
                        $this->saveObject($vendorAttribute);
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been disabled for Seller Profile.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "ASSIGN_TO_CUSTOMER_AND_SELLER":
                    $attributeUsedFor = 0;
                    $isVisible = 1;
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        $attribute = $this->loadAttributeById($vendorAttribute->getAttributeId());
                        $attribute->setIsVisible($isVisible);
                        $this->saveModel($attribute);

                        $vendorAttribute->setAttributeUsedFor($attributeUsedFor);
                        $this->saveModel($vendorAttribute);
                        
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been updated.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "ASSIGN_ONLY_TO_CUSTOMER":
                    $attributeUsedFor = 1;
                    $isVisible = 1;
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        $attribute = $this->loadAttributeById($vendorAttribute->getAttributeId());
                        $attribute->setIsVisible($isVisible);
                        $this->saveModel($attribute);

                        $vendorAttribute->setAttributeUsedFor($attributeUsedFor);
                        $this->saveModel($vendorAttribute);
                        
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been updated.',
                        $count
                    );
                    $returnArray['status'] = self::SUCCESS;
                    break;
                case "ASSIGN_ONLY_TO_SELLER":
                    $attributeUsedFor = 2;
                    $isVisible = 0;
                    $count = 0;
                    foreach ($vendorAttributeCollection as $vendorAttribute) {
                        $attribute = $this->loadAttributeById($vendorAttribute->getAttributeId());
                        $attribute->setIsVisible($isVisible);
                        $this->saveModel($attribute);

                        $vendorAttribute->setAttributeUsedFor($attributeUsedFor);
                        $this->saveModel($vendorAttribute);
                        
                        $count++;
                    }
                    $returnArray['message'] = __(
                        'A total of %1 record(s) have been updated.',
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
     * Load Attribute Model by Id
     *
     * @param Int $id
     *
     * @return Object $attributeModel
     */
    protected function loadAttributeById($id)
    {
        $attributeModel = $this->attributeFactory->create()->load($id);
        return $attributeModel;
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
