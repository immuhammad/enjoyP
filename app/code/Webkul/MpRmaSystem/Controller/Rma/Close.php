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
namespace Webkul\MpRmaSystem\Controller\Rma;

use Magento\Framework\App\Action\Context;
use Webkul\MpRmaSystem\Helper\Data;

class Close extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @return void
     */
    public function __construct(
        Context $context,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details
    ) {
        $this->mpRmaHelper = $mpRmaHelper;
        $this->details     = $details;
        parent::__construct($context);
    }

    /**
     * Close Rma Action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $helper = $this->mpRmaHelper;
        if (array_key_exists("is_guest", $data)) {
            if (!$helper->isGuestLoggedIn()) {
                return $this->resultRedirectFactory
                            ->create()
                            ->setPath('mprmasystem/guest/login');
            }
        } else {
            if (!$helper->isLoggedIn()) {
                return $this->resultRedirectFactory
                            ->create()
                            ->setPath('customer/account/login');
            }
        }

        if (array_key_exists("close_rma", $data)) {
            $rmaData = ['status' => Data::RMA_STATUS_SOLVED, 'final_status' => Data::FINAL_STATUS_CLOSED];
            $rmaId = $data['rma_id'];
            $rma = $this->details->create()->load($rmaId);
            $rma->addData($rmaData)->setId($rmaId)->save();
            $helper->sendUpdateRmaEmail($data);
        }

        if (array_key_exists("is_guest", $data)) {
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/guest/rma',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
        } else {
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/customer/rma',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
        }
    }
}
