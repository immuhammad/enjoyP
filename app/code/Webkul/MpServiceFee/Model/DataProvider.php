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

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @param \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\Collection $serviceCollection
     * @param \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\CollectionFactory $serviceCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\Collection $serviceCollection,
        \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\CollectionFactory $serviceCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $serviceCollection;
        $this->rowCollection = $serviceCollectionFactory->create();
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
        $items = $this->rowCollection->addFieldToFilter('seller_id', 0)->getItems();
        foreach ($items as $serviceItem) {
            $this->_loadedData['']["servicefee_form_container"][] = $serviceItem
            ->getData();
        }
        return $this->_loadedData;
    }
}
