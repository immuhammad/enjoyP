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

class Massdelete extends \Magento\Backend\App\Action
{
    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_mpQuoteHelper;

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
     * @param Filter                                                     $filter
     * @param Mpquotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory
     * @param QuoteRepositoryInterface                                   $quoteRepository
     */
    public function __construct(
        Action\Context $context,
        Mpquotesystem\Helper\Data $mpQuoteHelper,
        Filter $filter,
        Mpquotesystem\Model\ResourceModel\Quotes\CollectionFactory $collectionFactory,
        QuoteRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context);
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
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $quoteDeleted = 0;
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());

        try {
            foreach ($collection as $item) {
                $this->_quoteRepository->deleteById($item->getEntityId());
                $quoteDeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $quoteDeleted)
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __('Something went wrong while Deleting the data.')
            );
        }
        return $resultRedirect->setPath('*/*/');
    }
}
