<?php
namespace Consultera\Seller\Block;
class Index extends \Magento\Framework\View\Element\Template
{
    public function _prepareLayout()  
    {  
    
       $this->pageConfig->getTitle()->set(__('Enjoy Palestine | Merchant'));  
    
       return parent::_prepareLayout();  
    } 
}
