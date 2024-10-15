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

namespace Webkul\Mpquotesystem\Ui\Component\Listing\Column\Quote\Status;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class QuoteOptions used to provide various quote status with labels
 */
class QuoteOptions implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = $this->getOptionArray();
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }

    /**
     * GetOptionArray
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            \Webkul\Mpquotesystem\Model\Quotes::STATUS_UNAPPROVED => __('Unapproved'),
            \Webkul\Mpquotesystem\Model\Quotes::STATUS_APPROVED => __('Approved'),
            \Webkul\Mpquotesystem\Model\Quotes::STATUS_DECLINE => __('Declined'),
            \Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD => __('Sold')
        ];
    }
}
