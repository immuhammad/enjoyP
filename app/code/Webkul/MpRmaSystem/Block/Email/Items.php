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
namespace Webkul\MpRmaSystem\Block\Email;

use Webkul\MpRmaSystem\Model\ResourceModel\Conversation\CollectionFactory;

class Items extends \Magento\Framework\View\Element\Template
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
     * Get All Items
     *
     * @return array | \Webkul\MpRmaSystem\Helper\Data
     */
    public function getAllItems()
    {
        return $this->mpRmaHelper->getAllItems();
    }

    /**
     * Get Helper
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function helper()
    {
        return $this->mpRmaHelper;
    }
}
