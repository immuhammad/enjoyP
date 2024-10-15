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
namespace Webkul\MpVendorAttributeManager\Plugin\Eav\Model\Attribute\Data;

class File
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Constructor
     *
     * @param Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_request = $request;
    }

    /**
     * Function
     *
     * @param \Magento\Eav\Model\Attribute\Data\File $subject
     * @param callable $proceed
     * @param mixed $value
     * @return void
     */
    public function aroundValidateValue(
        \Magento\Eav\Model\Attribute\Data\File $subject,
        callable $proceed,
        $value
    ) {
        return true;
    }
}
