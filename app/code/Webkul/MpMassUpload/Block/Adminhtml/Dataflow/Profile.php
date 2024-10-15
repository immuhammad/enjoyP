<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Block\Adminhtml\Dataflow;

use Webkul\MpMassUpload\Api\AttributeProfileRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as AttributeGroup;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as ProductAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Webkul\MpMassUpload\Api\AttributeMappingRepositoryInterface;

class Profile extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * @var AttributeProfileRepositoryInterface
     */
    protected $_attributeProfileRepository;

    /**
     * @var AttributeGroup
     */
    protected $_attributeGroup;

    /**
     * @var ProductAttribute
     */
    protected $_productAttributeCollection;

    /**
     * @var EavAttribute
     */
    protected $_eavAttribute;

    /**
     * @var AttributeMappingRepositoryInterface
     */
    protected $_attributeMappingRepository;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param AttributeProfileRepositoryInterface              $attributeProfileRepository
     * @param AttributeGroup                                   $attributeGroup
     * @param ProductAttribute                                 $productAttributeCollection
     * @param EavAttribute                                     $eavAttribute
     * @param AttributeMappingRepositoryInterface              $attributeMappingRepository
     * @param \Magento\Framework\Json\Helper\Data              $jsonHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        AttributeProfileRepositoryInterface $attributeProfileRepository,
        AttributeGroup $attributeGroup,
        ProductAttribute $productAttributeCollection,
        EavAttribute $eavAttribute,
        AttributeMappingRepositoryInterface $attributeMappingRepository,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_jsonHelper = $jsonHelper;
        $this->_attributeProfileRepository = $attributeProfileRepository;
        $this->_attributeGroup = $attributeGroup;
        $this->_productAttributeCollection = $productAttributeCollection;
        $this->_eavAttribute = $eavAttribute;
        $this->_attributeMappingRepository = $attributeMappingRepository;
        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Webkul_MpMassUpload';
        $this->_controller = 'adminhtml_dataflow_profile';
        parent::_construct();
        $this->buttonList->remove('delete');
    }

    /**
     * Get Header Text.
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Mass Upload Dataflow Profile');
    }

    /**
     * Check permission for passed action.
     *
     * @param string $resourceId
     *
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Get Dataflow Profile By Id.
     * @param int $id
     *
     * @return collection object
     */
    public function getDataflowProfileById($id)
    {
        $attributeProfile = $this->_attributeProfileRepository->get($id);
        return $attributeProfile;
    }

    /**
     * Get getMappedProfileFields By Profile Id.
     * @param int $id
     *
     * @return collection object
     */
    public function getMappedProfileFields($profileId)
    {
        $mappedProfileFields = $this->_attributeMappingRepository
            ->getByProfileId($profileId);
        return $mappedProfileFields;
    }

   /**
    * get all attributes
    *
    * @param int $attributeSetId
    * @return array
    */
    public function getAllAttributes($attributeSetId)
    {
        $attributeids = [];
        $groups = $this->_attributeGroup->create()
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();
        foreach ($groups as $node) {
            $nodeChildren = $this->loadData($node);
            if ($nodeChildren->getSize() > 0) {
                foreach ($nodeChildren->getItems() as $child) {
                    array_push($attributeids, $child->getAttributeId());
                }
            }
        }
        return $attributeids;
    }

    /**
     * get resource eav attribute
     *
     * @param int $id
     * @return void
     */
    public function getCatalogResourceEavAttribute($id)
    {
        return $this->_eavAttribute->load($id);
    }

    /**
     * loads model data
     *
     * @param object $node
     * @return collection
     */
    public function loadData($node)
    {
        $nodeChildren = [];
        $nodeChildren = $this->_productAttributeCollection->create()
        ->setAttributeGroupFilter($node->getId())
        ->addVisibleFilter()
        ->load();
        return $nodeChildren;
    }

    /**
     * encodes data in json format
     *
     * @param array $data
     * @return json array
     */
    public function jsonEncode($data)
    {
        return $this->_jsonHelper->jsonEncode($data);
    }
}
