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
namespace Webkul\MpStripe\Api;
 
interface WebhookInterface
{
    /**
     * Handle stripe webhook request
     *
     * @api
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function executeWebhook();
}
