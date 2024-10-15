<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_stripe
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Api;
 
interface WebhookInterface
{
    /**
     * Handle stripe webhook request
     *
     * @api
     * @return void
     */
    public function executeWebhook();
}
