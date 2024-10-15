<?php
/**
 * Webkul Software
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Block;
  
use Magento\Framework\Registry;
use Magento\Backend\Block\Template\Context;
  
class Prefix extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var  Registry
     */
    protected $_coreRegistry;
  
    /**
     * @param Context  $context
     * @param Registry $coreRegistry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Webkul\SellerSubDomain\Helper\Data $ssdHelper,
        array $data = []
    ) {
        $this->_helper = $ssdHelper;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }
  
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = $element->getElementHtml();
        $baseurl = $this->_helper->getBaseUrlWithoutSsl();
        $html .= '<script type="text/javascript">
            require(["jquery", "jquery/ui"], function (jq) {
                jq(document).ready(function () {
                    jq("#' . $element->getHtmlId() . '").css("width", "30%");
                    jq("#' . $element->getHtmlId() . '").after(" shop_url.'.$baseurl.'");
                    jq("#' . $element->getHtmlId() . '").before("http:// ");
                });
            });
            </script>';
        return $html;
    }
}
