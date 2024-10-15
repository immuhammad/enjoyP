<?php
/**
 * Webkul Affiliate User Name UI.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Ui\Component\Listing\User\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Webkul\Affiliate\Helper\Data as AffDataHelper;

class AffiliateLinks extends Column
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Webkul\Affiliate\Helper\Data
     */
    public $affDataHelper;

    private $logger;

    /**
     * Constructor.
     *
     * @param ContextInterface           $context
     * @param UiComponentFactory         $uiComponentFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param array                      $components
     * @param array                      $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ProductFactory $productFactory,
        Session $customerSession,
        AffDataHelper $affDataHelper,
        \Psr\Log\LoggerInterface $logger,
        array $components = [],
        array $data = []
    ) {

        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->customerSession = $customerSession;
        $this->affDataHelper = $affDataHelper;
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $id = (int)$item['entity_id'];
                $products = $this->productFactory->create()->load($id);
                $isAffiliate = $this->affDataHelper->isAffiliateUser($this->customerSession->getCustomerId());
                $affId = $isAffiliate['data']->getCustomerId();
                $item['type_id'] = $products->getProductUrl().'?aff_id='.$affId.'&prod_id='.$products->getId();
            }
        }
        return $dataSource;
    }
}
