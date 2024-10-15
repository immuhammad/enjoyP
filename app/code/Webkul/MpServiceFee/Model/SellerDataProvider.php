<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Model;

use \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\CollectionFactory;

class SellerDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    
    /**
     * @var array
     */
    protected $_loadedData;

    /**
     * @var CollectionFactory
     */
    protected $serviceCollectionFactory;

    /**
     * Class constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $serviceCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $serviceCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $serviceCollectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $serviceItem) {
            $this->_loadedData[$serviceItem->getId()] = $serviceItem->getData();
        }
        return $this->_loadedData;
    }
}
