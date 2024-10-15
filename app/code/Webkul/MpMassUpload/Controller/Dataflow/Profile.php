<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpMassUpload
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpMassUpload\Controller\Dataflow;

class Profile extends \Webkul\MpMassUpload\Controller\Dataflow\AbstractProfile
{
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        if ($this->marketplaceHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('mpmassupload_layout2_dataflow_profile');
        }
        $resultPage->getConfig()->getTitle()->set(__('Marketplace Mass Upload Dataflow Profile'));
        return $resultPage;
    }
}
