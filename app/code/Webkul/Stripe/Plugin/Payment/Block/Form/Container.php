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
namespace Webkul\Stripe\Plugin\Payment\Block\Form;

class Container
{
    /**
     * Plugin to remove stripe payment method at multishiiping checkout and retrieve available payment methods
     *
     * @param \Magento\Payment\Block\Form\Container $subject
     * @param array $result
     * @return array
     */
    public function afterGetMethods(\Magento\Payment\Block\Form\Container $subject, $result)
    {
        $methods = [];
        $methodList = $result;
        foreach ($methodList as $method) {
            if (!($method->getCode() == \Webkul\Stripe\Model\PaymentMethod::METHOD_CODE)) {
                $methods[] = $method;
            }
        }
        return $methods;
    }
}
