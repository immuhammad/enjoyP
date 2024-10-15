<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Adminhtml\Managequotes;

use Magento\Backend\App\Action;
use Webkul\Mpquotesystem;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Mpquotesystem\Api\QuoteRepositoryInterface;

class MassUpdate extends \Magento\Backend\App\Action
{
    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_mpQuoteHelper;

    /**
     * @var Webkul\Mpquotesystem\Helper\Mail
     */
    protected $_mpQuoteMailHelper;

    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var Mpquotesystem\Model\ResourceModel\Quotes\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var QuoteRepositoryInterface
     */
    protected $_quoteRepository;

    /**
     * @param Action\Context                                             $context
     * @param Mpquotesystem\Helper\Data                                  $mpQuoteHelper
     * @param Mpquotesystem\Helper\Mail                                  $mpQuoteMailHelper
     * @param Filter                                                     $filter
     * @param Mpquotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory
     * @param QuoteRepositoryInterface                                   $quoteRepository
     */
    public function __construct(
        Action\Context $context,
        Mpquotesystem\Helper\Data $mpQuoteHelper,
        Mpquotesystem\Helper\Mail $mpQuoteMailHelper,
        Filter $filter,
        Mpquotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory,
        QuoteRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context);
        $this->_mpQuoteMailHelper = $mpQuoteMailHelper;
        $this->_mpQuoteHelper = $mpQuoteHelper;
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        $this->_quoteRepository = $quoteRepository;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpquotesystem::mpquotes'
        );
    }
    
    /**
     * Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $status = $data['quoteupdate'];
            if ($status == \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD) {
                $this->messageManager->addError(
                    __(
                        'Can not update Quote(s) Status to sold.'
                    )
                );
                return $resultRedirect->setPath('*/*/');
            }
            $quoteDeleted = 0;
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $quoteIds = $collection->getAllIds();
            list($updatedQuoteIds, $error) = $this->_validateMassUpdate($quoteIds, $status);
            if (!empty($error)) {
                foreach ($error as $message) {
                    $this->messageManager->addError($message);
                }
            }
            if (!empty($updatedQuoteIds)) {
                $coditionArr = [];
                foreach ($updatedQuoteIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $quotesCollection = $this->_collectionFactory->create();
                $quotesCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );
                foreach ($updatedQuoteIds as $quoteId) {
                    $this->_mpQuoteMailHelper->quoteStatusMail(
                        $quoteId,
                        __(
                            'Quote Status is updated by %1.',
                            'admin'
                        ),
                        'admin'
                    );
                }
                $this->messageManager->addSuccess(
                    __(
                        'A Total of %1 record(s) successfully updated.',
                        !empty($updatedQuoteIds)
                    )
                );
            }
            return $resultRedirect->setPath('*/*/');
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Updating the data.')
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Validate Mass Update
     *
     * @param int $quoteIds
     * @param string $status
     *
     * @return void
     */
    public function _validateMassUpdate($quoteIds, $status)
    {
        $error = [];
        if (!empty($quoteIds)) {
            foreach ($quoteIds as $key => $quoteId) {
                $quote = $this->_quoteRepository->getById($quoteId);
                $product = $this->_mpQuoteHelper->getProduct($quote->getProductId());
                $productQty = $this->getProductQty($quote, $product);
                if ($quote->getStatus() == \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD) {
                    $error[] = __('Quote id %1 Already has been sold', $quoteId);
                    unset($quoteIds[$key]);
                } elseif ($status==\Webkul\Mpquotesystem\Model\Quotes::STATUS_UNAPPROVED
                    && ($quote->getStatus()==\Webkul\Mpquotesystem\Model\Quotes::STATUS_DECLINE
                    || $quote->getStatus()==\Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED)
                ) {
                    $error[] = __('Can not update status for quote id %1', $quoteId);
                    unset($quoteIds[$key]);
                } elseif ($status==\Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED
                    && $productQty < $quote->getQuoteQty()
                    && $status == \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED
                ) {
                    unset($quoteIds[$key]);
                    $error[] = __('Requested quantity is not available for quote id %1.', $quoteId);
                }
            }
        }
        return [$quoteIds,$error];
    }
    
    /**
     * Get Product Qty
     *
     * @param object $quote
     * @param object $product
     *
     * @return void
     */
    protected function getProductQty($quote, $product)
    {
        if ($product->getTypeId() == "bundle") {
            $bundleOption = $this->_mpQuoteHelper->convertStringAccToVersion(
                $quote->getBundleOption(),
                'decode'
            );
            $validateQty = $this->_mpQuoteHelper->validateBundleProductQuantity(
                $product,
                $bundleOption,
                $quote,
                []
            );
            $productQty = 0;
            if ($validateQty) {
                $productQty = $this->_mpQuoteHelper->getBundleProductQuatity(
                    $product,
                    $bundleOption
                );
            }
        } elseif ($product->getTypeId()=='configurable') {
            $productQty = $this->_mpQuoteHelper->getConfigurableProductQuantity(
                $product,
                $quote
            );
        } else {
            $productQty = $product->getQuantityAndStockStatus()['qty'];
        }
        return round($productQty);
    }
}
