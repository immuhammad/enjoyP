<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpquotesystem\Ui\DataProvider;

use Webkul\Mpquotesystem\Model\ResourceModel\Quotes\CollectionFactory;

/**
 * Class to get Quotes List Data
 */
class QuotesListDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Marketplace\Helper\Data $helperData,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $sellerId = $helperData->getCustomerId();
        $productCollection = [];
        $productCollection = $productCollectionFactory->create()
                                    ->addFieldToFilter(
                                        'seller_id',
                                        ['eq' => $sellerId]
                                    )
                                    ->addFieldToSelect('mageproduct_id');
        $collectionData = $collectionFactory->create()
                                ->addFieldToFilter(
                                    'product_id',
                                    ['in' => $productCollection->getData()]
                                );
        $this->collection = $collectionData;
    }
}
