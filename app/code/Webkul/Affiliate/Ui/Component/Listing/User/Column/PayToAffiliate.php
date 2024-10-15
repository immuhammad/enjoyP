<?php
/**
 * Webkul Affiliate PayToAffiliate Column.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Ui\Component\Listing\User\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\Template;
use Webkul\Affiliate\Helper\Data as AffiliateDataHelper;

class PayToAffiliate extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var AffiliateDataHelper
     */
    private $affiDataHelper;

    /**
     * @var Template
     */
    private $template;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Template $template,
        AffiliateDataHelper $affiDataHelper,
        array $components = [],
        array $data = []
    ) {
    
        $this->affiDataHelper = $affiDataHelper;
        $this->urlBuilder = $urlBuilder;
        $this->template = $template;
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
            $fieldName = $this->getData('name');
            $affConfig = $this->affiDataHelper->getAffiliateConfig();
            $currency = $this->affiDataHelper->getCurrentCurrencyCode();
            foreach ($dataSource['data']['items'] as & $item) {
                $buttomActive =  $item['pay_notify'] ? "style='border-color:green;color:green;'" : '';
                $affUserPayment = isset($item['current_payment_method']) ? json_decode($item['current_payment_method'], true) : [];
                $item[$fieldName . '_html'] = "<button ".$buttomActive." class='button' id='aff-user-"
                                                .$item['aff_user_id']."'><span>". __('Pay To Affiliate')
                                                ."</span></button>";
                $item[$fieldName . '_seller_payment_method'] = $item['current_payment_method'];
                $item[$fieldName . '_loader_src'] = $this->template
                                                            ->getViewFileUrl('Webkul_Affiliate::images/loader.gif');
                $item[$fieldName . '_sellerid'] = $item['aff_user_id'];
                if (isset($affUserPayment['payment_method'])
                    && ($affUserPayment['payment_method'] == 'paypal_standard')) {
                    $item[$fieldName . '_title'] = __('Pay to affiliate by paypal');
                    $item[$fieldName . '_urlfix'] = $affConfig['sandbox'] ? 'sandbox.' : '';
                    $item[$fieldName . '_currency'] = $currency;
                    $item[$fieldName . '_admin_email'] = $affConfig['manager_email'];
                    $item[$fieldName . '_firstname'] = $affConfig['manager_first_name'];
                    $item[$fieldName . '_lastname'] = $affConfig['manager_last_name'];
                    $item[$fieldName . '_returnurl'] = $this->urlBuilder
                                            ->getUrl('affiliate/user/paypalsuccess/id/'.$item['aff_user_id']);
                    $item[$fieldName . '_cancelreturn'] = $this->urlBuilder->getUrl('affiliate/user/paypalfailed');
                    $item[$fieldName . '_ipnnotify'] = $this->urlBuilder->getUrl('affiliate/user/paypalipnnotify');
                } else {
                    $item[$fieldName . '_title'] = __('Affiliate User Transaction Detail');
                    $item[$fieldName . '_submitlabel'] = __('Save Detail');
                    $item[$fieldName . '_cancellabel'] = __('Cancel');
                    $item[$fieldName . '_formaction'] = $this->urlBuilder->getUrl('affiliate/user/pay');
                }
            }
        }
        return $dataSource;
    }
}
