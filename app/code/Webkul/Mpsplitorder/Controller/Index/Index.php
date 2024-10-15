<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitorder
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpsplitorder\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Exception\LocalizedException;

class Index extends Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var Webkul\Mpsplitorder\Helper\Data
     */
    protected $_mpsplitHelper;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;
    /**
     * @var SessionManager
     */
    protected $_coreSession;
    /**
     * @var Webkul\Mpadaptivepayment\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param Context                               $context
     * @param OrderRepositoryInterface              $orderRepository
     * @param \Magento\Checkout\Model\Session       $checkoutSession
     * @param \Webkul\Mpsplitorder\Helper\Data $mpsplitHelper
     * @param \Webkul\Mpadaptivepayment\Helper\Data $helper
     * @param PageFactory                           $resultPageFactory
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\Mpsplitorder\Helper\Data $mpsplitHelper,
        //\Webkul\Mpadaptivepayment\Helper\Data $helper,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        SessionManager $coreSession
    ) {
        $this->_mpsplitHelper = $mpsplitHelper;
        $this->_coreSession = $coreSession;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {

            $html="";
            //ini_set('track_errors', 1);

            $helper = new \Magento\Framework\DataObject();
            /*$this->_objectManager->create(
                'Webkul\Mpadaptivepayment\Helper\Data'
            );*/

            $paypalMerchantId = $helper->getMpAdaptivePaymentMerchantId();
            if (!$this->_checkoutSession->getQuoteId()) {
                throw new LocalizedException(__('Payment authorizing error.'));
            }
            //set PayPal Endpoint to sandbox
            $sandbox = '';
            $sandboxstatus = $helper->getMpAdaptivePaymentMode();
            if ($sandboxstatus == 1) {
                $sandbox = 'sandbox.';
            }

            $payPalBaseURL = 'https://www.'.$sandbox.'paypal.com/webscr';
            $payPalAdaptiveURL = 'https://svcs.'.$sandbox.'paypal.com/AdaptivePayments/';

            $url = trim($payPalAdaptiveURL.'Pay');
            $feesPayer = $helper->getFeesPayer();//TODO
            //Default App ID for Sandbox

            $APIRequestFormat = 'NV';
            $APIResponseFormat = 'JSON';

            //for cart calculation
            $itemArray = $this->_coreSession->getData('item');
            if (count($itemArray) == 0) {

                return;
            }
            $orderIds = $this->_coreSession->getData('orderids');
            $lastOrderId = $this->_coreSession->getData('lastOrderId');
            $splitShippingInfo = $this->_coreSession->getData('shipping_info');
            $order = $this->_objectManager->create(Magento\Sales\Model\Order::class)->load($lastOrderId);
            $cart = [];
            $i = 0;
            $commission = 0;
            $newvar = '';
            $orderShippingTaxAmount = 0;
            $orderShippingAmount = 0;
            $customerAddressId = 0;
            if (!empty($order->getShippingAddress())) {
                $shipmeth = $splitShippingInfo['shipping_method'];
                $orderShippingTaxAmount = $splitShippingInfo['shipping_tax_amount'];
                $orderShippingAmount = $splitShippingInfo['shipping_amount'];
                $customerAddressId = $order->getShippingAddress()
                ->getCustomerAddressId();
            } else {
                $shipmeth = '';
                $customerAddressId = $order->getBillingAddress()
                ->getCustomerAddressId();
            }
            if ($customerAddressId == null) {
                $customerAddressId = $order->getBillingAddress()
                ->getCustomerAddressId();
            }
            $customerAddress = $this->_objectManager->create(
                Magento\Customer\Model\Address::class
            )->load($customerAddressId);
            $customName = $customerAddress['firstname'].' '.$customerAddress['lastname'];
            //Guest User
            if (!$customerAddressId || $customerAddressId == null) {
                $customName = $order->getBillingAddress()->getFirstname().
                ' '.$order->getBillingAddress()->getLastname();
                $customerAddress = $order->getBillingAddress();
            }

            $methods = $this->_objectManager->create(
                Magento\Shipping\Model\Config::class
            )->getActiveCarriers();
            $options = [];
            $allmethods = [];
            $shipinf = [];
            foreach ($methods as $_code => $_method) {
                array_push($allmethods, $_code);
            }

            if ($shipmeth == 'mp_multi_shipping_mp_multi_shipping') {
                $newvar = 'webkul';
                $shippinginfo = $this->_coreSession->getData(
                    'selected_shipping'
                );
                foreach ($shippinginfo as $key => $val) {
                    $shipinf[] = ['seller' => $key,'amount' => $val['amount']];
                }
            } else {
                $shipmethod = explode('_', $shipmeth, 2);
                $shippinginfo = $this->_coreSession->getShippingInfo();
                if (in_array($shipmethod[0], $allmethods) && !empty($shippinginfo[$shipmethod[0]])) {
                    foreach ($shippinginfo[$shipmethod[0]] as $key) {
                        $newvar = 'webkul';
                        foreach ($key['submethod'] as $k => $v) {
                            $shipinf[]=($k == $shipmethod[1])?([
                                'seller' => $key['seller_id'],
                                'amount' => $v['cost'],
                            ]):($shipinf);

                            // if ($k == $shipmethod[1]) {
                            //     $shipinf[] = [
                            //         'seller' => $key['seller_id'],
                            //         'amount' => $v['cost'],
                            //     ];
                            // }
                        }
                    }
                }
            }
            $marketplaceHelper = $this->_objectManager->create(
                \Webkul\Marketplace\Helper\Data::class
            );
            $advanceCommissionRule = $this->_customerSession->getData(
                'advancecommissionrule'
            );
            foreach ($itemArray as $item) {
                $invoiceprice = 0;
                $itemId = $item['product_id'];
                $tempcoms = 0;
                if (!$marketplaceHelper->getUseCommissionRule()) {
                    $this->_eventManager->dispatch(
                        'mp_advance_commission',
                        ['id' => $itemId]
                    );
                    $advancecommission = $this->_customerSession->getData(
                        'commission'
                    );
                    if ($advancecommission != '') {
                        $percent = $advancecommission;
                        $commType = $marketplaceHelper->getCommissionType();
                        $tempcoms=($commType == 'fixed')?
                                        $percent:($item['row_total']*$advancecommission)/100;
                        // if ($commType == 'fixed') {
                        //     $tempcoms = $percent;
                        // } else {
                        //     $tempcoms = ($item['row_total'] * $advancecommission) / 100;
                        // }
                        
                        $tempcoms=($tempcoms > $item['row_total'])?
                                $item['row_total'] * $marketplaceHelper->getConfigCommissionRate() / 100:$tempcoms;
                        // if ($tempcoms > $item['row_total']) {
                        //     $tempcoms = $item['row_total'] * $marketplaceHelper->getConfigCommissionRate() / 100;
                        // }
                        $commissionDetail['id'] = $item['seller_id'];
                    }
                } else {

                    $commissionDetail['id']=(count($advanceCommissionRule))?
                    ($item['seller_id']):
                    ($commissionDetail['id']);

                    $tempcoms=(count($advanceCommissionRule) &&
                    $advanceCommissionRule[$item['id']]['type'] == 'fixed')?
                    ($advanceCommissionRule[$item['id']]['amount']):
                    (($item['row_total'] * $advanceCommissionRule[$item['id']]['amount']) / 100);
                    
                    // if (count($advanceCommissionRule)) {
                    //     if ($advanceCommissionRule[$item['id']]['type'] == 'fixed') {
                    //         $tempcoms = $advanceCommissionRule[$item['id']]['amount'];
                    //     } else {
                    //         $tempcoms =
                    //         ($item['row_total'] * $advanceCommissionRule[$item['id']]['amount']) / 100;
                    //     }
                    //     $commissionDetail['id'] = $item['seller_id'];
                    // }
                }
                if (!$tempcoms) {
                    $commissionDetail = $this->_mpsplitHelper->getSellerDetailById($item['seller_id']);
                    if ($commissionDetail['id'] !== 0 &&
                        $commissionDetail['commission'] !== 0
                    ) {
                        $tempcoms = round(
                            ($item['row_total'] * $commissionDetail['commission']) / 100,
                            2
                        );
                    }
                }

                $commission = $commission + $tempcoms;
                $price = $item['row_total'] - $tempcoms;
                $invoiceprice = $item['row_total'];

                $shippingprice = 0;
                if ($newvar == 'webkul') {
                    $custid = $item['seller_id'];
                    foreach ($shipinf as $k => $key) {
                        if ($key['seller'] == $custid) {
                            $price = $price + $key['amount'];
                            $shippingprice = $key['amount'];
                            $shipinf[$k]['amount'] = 0;
                        }
                    }
                }
                if ($orderShippingTaxAmount !== 0 &&
                    ($commissionDetail['id'] == 0 &&
                    $commissionDetail['id'] == '' &&
                    $commissionDetail['id'] == null)
                ) {
                    $adminShipTaxAmt = 1;
                    $cart[$i]['data'] = $commissionDetail['id'].
                    ','.
                    $item['product_id'].
                    ','.
                    $price.
                    ','.
                    $invoiceprice.
                    ','.
                    $shippingprice.
                    ','.
                    ($item['tax_amount'] + $orderShippingTaxAmount);
                } else {
                    $cart[$i]['data'] = $commissionDetail['id'].
                    ','.
                    $item['product_id'].
                    ','.
                    $price.
                    ','.
                    $invoiceprice.
                    ','.
                    $shippingprice.
                    ','.
                    $item['tax_amount'];
                }
                ++$i;
            }

            asort($cart);
            $finalcart = [];
            $i = 0;
            $totalsellerpaytoadmin = 0;
            $adminTotalTax = 0;
            $sellertax = 0;
            foreach ($cart as $item) {
                $paypalid = 0;
                $temp = explode(',', $item['data']);

                if ($temp[0] != 0) {
                    $data = $this->_objectManager->create(
                        Webkul\Mpadaptivepayment\Model\Mpadaptivepayment::class
                    )->getCollection()
                    ->addFieldToFilter('seller_id', $temp[0]);

                    if (count($data)) {

                        foreach ($data as $paypaldetail) {

                            $paypalid = $paypaldetail->getPaypalId();
                            $paypalfname = $paypaldetail->getPaypalFname();
                            $paypallname = $paypaldetail->getPaypalLname();

                               $paypalid=($paypalid && !$helper->paypalaccountcheck(
                                   $paypalid,
                                   $paypalfname,
                                   $paypallname
                               ))?'':$paypalid;
                            $sellerTaxToAdmin=(!$paypalid || ($paypalid == $paypalMerchantId))?1:$sellerTaxToAdmin;
                            $paypalid=(!$paypalid || ($paypalid == $paypalMerchantId))?$paypalMerchantId:$paypalid;
                            $totalsellerpaytoadmin=(!$paypalid || ($paypalid == $paypalMerchantId))?
                            $totalsellerpaytoadmin + $temp[2]:$totalsellerpaytoadmin;
                            
                            // if (!$paypalid ||
                            //     ($paypalid == $paypalMerchantId)
                            // ) {
                            //     $sellerTaxToAdmin = 1;
                            //     $paypalid = $paypalMerchantId;
                            //     $totalsellerpaytoadmin = $totalsellerpaytoadmin + $temp[2];
                            // }
                        }
                    } else {
                        $sellerTaxToAdmin = 1;
                        $paypalid = $paypalMerchantId;
                        $totalsellerpaytoadmin = $totalsellerpaytoadmin + $temp[2];
                    }
                } else {
                    $paypalid = $paypalMerchantId;
                }
                $sellertax = 0;
                if (!$marketplaceHelper->getConfigTaxManage()) {
                    $adminTotalTax = $adminTotalTax + $temp[5];
                } else {
                    $sellertax = $temp[5];
                }
                if ($i == 0) {
                    $finalcart[$i]['price'] = $temp[2] + $sellertax;
                    $finalcart[$i]['seller'] = $temp[0];
                    $finalcart[$i]['paypalid'] = $paypalid;
                    ++$i;
                } else {
                    if ($temp[0] == $finalcart[$i - 1]['seller']) {
                        $finalcart[$i - 1]['price'] =
                        $finalcart[$i - 1]['price'] + $sellertax + $temp[2];
                    } else {
                        $finalcart[$i]['price'] = $temp[2] + $sellertax;
                        $finalcart[$i]['seller'] = $temp[0];
                        $finalcart[$i]['paypalid'] = $paypalid;
                        ++$i;
                    }
                }
            }
            $status = 0;
            $index = 0;
            $counter = 0;
            foreach ($finalcart as $cart) {
                if ($cart['seller'] == 0) {
                    $status = 1;
                    $index = $counter;
                }
                ++$counter;
            }
            $adminShippingTax = 0;
            $quoteshipPrice = 0;
            if ($newvar != 'webkul') {
                if ($helper->getShippingTaxClass() &&
                    (!isset($adminShipTaxAmt) ||
                    (int)$adminShipTaxAmt == 0 ||
                    $adminShipTaxAmt == null)
                ) {
                    $adminShippingTax = $orderShippingTaxAmount;
                }
                $quoteshipPrice = $orderShippingAmount;
            }
            if ($status == 1) {
                $finalcart[$index]['price'] = $finalcart[$index]['price'] +
                $quoteshipPrice + $commission + $adminShippingTax;
            } else {
                $orderShippingTaxAmountAndCommission = ($orderShippingTaxAmount !== 0)? $commission + $orderShippingTaxAmount:$commission;
                $finalcart[$counter]['price'] = ($newvar == '')?$quoteshipPrice + $commission + $adminShippingTax:$orderShippingTaxAmountAndCommission;
                
                $finalcart[$counter]['seller'] = 0;
                $finalcart[$counter]['paypalid'] = $paypalMerchantId;
                $finalcart[$counter]['primary'] = false;
            }
            $count = 0;
            $totalsellercount = count($finalcart);

            foreach ($finalcart as $partner) {
                if ($partner['paypalid'] == $paypalMerchantId) {
                    if ($finalcart[$count]['seller'] == 0) {
                        if ((int)$adminTotalTax == 0) {
                            $finalcart[$count]['price'] = $finalcart[$count]['price'] +
                            $totalsellerpaytoadmin;

                            $finalcart[$count]['price'] =($sellertax !== 0 &&
                                isset($sellerTaxToAdmin) &&
                                (int)$sellerTaxToAdmin == 1
                            ) ?($finalcart[$count]['price'] + $sellertax):
                                ($finalcart[$count]['price']);
                            
                        } else {
                            $finalcart[$count]['price'] = $finalcart[$count]['price'] +
                            $totalsellerpaytoadmin + $adminTotalTax;
                        }
                    } else {
                        $finalcart[$count]['price'] = 0;
                        --$totalsellercount;
                    }
                }
                ++$count;
            }
            $actionType = 'PAY';
            if (count($finalcart) > 1) {
                if ($helper->getMpAdaptivePaymentType() == 1) {
                    $feesPayer = $helper->getFeesPayerChained();
                    $actionType=($helper->getMpAdaptivePaymentTypeChained()==1 && $totalsellercount>1) ?
                         'PAY_PRIMARY':$actionType;
                    $totalamount = 0;
                    foreach ($finalcart as $partner) {
                        $totalamount = ($partner['price'])?
                        $totalamount + $partner['price']:
                        $totalamount;
                    }
                    $count = 0;
                    foreach ($finalcart as $partner) {
                        if ($partner['paypalid'] == $paypalMerchantId && $partner['seller'] == 0) {
                            $finalcart[$count]['price'] = $totalamount;
                            $finalcart[$count]['primary']=($totalsellercount > 1)?true:$finalcart[$count]['primary'];
                        }
                        ++$count;
                    }
                }
            }
            $bodyparams = [
                'requestEnvelope.errorLanguage' => 'en_US',
                'actionType' => $actionType,
                'feesPayer' => $feesPayer,
                'currencyCode' => $marketplaceHelper->getCurrentCurrencyCode(),
                'cancelUrl' => $this->_mpsplitHelper->getCancelUrl(
                    $orderIds
                ),
                'returnUrl' => $helper->getReturnlUrl(
                    $this->_checkoutSession->getQuoteId(),
                    $this->_checkoutSession->getLastOrderId()
                ),
                'ipnNotificationUrl' => $this->_mpsplitHelper->getIpnNotificationUrl(),
            ];

            $i = 0;
            foreach ($finalcart as $partner) {
                if ($partner['price'] != 0) {
                    if (!isset($partner['primary'])) {
                        $partner['primary'] = false;
                    }
                    $temp = [
                        "receiverList.receiver($i).email" => $partner['paypalid'],
                        "receiverList.receiver($i).amount" => $partner['price'],
                        "receiverList.receiver($i).primary" => $partner['primary'],
                    ];
                    $bodyparams += $temp;
                    ++$i;
                }
            }
            // convert payload array into url encoded query string
            $response = $helper->getResponseFromCurl(
                $url,
                $bodyparams,
                $APIRequestFormat,
                $APIResponseFormat
            );

            //set paypal redirect url in checkout session
            if (!empty($response['responseEnvelope']['ack']) &&
                $response['responseEnvelope']['ack'] == 'Success'
            ) {
                if (!empty($response['payKey'])) {
                    $url = trim($payPalAdaptiveURL.'SetPaymentOptions');
                    $bodyparams = [
                        'requestEnvelope.errorLanguage' => 'en_US',
                        'requestEnvelope.detailLevel' => 'ReturnAll',
                        'payKey' => $response['payKey'],
                        'senderOptions.referrerCode' => 'Webkul_SP',
                        'senderOptions.shippingAddress.addresseeName' => $customName,
                        'senderOptions.shippingAddress.city' => $customerAddress['city'],
                        'senderOptions.shippingAddress.country' => $customerAddress['country_id'],
                        'senderOptions.shippingAddress.state' => $customerAddress['region'],
                        'senderOptions.shippingAddress.street1' => $customerAddress['street'],
                        'senderOptions.shippingAddress.zip' => $customerAddress['postcode'],
                    ];
                    //create request and add headers
                   
                    $resSetoptionArray = $helper->getResponseFromCurl(
                        $url,
                        $bodyparams,
                        $APIRequestFormat,
                        $APIResponseFormat
                    );

                    if (!empty($resSetoptionArray['responseEnvelope']['ack'])
                        && $resSetoptionArray['responseEnvelope']['ack'] == 'Success'
                    ) {
                        //set url to approve the transaction
                        $payPalURL = $payPalBaseURL.'?cmd=_ap-payment&paykey='.$response['payKey'];
                        $html =  $html.'<p><a id="paypalredirect" href="' .
                        $payPalURL . '">'.
                        __("Click here if you are not redirected within 10 seconds").
                        '...</a> </p>';
                        $html =  $html.'<script type="text/javascript">   
                        function redirect(){
                        document.getElementById("paypalredirect").click();
                        }
                        setTimeout(redirect, 2000);
                        </script>';
                        $this->getResponse()->setBody($html);
                        return;
                    }
                }
            } else {
                throw new LocalizedException(
                    __(
                        'ERROR Code: %1 <br/>ERROR Message: %2',
                        $response['error'][0]['errorId'],
                        urldecode($response['error'][0]['message'])
                    )
                );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
