<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubDomain\Block\Html\Header;

class Logo extends \Magento\Theme\Block\Html\Header\Logo
{
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageHelper,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->escaper = $escaper;
        parent::__construct(
            $context,
            $fileStorageHelper,
            $data
        );
    }

    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Webkul_SellerSubDomain::html/header/logo.phtml';
}
