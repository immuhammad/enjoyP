<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Stripe\Block\Payment;

/**
 * Base payment information block
 */
class Info extends \Magento\Payment\Block\Info
{
    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get some specific information in format of array($label => $value)
     *
     * @return array
     */
    public function getSpecificInformation()
    {
        $stripeType = [];
        $additionalInformation = $this->getInfo()->getData('additional_information');
        if (isset($additionalInformation['paymentSource']) && $additionalInformation['paymentSource'] == 'customer') {
            if (isset($additionalInformation['additional_information']['stripe_charge'])) {
                $savedStripsCardDetail = $additionalInformation['additional_information']['stripe_charge'];
                $source = $this->jsonHelper->jsonDecode($savedStripsCardDetail);
                $typeofStripePayment = array_values($this->jsonHelper->jsonDecode($source['source']))[1]['object'];
                $last4 = array_values($this->jsonHelper->jsonDecode($source['source']))[1]['last4'];
                $stripeType = ['type' => $typeofStripePayment." ****".$last4];
            }
        } elseif (isset($additionalInformation['stype'])) {
                $stripeType = ['type' => $additionalInformation['stype']];
        }
        return $this->_prepareSpecificInformation($stripeType)->getData();
    }
}
