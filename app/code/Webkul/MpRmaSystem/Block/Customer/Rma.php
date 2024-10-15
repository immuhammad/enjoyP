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
namespace Webkul\MpRmaSystem\Block\Customer;

use Webkul\MpRmaSystem\Model\ResourceModel\Conversation\CollectionFactory;

class Rma extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var CollectionFactory
     */
    protected $conversationCollection;

    /**
     * @var Collection
     */
    protected $conversations;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param CollectionFactory $conversationCollection
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        CollectionFactory $conversationCollection,
        array $data = []
    ) {
        $this->mpRmaHelper            = $mpRmaHelper;
        $this->conversationCollection = $conversationCollection;
        parent::__construct($context, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('RMA Details'));
    }

    /**
     * Get Conversation
     *
     * @return null|Webkul\MpRmaSystem\Model\ResourceModel\Conversation\Collection
     */
    public function getAllConversations()
    {
        if (!$this->conversations) {
            $rmaId = $this->mpRmaHelper->getCurrentRmaId();
            $collection = $this->conversationCollection
                                ->create()
                                ->addFieldToFilter("rma_id", $rmaId)
                                ->setOrder("created_time", "desc");
            $this->conversations = $collection;
        }

        return $this->conversations;
    }

    /**
     * Preparee getLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllConversations()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mprmasystem.rma.conversations.pager'
            )->setCollection(
                $this->getAllConversations()
            );
            $this->setChild('pager', $pager);
            $this->getAllConversations()->load();
        }

        return $this;
    }

    /**
     * Get Page html
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Function for accessing helper
     *
     * @return obejct \Webkul\MpRmaSystem\Helper\Data
     */
    public function helper()
    {
        return $this->mpRmaHelper;
    }
}
