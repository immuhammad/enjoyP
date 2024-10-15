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
namespace Webkul\Mpquotesystem\Model\Notification;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;

class MpquotesystemConfigProvider
{

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $viewFileSystem;

    /**
     * @var \Webkul\Mpquotesystem\Model\QuotesFactory
     */
    protected $mpquotes;

    /**
     * @param \Magento\Backend\Model\Auth\Session       $authSession
     * @param FormKey                                   $formKey
     * @param \Magento\Framework\View\Asset\Repository  $viewFileSystem
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        FormKey $formKey,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
    ) {
        $this->authSession = $authSession;
        $this->formKey = $formKey;
        $this->viewFileSystem = $viewFileSystem;
        $this->_mpquote = $mpquotes;
    }

    /**
     * Return quote config data
     *
     * @return array
     */
    public function getConfig()
    {
        if ($this->isAdminLoggedIn()) {
            $defaultImageUrl = $this->viewFileSystem->getUrlWithParams(
                'Webkul_Mpquotesystem::images/icons_notifications.png',
                []
            );
            $output['formKey'] = $this->formKey->getFormKey();
            $output['image'] = $defaultImageUrl;
            $output['quoteNotification'] = $this->getQuoteNotificationData();
        }
        return $output;
    }

    /**
     * Return quote data for notification.
     *
     * @return array
     */
    protected function getQuoteNotificationData()
    {
        $quoteData = [];
        $quoteCollection = $this->_mpquote->create()->getCollection()
        ->addFieldToFilter('admin_pending_notification', ['neq' => 0]);

        if ($quoteCollection->getSize()) {
            foreach ($quoteCollection as $value) {
                $quoteData[] = [
                    'entity_id' => $value->getEntityId()
                ];
            }
        }

        return $quoteData;
    }

    /**
     * Check if customer is logged in
     *
     * @return bool
     */
    private function isAdminLoggedIn()
    {
        return (bool)$this->authSession->isLoggedIn();
    }
}
