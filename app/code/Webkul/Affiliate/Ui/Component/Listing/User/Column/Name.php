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
use Magento\Customer\Api\CustomerRepositoryInterface;

class Name extends Column
{
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

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
        CustomerRepositoryInterface $customerRepository,
        array $components = [],
        array $data = []
    ) {

        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->customerRepository = $customerRepository;
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
                try {
                    $customer = $this->customerRepository->getById($item[$this->getData('name')]);
                    $customerName = $customer->getFirstName()." ".$customer->getLastName();
                } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                    $customerName = __("N/A");
                }
                
                $item['aff_user_id'] = $item[$this->getData('name')];
                $item['aff_balance_amount'] = isset($item['balance_amount'])? $item['balance_amount'] : '';
                $item[$this->getData('name')] = $item[$this->getData('name')]? $customerName : __('Not Available');
            }
        }
        return $dataSource;
    }
}
