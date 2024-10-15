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

namespace Webkul\Mpquotesystem\Model\Config\Source;

class QuoteOption extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get attribute option
     *
     * @param string $store
     * @return array
     */
    public function toOptionArray($store = null)
    {
        $quoteStatus = [
                            ['value' => 2,'label' => (string)__('Default')],
                            ['value' => 0,'label' => (string)__(' Disabled')],
                            ['value' => 1,'label' => (string)__(' Enabled')],
                        ];

        return $quoteStatus;
    }

    /**
     * Get All Options
     *
     * @return array
     */
    public function getAllOptions()
    {
        $countryListData = $this->toOptionArray();
        if (!$this->_options) {
            $this->_options = $countryListData;
        }
        return $this->_options;
    }
}
