<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpquotesystem\Block\Adminhtml;

class Product extends \Magento\Backend\Block\Template
{

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\Mpquotesystem\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Mpquotesystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Return min qty
     *
     * @return int
     */
    public function getMinQty()
    {
        $minQty = $this->helper->getGlobalMinQty();
        return $minQty;
    }
}
