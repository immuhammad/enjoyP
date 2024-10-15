<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Reasons;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Using for Rma admin resource
     */
    public const ADMIN_RESOURCE = 'Webkul_MpRmaSystem::reasons';

    /**
     * @var Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Webkul\MpRmaSystem\Model\ResourceModel\Region\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param Filter $filter
     * @param \Webkul\MpRmaSystem\Model\ResourceModel\Reasons\CollectionFactory $collectionFactory
     * @return void
     */
    public function __construct(
        Context $context,
        Filter $filter,
        \Webkul\MpRmaSystem\Model\ResourceModel\Reasons\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Using for Mass Delete Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collection->walk('delete');
        $this->messageManager->addSuccess(__('Selected Reason(s) deleted successfully'));
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/reasons/');
    }
}
