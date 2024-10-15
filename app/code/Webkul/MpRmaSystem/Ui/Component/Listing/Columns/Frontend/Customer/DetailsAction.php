<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Ui\Component\Listing\Columns\Frontend\Customer;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class DetailsAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Initialize Dependencies
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     * @return void
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['final_status']>0) {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'mprmasystem/customer/rma/',
                            ['id' => $item['id']]
                        ),
                        'label' => __('View'),
                        'hidden' => false,
                    ];
                } else {
                    $item[$this->getData('name')]['edit'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'mprmasystem/customer/rma/',
                            ['id' => $item['id']]
                        ),
                        'label' => __('View'),
                        'hidden' => false,
                    ];
                    $item[$this->getData('name')]['cancel'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'mprmasystem/customer/cancel/',
                            ['id' => $item['id']]
                        ),
                        'confirm' => [
                            'title' => __('Delete'),
                            'message' => __('Are you sure you want to cancel RMA')
                        ],
                        'label' => __('Cancel'),
                        'hidden' => false,
                    ];
                }
            }
        }

        return $dataSource;
    }
}
