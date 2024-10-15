<?php
/**
 * Webkul Software.
 *
 * @category   Webkul
 * @package    Webkul_MpServiceFee
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Controller\Fees;

use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Controller\Adminhtml\AbstractAction;

class Render extends AbstractAction
{
    
    /**
     * Execute
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            \Magento\Backend\Model\View\Result\Redirect::render('admin/noroute');
            return;
        }
        $component = $this->factory->create($this->_request->getParam('namespace'));
        $this->prepareComponent($component);
        $this->_response->appendBody((string) $component->render());
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface  $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface  $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
