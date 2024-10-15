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
use Magento\Framework\Filesystem\Driver\File;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Escaper;

class Paymentnotify extends Action
{

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    /**
     * @var InvoiceSender
     */
    protected $_invoiceSender;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var Transaction\BuilderInterface
     */
    protected $_transactionBuilder;

    protected $_sandbox;
    protected $_apiRequestFormat;
    protected $_apiResponseFormat;

    /**
     * @param Context                                 $context
     * @param InvoiceSender                           $invoiceSender
     * @param OrderRepositoryInterface                $orderRepository
     * @param Transaction\BuilderInterface            $transactionBuilder
     * @param \Webkul\Mpadaptivepayment\Logger\Logger $logger
     */
    public function __construct(
        Context $context,
        InvoiceSender $invoiceSender,
        \Magento\Framework\HTTP\Client\Curl $curl,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Filesystem\Driver\File $filesystem,
        \Magento\Framework\Escaper $escper,
        Transaction\BuilderInterface $transactionBuilder
    ) {
        $this->_curl = $curl;
        $this->_invoiceSender = $invoiceSender;
        $this->escper=$escper;
        $this->_filesystem = $filesystem;
        $this->_orderRepository = $orderRepository;
        $this->_transactionBuilder = $transactionBuilder;
        parent::__construct($context);
    }

    /**
     * Payment notify action.
     *
     * @return void
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $orderid=explode('?orderid=', $data['cancel_url']);
        $orderIds = explode(',', $orderid[1]);
        //set PayPal Endpoint to sandbox
        $this->_sandbox = '';
        $helper = new \Magento\Framework\DataObject();/*$this->_objectManager->create(
            'Webkul\Mpadaptivepayment\Helper\Data'
        );*/
        $sandboxstatus = $helper->getMpAdaptivePaymentMode();
        if ($sandboxstatus == 1) {
            $this->_sandbox = 'sandbox.';
        }

        $this->_apiRequestFormat = 'NV';
        $this->_apiResponseFormat = 'JSON';

        $this->ipnPostMethod();

        $sandbox = $this->_sandbox;
        $adaptiveUrl = 'https://svcs.'.$sandbox.'paypal.com/AdaptivePayments/';
        if (!empty($data['pay_key'])) {
            /*for check payment details*/
            $url = trim($adaptiveUrl.'PaymentDetails');
            $bodyparams = [
                'payKey' => $data['pay_key'],
                'requestEnvelope.errorLanguage' => 'en_US',
                'requestEnvelope.detailLevel' => 'ReturnAll'
            ];

            $paymentData = $helper->getResponseFromCurl(
                $url,
                $bodyparams,
                $this->_apiRequestFormat,
                $this->_apiResponseFormat
            );
            if (isset($paymentData['status'])
                && ($paymentData['status']!='INCOMPLETE'
                || ($paymentData['status']=='INCOMPLETE'
                    && $paymentData['actionType'] == "PAY_PRIMARY"))
            ) {
                foreach ($orderIds as $orderId) {
                    $order = $this->_orderRepository->get($orderId);
                    $paymentCode = '';

                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }

                    $lastTransId = $order->getPayment()->getLastTransId();

                    $returnData =$this->getOrderSellerData($orderId);

                    $idsToCreateInvoice = $returnData['seller_amount_data'];
                    $flag = $returnData['flag'];

                    $sellerIds = [];
                    if ($paymentData['actionType'] == 'PAY_PRIMARY' && $lastTransId == '') {
                            $ordercollection = $this->_objectManager->create(
                                \Webkul\Marketplace\Model\Saleslist::class
                            )->getCollection()
                            ->addFieldToFilter('order_id', $orderId);
                        foreach ($ordercollection as $datatemp) {
                            $datatemp->setDelayedChainedPayment(1);
                            $datatemp->save();
                        }
                    } else {
                        $sellerIds = $returnData['seller_ids_data'];
                    }
                    /*$this->_logger->info(print_r($idsToCreateInvoice));*/
                    $this->_objectManager->create(
                        \Webkul\Marketplace\Helper\Orders::class
                    )->getCommssionCalculation($order);

                    if (!$lastTransId || $lastTransId=="" || $lastTransId!==$data['pay_key']) {
                          $trId = $this->saveTransaction(
                              $order,
                              $data['pay_key'],
                              $paymentData
                          );
                    }

                    /*create invoice for all sellers and admin*/
                    $this->partInv($idsToCreateInvoice, $order, $orderId, $sellerId, $paymentCode);
                    // if (count($idsToCreateInvoice) > 0) {
                    //     $shipadminnew = 0;
                    //     $idsToCreateInvoice[0] = 0;
                    //     $shippingAmount = 0;

                    //     foreach ($idsToCreateInvoice as $key => $value) {
                    //         $sellerId = $key;
                    //         if ($order->canUnhold()) {
                    //             return;
                    //             /*$this->_logger->info(
                    //                 print_r(
                    //                     __('Can not create invoice as order is in HOLD state')
                    //                 )
                    //             );*/
                    //         } else {
                    //             $invoiceId = 0;
                    //             $codCharges = 0;
                    //             $marketplaceOrder = $this->_objectManager->create(
                    //                 \Webkul\Marketplace\Model\Orders::class
                    //             )->getCollection()
                    //             ->addFieldToFilter('order_id', $orderId)
                    //             ->addFieldToFilter('seller_id', $sellerId);
                    //             foreach ($marketplaceOrder as $tracking) {
                    //                 $shippingAmount = $tracking->getShippingCharges();
                    //                 if ($paymentCode == 'mpcashondelivery') {
                    //                     $codCharges = $tracking->getCodCharges();
                    //                 }
                    //                 $invoiceId = $tracking->getInvoiceId();
                    //             }

                    //             if (!$invoiceId) {
                    //                 $items = [];
                    //                 $itemsarray = [];
                    //                 $codCharges = 0;
                    //                 $tax = 0;

                    //                 $collection = $this->_objectManager->create(
                    //                     \Webkul\Marketplace\Model\Saleslist::class
                    //                 )->getCollection()
                    //                 ->addFieldToFilter('seller_id', $sellerId)
                    //                 ->addFieldToFilter('order_id', $orderId);

                    //                 foreach ($collection as $saleproduct) {
                    //                     if ($paymentCode == 'mpcashondelivery') {
                    //                         $codCharges = $codCharges + $saleproduct->getCodCharges();
                    //                     }
                    //                     $tax = $tax + $saleproduct->getTotalTax();
                    //                     array_push($items, $saleproduct['order_item_id']);
                    //                 }

                    //                 $itemsarray = $this->_getItemQtys($order, $items);

                    //                 $total = $itemsarray['subtotal'] + $shippingAmount + $codCharges + $tax;
                    //                 /*invoice*/
                    //                 if (count($itemsarray) > 0 || $total) {
                    //                     $this->createSellerOrderInvoice(
                    //                         $order,
                    //                         $itemsarray,
                    //                         $data['pay_key'],
                    //                         $paymentCode,
                    //                         $shippingAmount,
                    //                         $sellerId,
                    //                         $codCharges,
                    //                         $tax
                    //                     );
                    //                 }
                    //             }
                    //         }
                    //     }
                    //     $this->createShippingInvoice(
                    //         $order,
                    //         $data['pay_key']
                    //     );
                    // } else {
                    //     $this->createOrderInvoice($order, $data['pay_key']);
                    // }

                    /*end invoice*/
                    if ($trId && $trId!=="" && $trId!==0) {
                        $this->payToSellerMethod($order, $sellerIds, $flag, $trId);
                    }
                }
            }
        }
    }

    protected function partInv($idsToCreateInvoice, $order, $orderId, $sellerId, $paymentCode)
    {
        $data = $this->getRequest()->getParams();
        if (count($idsToCreateInvoice) > 0) {

            $shipadminnew = 0;
            $idsToCreateInvoice[0] = 0;
            $shippingAmount = 0;
            foreach ($idsToCreateInvoice as $key => $value) {

                $sellerId = $key;
                if ($order->canUnhold()) {
                    return;
                    /*$this->_logger->info(
                        print_r(
                            __('Can not create invoice as order is in HOLD state')
                        )
                    );*/
                } else {

                    $invoiceId = 0;
                    $codCharges = 0;
                    $marketplaceOrder = $this->_objectManager->create(
                        \Webkul\Marketplace\Model\Orders::class
                    )->getCollection()
                    ->addFieldToFilter('order_id', $orderId)
                    ->addFieldToFilter('seller_id', $sellerId);
                    foreach ($marketplaceOrder as $tracking) {
                        $shippingAmount = $tracking->getShippingCharges();
                        $codCharges=($paymentCode == 'mpcashondelivery')? ($tracking->getCodCharges()):($codCharges);
                        $invoiceId = $tracking->getInvoiceId();
                    }

                    if (!$invoiceId) {
                        $items = [];
                        $itemsarray = [];
                        $codCharges = 0;
                        $tax = 0;

                        $collection = $this->_objectManager->create(
                            \Webkul\Marketplace\Model\Saleslist::class
                        )->getCollection()
                        ->addFieldToFilter('seller_id', $sellerId)
                        ->addFieldToFilter('order_id', $orderId);

                        foreach ($collection as $saleproduct) {
                            // if ($paymentCode == 'mpcashondelivery') {
                            //     $codCharges = $codCharges + $saleproduct->getCodCharges();
                            // }
                            $codCharges=($paymentCode=='mpcashondelivery')?
                            ($codCharges+$saleproduct->getCodCharges()):($codCharges);
                            $tax = $tax + $saleproduct->getTotalTax();
                            array_push($items, $saleproduct['order_item_id']);
                        }

                        $itemsarray = $this->_getItemQtys($order, $items);

                        $total = $itemsarray['subtotal'] + $shippingAmount + $codCharges + $tax;
                        /*invoice*/
                        if (count($itemsarray) > 0 || $total) {
                            $this->createSellerOrderInvoice(
                                $order,
                                $itemsarray,
                                $data['pay_key'],
                                $paymentCode,
                                $shippingAmount,
                                $sellerId,
                                $codCharges,
                                $tax
                            );
                        }
                    }
                }
            }
            $this->createShippingInvoice(
                $order,
                $data['pay_key']
            );
        } else {
            $this->createOrderInvoice($order, $data['pay_key']);
        }
    }

    /**
     * Post IPN data back to PayPal to validate the IPN data is genuine
     * Without this step anyone can fake IPN data
     * @return void
     */
    protected function ipnPostMethod()
    {
        /*read post data*/
        $rawPostData = $this->_filesystem->fileGetContents('php://input');
        $rawPostArray = explode('&', $rawPostData);
        $myPost = [];
        foreach ($rawPostArray as $keyval) {
            $keyval = explode('=', $keyval);
            if (count($keyval) == 2) {
                $myPost[$keyval[0]] = urldecode($keyval[1]);
            }
        }
        // read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        if (function_exists('get_magic_quotes_gpc')) {
            $getMagicQuotesExists = true;
        }
        foreach ($myPost as $key => $value) {
            if ($getMagicQuotesExists == true && get_magic_quotes_gpc() == 1) {
                $value = urlencode($this->escper->escapeQuote($value));
            } else {
                $value = urlencode($value);
            }
            $req .= "&$key=$value";
        }

        $paypalUrl='https://www.'.$this->_sandbox.'paypal.com/cgi-bin/webscr';

        $this->curl->post($paypalUrl, $req);
        // if ($ch == false) {
        //     return false;
        // }

        // curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        // curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
        // curl_setopt($ch, CURLOPT_HEADER, 1);
        // curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Connection: Close']);

       // $res = curl_exec($ch);
        $response=$this->_curl->getBody();
        if ($response) {
            $this->_logger->info(
                date('[Y-m-d H:i e] ').
                "Can't connect to PayPal to validate IPN message: ".
                ($response).PHP_EOL
            );
            //curl_close($ch);
            return false;
        } else {
            $this->_logger->info(
                date('[Y-m-d H:i e] ').
                'HTTP request of validation request:'.
                $response.
                "for IPN payload: $req".PHP_EOL
            );
            $this->_logger->info(
                date('[Y-m-d H:i e] ').
                "HTTP response of validation request: $res".PHP_EOL
            );
            //curl_close($ch);
        }
        // Inspect IPN validation result and act accordingly
        // Split response headers and payload, a better way for strcmp
        $tokens = explode("\r\n\r\n", trim($response));
        $res = trim(end($tokens));
        if (strcmp($response, 'VERIFIED') == 0) {
            // check whether the payment_status is Completed
            $this->_logger->info(
                date('[Y-m-d H:i e] ')."Verified IPN: $req ".PHP_EOL
            );
        } elseif (strcmp($response, 'INVALID') == 0) {
            // log for manual investigation
            // Add business logic here which deals with invalid IPN messages
            $this->_logger->info(
                date('[Y-m-d H:i e] ')."Invalid IPN: $req".PHP_EOL
            );
        }
    }

    /**
     * Get Seller array data with seller amount in a order.
     * @param $orderId
     * @return array
     */
    protected function getOrderSellerData($orderId = '')
    {
        $flag = 0;
        $idsToCreateInvoice = [];
        $sellerIdsData = [];

        $ordercollection = $this->_objectManager->create(
            \Webkul\Marketplace\Model\Saleslist::class
        )->getCollection()
        ->addFieldToFilter('order_id', $orderId)
        ->addFieldToFilter('cpprostatus', 0);
        foreach ($ordercollection as $orderitem) {
            $flag = 1;
            $sellerId = $orderitem->getSellerId();
            array_push($sellerIdsData, $sellerId);
            $actualSellerAmount = $orderitem->getActualSellerAmount();
            if (isset($idsToCreateInvoice[$sellerId])
                && $idsToCreateInvoice[$sellerId]
            ) {
                $idsToCreateInvoice[$sellerId] =
                $idsToCreateInvoice[$sellerId] + $actualSellerAmount;
            } else {
                $idsToCreateInvoice[$sellerId] = $actualSellerAmount;
            }
        }

        return [
            'seller_ids_data' => $sellerIdsData,
            'seller_amount_data' => $idsToCreateInvoice,
            'flag' => $flag
        ];
    }

    /**
     * Save order payment and transaction with payment raw data.
     * @param $order
     * @param $transactionId
     * @param $paymentData
     * @return string
     */
    protected function saveTransaction($order, $transactionId, $paymentData)
    {
        $transactionData = $paymentData;
        $transactionData['responseEnvelope'] = json_encode(
            $paymentData['responseEnvelope']
        );
        $transactionData['cancelUrl'] = $paymentData['cancelUrl'];
        $transactionData['currencyCode'] = $paymentData['currencyCode'];
        $transactionData['ipnNotificationUrl'] = $paymentData['ipnNotificationUrl'];
        $transactionData['paymentInfoList'] = json_encode(
            $paymentData['paymentInfoList']
        );
        $transactionData['returnUrl'] = $paymentData['returnUrl'];
        $transactionData['senderEmail'] = $paymentData['senderEmail'];
        $transactionData['status'] = $paymentData['status'];
        $transactionData['payKey'] = $paymentData['payKey'];
        $transactionData['actionType'] = $paymentData['actionType'];
        $transactionData['feesPayer'] = $paymentData['feesPayer'];
        $transactionData['sender'] = json_encode($paymentData['sender']);

        if (!empty($paymentData['shippingAddress'])) {
            $transactionData['shippingAddress'] = json_encode(
                $paymentData['shippingAddress']
            );
        } else {
            $transactionData['shippingAddress'] = '';
        }
        $reverseOnError  = $paymentData['reverseAllParallelPaymentsOnError'];
        $transactionData['reverseAllParallelPaymentsOnError']=$reverseOnError;

        $payment = $order->getPayment();

        $payment->setLastTransId($transactionId);
        $payment->setTransactionId($transactionId);
        $payment->setAdditionalInformation(
            [Transaction::RAW_DETAILS => $transactionData]
        );
        $formatedPrice = $order->getBaseCurrency()->formatTxt(
            $order->getGrandTotal()
        );

        $message = __('The captured amount is %1.', $formatedPrice);

        $transaction = $this->_transactionBuilder->setPayment($payment)
        ->setOrder($order)
        ->setTransactionId($transactionId)
        ->setAdditionalInformation(
            [Transaction::RAW_DETAILS => $transactionData]
        )
        ->setFailSafe(true)
        ->build(Transaction::TYPE_CAPTURE);

        $trId = $transaction->save()->getId();
        if ($trId) {
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $message
            );
            $payment->setParentTransactionId(null);
            $payment->save();
            $order->save();
        }
        return $trId;
    }

    /**
     * Create seller items invoice.
     * @param $order
     * @param $transactionId
     * @param $shippingAmount
     * @return string
     */
    protected function createSellerOrderInvoice(
        $order,
        $itemsarray,
        $transactionId,
        $paymentCode,
        $shippingAmount,
        $sellerId,
        $codCharges,
        $tax
    ) {
        if ($order->canInvoice()) {
            $orderId = $order->getId();
            $newTax = 0;
            $shipadminnew = 0;
            $baseShippingTaxAmount = $order->getBaseShippingTaxAmount();
            $totalQtyOrdered = $order->getTotalQtyOrdered();
            if ($baseShippingTaxAmount !== null && $baseShippingTaxAmount !== '') {
                $newtax = $baseShippingTaxAmount / $totalQtyOrdered;
                foreach ($itemsarray['data'] as $value) {
                    if ((int)$value!==0
                        && $value!==""
                        && $newTax!==null
                    ) {
                        $newTax = $newTax + $newtax * (int)$value;
                    }
                }
            }
            if ((int)$shippingAmount==0 && (int)$sellerId==0) {
                $shipadminnew = $order->getShippingAmount();
            }

            if (isset($shipadminnew)
                && (int)$shipadminnew!==0
                && $shipadminnew!==""
                && $shipadminnew!== null
            ) {
                $shippingAmount = $shipadminnew;
            }
            $subtotal = $itemsarray['subtotal'];
            $invoice = $this->_objectManager->create(
                Magento\Sales\Model\Service\InvoiceService::class
            )->prepareInvoice($order, $itemsarray['data']);
            $invoice->setTransactionId($transactionId);
            $invoice->setRequestedCaptureCase('online');
            $invoice->setShippingAmount($shippingAmount);
            $invoice->setBaseShippingInclTax($shippingAmount);
            $invoice->setBaseShippingAmount($shippingAmount);
            $invoice->setSubtotal($subtotal);
            $invoice->setBaseSubtotal($itemsarray['baseSubtotal']);
            if ($paymentCode == 'mpcashondelivery') {
                $invoice->setMpcashondelivery($codCharges);
            }

            $total = $subtotal+$shippingAmount+$codCharges+$tax+$newTax;
            $invoice->setGrandTotal($total);
            $invoice->setBaseGrandTotal($total);
            $newTax=0;

            $invoice->register();
            $invoice->save();
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->_objectManager->create(
                Magento\Framework\DB\Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();

            $invoiceId = $invoice->getId();

            $this->_invoiceSender->send($invoice);
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->setState('processing')
            ->setStatus('processing')
            ->save();

            /*--update mpcod table records--*/
            if ($paymentCode == 'mpcashondelivery') {
                $saleslistColl = $this->_objectManager->create(
                    \Webkul\Marketplace\Model\Saleslist::class
                )
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['eq' => $orderId]
                )
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $sellerId]
                );
                foreach ($saleslistColl as $saleslist) {
                    $saleslist->setCollectCodStatus(1);
                    $saleslist->save();
                }
            }
            $trackingcol1 = $this->_objectManager->create(\Webkul\Marketplace\Model\Orders::class)
            ->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('seller_id', $sellerId);
            foreach ($trackingcol1 as $row) {
                $row->setInvoiceId($invoiceId);
                $row->save();
            }
        }
    }

    /**
     * Create remaining order shipping amount invoice.
     * @param $order
     * @param $transactionId
     * @param $shippingAmount
     * @return void
     */
    protected function createShippingInvoice($order, $transactionId)
    {
        if ($order->getTotalDue()) {
            $shippingAmount = $order->getTotalDue();
            $invoice = $this->_objectManager->create(
                Magento\Sales\Model\Service\InvoiceService::class
            )->prepareInvoice($order);
            $invoice->setTransactionId($transactionId);
            $invoice->setRequestedCaptureCase('online');
            $invoice->setShippingAmount($shippingAmount);
            $invoice->setBaseShippingInclTax($shippingAmount);
            $invoice->setBaseShippingAmount($shippingAmount);
            $invoice->setSubtotal($shippingAmount);
            $invoice->setBaseSubtotal($shippingAmount);
            $invoice->setGrandTotal($shippingAmount);
            $invoice->setBaseGrandTotal($shippingAmount);
            $invoice->register();
            $invoice->save();
            $invoice->getOrder()->setIsInProcess(true);

            $transactionSave = $this->_objectManager->create(
                Magento\Framework\DB\Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();

            $invoiceId = $invoice->getId();

            $this->_invoiceSender->send($invoice);
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->setState('processing')
            ->setStatus('processing')
            ->save();
        }
    }

    /**
     * Create order invoice.
     * @param $order
     * @param $transactionId
     * @return void
     */
    protected function createOrderInvoice($order, $transactionId)
    {
        if ($order->canInvoice()) {
            $invoice = $this->_objectManager->create(
                Magento\Sales\Model\Service\InvoiceService::class
            )->prepareInvoice($order);
            $invoice->setTransactionId($transactionId);
            $invoice->setRequestedCaptureCase('online');
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->_objectManager->create(
                Magento\Framework\DB\Transaction::class
            )->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
            $this->_invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->setState('processing')
            ->setStatus('processing')
            ->save();
        }
    }

    /**
     * Pay to seller if action type is pay.
     * @param array | $sellerIds
     * @param bool | $flag
     * @param int | $trId
     * @return void
     */
    protected function payToSellerMethod($order, $sellerIds, $flag, $trId)
    {
        if (count($sellerIds) > 0 && $flag == 1) {
            $data = new \Magento\Framework\DataObject();/*$this->_objectManager->create(
                'Webkul\Mpadaptivepayment\Model\Mpadaptivepayment'
            )
            ->getCollection()
            ->addFieldToFilter('seller_id', ['in' => $sellerIds]);*/
            if (count($data)) {
                $helper = new \Magento\Framework\DataObject();/*$this->_objectManager->create(
                    'Webkul\Mpadaptivepayment\Helper\Data'
                );*/
                foreach ($data as $paypaldetail) {
                    $sellerId = $paypaldetail->getSellerId();
                    $paypalid = $paypaldetail->getPaypalId();
                    $paypalfname = $paypaldetail->getPaypalFname();
                    $paypallname = $paypaldetail->getPaypalLname();
                    if ($helper->paypalaccountcheck($paypalid, $paypalfname, $paypallname)) {
                        if (!($helper->getMpAdaptivePaymentType() == 1 &&
                            $helper->getMpAdaptivePaymentTypeChained() == 1)
                        ) {
                            $this->_objectManager->create(
                                \Webkul\Marketplace\Helper\Orders::class
                            )->paysellerpayment($order, $sellerId, $trId);
                        }
                    }
                }
            }
        }
    }

    /**
     * Get Order item array data.
     * @param $order
     * @param $items
     * @return array
     */
    public function customMergeArray($item, $item_child)
    {
        return array_merge([$item], $item_child);
    }
    protected function _getItemQtys($order, $items)
    {
        $data = [];
        $subtotal = 0;
        $baseSubtotal = 0;
        foreach ($order->getAllItems() as $item) {

            if (in_array($item->getItemId(), $items)) {

                $data[$item->getItemId()] = (int)(
                    $item->getQtyOrdered() - $item->getQtyInvoiced()
                );

                $_item = $item;

                // for bundle product
                // $bundleitems = array_merge([$_item], $_item->getChildrenItems());
                $bundleitems = $this->customMergeArray($_item, $_item->getChildrenItems());

                if ($_item->getParentItem()) {
                    continue;
                }

                if ($_item->getProductType() == 'bundle') {
                    foreach ($bundleitems as $_bundleitem) {
                        if ($_bundleitem->getParentItem()) {
                            $data[$_bundleitem->getItemId()] = (int)(
                                $_bundleitem->getQtyOrdered() - $item->getQtyInvoiced()
                            );
                        }
                    }
                }
                $subtotal += $_item->getRowTotal();
                $baseSubtotal += $_item->getBaseRowTotal();
            } else {
                if (!$item->getParentItemId()) {
                    $data[$item->getItemId()] = 0;
                }
            }
        }
        return [
            'data' => $data,
            'subtotal' => $subtotal,
            'baseSubtotal' => $baseSubtotal
        ];
    }
}
