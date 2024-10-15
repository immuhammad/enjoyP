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
use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 *  Webkul Mpmembership Index Ipnnotifyproduct controller
 */
class Paypalsellerreturn extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var \Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Webkul\Mpmembership\Model\Transaction
     */
    protected $membershipModel;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @param Context                                $context
     * @param CollectionFactory                      $collectionFactory
     * @param \Webkul\Mpmembership\Model\Transaction $membershipModel
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Webkul\Mpmembership\Model\Transaction $membershipModel,
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

            if (isset($data['ref_no'])) {
                $invoice = explode('-', $data['ref_no']);

                $transdata = [
                    'seller_id' => $invoice[1] ,
                    'reference_number' => $invoice[0],
                    'transaction_status' => 'pending'
                ];
                if (isset($data['check_type'])) {
                    $transdata['check_type'] = $data['check_type'];
                }
                if (array_key_exists("no_of_products", $data)) {
                    $transdata['no_of_products'] = $data['no_of_products'];
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
            $this->helper->logDataInLogger("Controller_Index_Paypalsellerreturn execute : ".$e->getMessage());
        }
        $this->_redirect('mpmembership');
    }
}
