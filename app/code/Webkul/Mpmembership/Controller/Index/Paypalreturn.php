<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 *  Webkul Mpmembership Index Ipnnotifyproduct controller
 */
class Paypalreturn extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var \Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Webkul\Mpmembership\Model\Product
     */
    protected $membershipModel;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @param Context                            $context
     * @param CollectionFactory                  $collectionFactory
     * @param \Webkul\Mpmembership\Model\Product $membershipModel
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Webkul\Mpmembership\Model\Product $membershipModel,
        \Webkul\Mpmembership\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->membershipModel = $membershipModel;
        $this->helper     = $helper;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * executes when paypal returns to website after completing payment
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            $productIds = [];

            if (isset($data['ref_no'])) {
                $invoice = explode('-', $data['ref_no']);
                if (isset($data['pro_ids'])) {
                    $productIds = json_decode(urldecode($data['pro_ids']));
                }

                $transdata = [
                    'seller_id' => $invoice[1] ,
                    'reference_number' => $invoice[0],
                    'transaction_status' => 'pending'
                ];

                if (!empty($productIds)) {
                    $transdata['no_of_products'] = count($productIds);
                    $transdata['product_ids'] = implode(",", $productIds);
                }
                $membershipdata = $this->collectionFactory->create();
                $collection = $membershipdata->addFieldToFilter(
                    'reference_number',
                    ['eq' => $invoice[0]]
                );

                if ($collection->getSize() <= 0) {
                    $sellerTransactionCollection = $this->membershipModel
                        ->setData($transdata)
                        ->save();
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Index_Paypalreturn execute : ".$e->getMessage());
        }
        $this->_redirect('mpmembership');
    }
}
