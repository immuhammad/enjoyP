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

namespace Webkul\MpServiceFee\Ui\DataProvider;

use Webkul\Marketplace\Helper\Data as HelperData;
use \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\Collection as ServiceCollection;
use \Webkul\MpServiceFee\Model\ResourceModel\AttributesList\CollectionFactory;

class ServiceFeesList extends \Magento\Ui\DataProvider\AbstractDataProvider
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
     * @param ServiceCollection $serviceCollection
     * @param HelperData $marketplaceHelper
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $serviceCollectionFactory,
        ServiceCollection $serviceCollection,
        HelperData $marketplaceHelper,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $sellerId = $marketplaceHelper->getCustomerId();
        $collectionData = $serviceCollectionFactory->create()->addFieldToFilter("seller_id", ["eq" => $sellerId]);
        $this->collection = $collectionData;
    }
}
