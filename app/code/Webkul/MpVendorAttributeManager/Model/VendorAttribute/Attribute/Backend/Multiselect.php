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
namespace Webkul\MpVendorAttributeManager\Model\VendorAttribute\Attribute\Backend;

class Multiselect extends \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Before Attribute Save Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $value = $this->request->getPostValue();
        $explodedValue = explode('_', $attributeCode);
        if (is_array($explodedValue)) {
            if ('wkv' == $explodedValue[0]) {
                $data = $this->request->getPostValue();
                if (isset($data[$attributeCode]) && !is_array($data[$attributeCode])) {
                    $data = [];
                }
                if (isset($data[$attributeCode])) {
                    $object->setData($attributeCode, join(',', $data[$attributeCode]));
                }
                if (!$object->hasData($attributeCode)) {
                    $object->setData($attributeCode, false);
                }
                return $this;
            }
        }

        return parent::beforeSave($object);
    }

    /**
     * After Load Attribute Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($this->getAttribute()->getIsUserDefined() && $this->getAttribute()->getEntityTypeId() == 1) {
            $data = $object->getData($attributeCode);
            if (!is_array($data) && $data) {
                $object->setData($attributeCode, explode(',', $data));
            } else {
                $object->setData($attributeCode, []);
            }
        }
        return $this;
    }
}
