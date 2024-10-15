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

namespace Webkul\Mpquotesystem\Model;

use Webkul\Mpquotesystem\Model\QuotesFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;

class QuoteRepository implements \Webkul\Mpquotesystem\Api\QuoteRepositoryInterface
{
    /**
     * @var Webkul\Mpquotesystem\Model\QuotesFactory
     */
    protected $_quoteFactory;

    /**
     * @param QuotesFactory $quoteFactory
     */
    public function __construct(
        QuotesFactory $quoteFactory
    ) {
        $this->_quoteFactory = $quoteFactory;
    }

    /**
     * Save quote.
     *
     * @param Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote
     * @return Webkul\Mpquotesystem\Api\Data\QuoteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote)
    {
        $quoteModel = null;
        if ($quote->getEntityId()) {
            $quoteModel = $this->_quoteFactory->create()->load($quote->getEntityId());
        }
        if ($quoteModel === null) {
            $quoteModel = $this->_quoteFactory->create();
            $quoteModel->addData($quote);
        } else {
            $quoteModel->addData($quote);
        }
        $quoteId = $quoteModel->save()->getEntityId();
        return $this->_quoteFactory->load($quoteId);
    }

    /**
     * Retrieve customer address.
     *
     * @param int $quoteId
     * @return \Magento\Customer\Api\Data\AddressInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($quoteId)
    {
        $quoteModel = $this->_quoteFactory->create()->load($quoteId);
        return $quoteModel;
    }

    /**
     * Delete quote.
     *
     * @param \Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Webkul\Mpquotesystem\Api\Data\QuoteInterface $quote)
    {
        try {
            $quoteId = $quote->getEntityId();
            $quoteModel = $this->_quoteFactory->create()->load($quoteId);
            $quoteModel->delete();
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove quote %1', $quoteId)
            );
        }
        return true;
    }

    /**
     * Delete quote by quote ID.
     *
     * @param int $quoteId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($quoteId)
    {
        $quoteModel = $this->_quoteFactory->create()->load($quoteId);
        $this->delete($quoteModel);
        return true;
    }
}
