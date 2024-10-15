<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Block\Adminhtml\Sales\Items\Column;

class Name
{
    /**
     * Around plugin of getFormattedOption function
     *
     * @param \Magento\Sales\Block\Adminhtml\Items\Column\Name $subject
     * @param \Closure                                         $proceed
     * @param string                                           $value
     *
     * @return void
     */
    public function aroundGetFormattedOption(
        \Magento\Sales\Block\Adminhtml\Items\Column\Name $subject,
        \Closure $proceed,
        $value
    ) {
        $remainder = '';
        if (strpos($value, 'attachment') === false) {
            return $proceed($value);
        } else {
            $result = ['value' => nl2br($value), 'remainder' => nl2br($remainder)];
            return $result;
        }
    }
}
