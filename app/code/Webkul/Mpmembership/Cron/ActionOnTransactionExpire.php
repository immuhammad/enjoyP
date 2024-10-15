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

namespace Webkul\Mpmembership\Cron;

use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;
use Webkul\Mpmembership\Model\Transaction;

class ActionOnTransactionExpire
{
    /**
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Webkul\Mpmembership\Helper\Data $helper,
        CollectionFactory $collectionFactory
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * checks if any pack is expired
     *
     * @return void
     */
    public function execute()
    {
        try {
            $this->helper->logDataInLogger(
                "Cron_ActionOnTransactionExpire_execute : RUN"
            );
            $transactionCollection = $this->collectionFactory->create();
            if ($transactionCollection->getSize()) {
                foreach ($transactionCollection as $data) {
                    if ($data["transaction_status"]=="pending") {
                        $this->helper->updateTransactionType($data->getId(), Transaction::PENDING);
                    } elseif ($data->getCheckType() == Transaction::TIME_AND_PRODUCTS) {
                        $this->helper->validateTransactionForTimeAndProducts($data);
                    } elseif ($data->getCheckType() == Transaction::TIME) {
                        $this->helper->validateTransactionForTime($data);
                    } elseif ($data->getCheckType() == Transaction::PRODUCTS) {
                        $this->helper->validateTransactionForProducts($data);
                    } else {
                        $this->helper->updateTransactionType($data->getId(), Transaction::EXPIRED);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Cron_ActionOnTransactionExpire_execute Exception : ".$e->getMessage()
            );
        }
    }
}
