<?php
/**
 * Webkul Affiliate Sales Mass Approve Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\Statistics;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Affiliate\Model\ResourceModel\Sale\CollectionFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Helper\Email as HelperEmail;

class SalesApprove extends \Magento\Backend\App\Action
{
    /**
     * Logger
     *
     * @var \Magento\Framework\Logger\Monolog
     */
    private $_logger;
    /**
     * Massactions for approve sales filter.
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Webkul\Affiliate\Model\UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var Webkul\Affiliate\Helper\Email
     */
    private $helperEmail;

    /**
     * @param Context            $context
     * @param Filter             $filter
     * @param CollectionFactory  $collectionFactory
     * @param UserBalanceFactory $userBalance
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        UserBalanceFactory $userBalance,
        HelperEmail $helperEmail,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Logger\Monolog $logger
    ) {
        $this->_logger = $logger;
        $this->redirect = $redirect;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->userBalance = $userBalance;
        $this->helperEmail = $helperEmail;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $recordUpdate = 0;
            foreach ($collection->getItems() as $affiSales) {
                if ($affiSales->getAffiliateStatus() == 0) {
                    $userBalColl = $this->userBalance->create()->getCollection()
                                        ->addFieldToFilter(
                                            'aff_customer_id',
                                            $affiSales->getAffCustomerId()
                                        );
                    if ($userBalColl->getSize()) {
                        foreach ($userBalColl as $userBalance) {
                            $totalBal = $userBalance->getBalanceAmount() + $affiSales->getCommission();
                            $userBalance->setBalanceAmount($totalBal);
                            $this->_saveObject($userBalance);
                        }
                    } else {
                        $data = [
                            'aff_customer_id' => $affiSales->getAffCustomerId(),
                            'balance_amount' => $affiSales->getCommission()
                        ];
                        $tmpUserBal = $this->userBalance->create();
                        $tmpUserBal->setData($data);
                        $this->_saveObject($tmpUserBal);
                    }
                    $affiSales->setAffiliateStatus(1);
                    $this->_saveObject($affiSales);
                    $recordUpdate++;

                    /** send order approve mail notification to Affiliate User*/
                    $this->helperEmail->sendMailToAffiliateUser(
                        $affiSales->getAffCustomerId(),
                        $affiSales->getOrderIncrementId()
                    );
                }
            }
            $this->messageManager->addSuccess(__(
                'A total of %1 record(s) have been updated.',
                $recordUpdate
            ));
        } catch (\Exception $e) {
            $this->_logger->info(json_encode($e));
            $this->messageManager->addError(__($e->getMessage()));
        }
        $url = $this->redirect->getRefererUrl();
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)
                            ->setPath($url);
    }

    /**
     * Check Affiliate Sales Approve Permission.
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
                ->isAllowed('Webkul_Affiliate::statistics_sales');
    }

    /**
     * saveObject
     * @param Object $object
     * @return void
     */
    private function _saveObject($object)
    {
        $object->save();
    }
}
