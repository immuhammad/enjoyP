<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\File\UploaderFactory;

class Booking extends \Magento\Catalog\Model\Product\Type\Virtual
{

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param UploaderFactory $uploaderFactory
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        UploaderFactory $uploaderFactory = null
    ) {
        $this->_helper = $helper;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $serializer,
            $uploaderFactory
        );
    }
    
    /**
     * Return true if product has options
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasOptions($product)
    {
        return true;
    }

    /**
     * Check if product has required options
     *
     * @param  \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasRequiredOptions($product)
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function prepareForCartAdvanced(\Magento\Framework\DataObject $buyRequest, $product, $processMode = null)
    {
        $productAttrSet = $product->getAttributeSetId();
        $productType = $product->getTypeId();
        $eventType = $this->_helper->getProductAttributeSetIdByLabel('Event Booking');

        if ($eventType == $productAttrSet && $productType == "booking") {
            $optionsFromRequest = $buyRequest->getOptions();
            $tmp = [];
            foreach ($optionsFromRequest as $optionIdKey => $optionVal) {
                $optionValues = $product->getOptionById($optionIdKey);
                if ($optionValues->getTitle() == "Event Tickets" && !is_array($optionVal)) {
                    $optionVal = [$optionVal];
                }
                $tmp[$optionIdKey] = $optionVal;
            }
            $buyRequest->setOptions($tmp);
        }
        if (!$processMode) {
            $processMode = self::PROCESS_MODE_FULL;
        }
        $_products = $this->_prepareProduct($buyRequest, $product, $processMode);
        $this->processFileQueue();
        return $_products;
    }
}
