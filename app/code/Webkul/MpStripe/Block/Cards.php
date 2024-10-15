<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block;

use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * MpStripe block.
 *
 * @author Webkul Software
 */
class Cards extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;
    /**
     * @var Webkul\Stripe\Model\ResourceModel\StripeCustomer\CollectionFactory
     */
    private $stripeCustomerFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context                    $context
     * @param \Webkul\Marketplace\Helper\Data                                     $helper
     * @param array                                                               $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpStripe\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * GetSavedCards get customer saved cards.
     *
     * @return Webkul\Stripe\Model\StripeCustomer
     */
    public function getSavedCards()
    {
        return $this->helper->getSavedCards();
    }

    /**
     * GetStripeHelper function
     *
     * @return Object
     */
    public function getStripeHelper()
    {
        return $this->helper;
    }
}
