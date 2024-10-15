<?php
/**
 * Webkul Affiliate User Transaction View UI.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Ui\Component\Listing\User\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

/**
 * Class Status.
 */
class TrafficView extends Column
{
    /**
     * @var Magento\Framework\UrlInterface
     */
    private $urlInterface;

    /**
     * Constructor.
     *
     * @param ContextInterface           $context
     * @param UiComponentFactory         $uiComponentFactory
     * @param UrlInterface               $urlInterface
     * @param array                      $components
     * @param array                      $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlInterface,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlInterface = $urlInterface;
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
                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlInterface->getUrl(
                        'affiliate/statistics/traffic',
                        ['aff_customer_id' =>$item['aff_user_id']]
                    ),
                    'label' => __('View'),
                    'hidden' => false,
                ];
            }
        }
        return $dataSource;
    }
}
