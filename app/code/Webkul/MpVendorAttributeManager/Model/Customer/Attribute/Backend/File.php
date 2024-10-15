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
namespace Webkul\MpVendorAttributeManager\Model\Customer\Attribute\Backend;

class File extends \Magento\Eav\Model\Entity\Attribute\Backend\Increment
{
    /**
     * @var string
     */
    protected $_type = 'file';

     /**
      * Save uploaded file and set its name to category
      *
      * @param \Magento\Framework\DataObject $object
      * @return \Magento\Catalog\Model\Category\Attribute\Backend\Image
      */
    public function afterSave($object)
    {
        return $this;
    }
}
