<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Consultera\Ambasdor\Model\Customer\Attribute\Source;

class CustomerType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * getAllOptions
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [
                ['value' => '1', 'label' => __(' ')],
                ['value' => '6', 'label' => __('Influencer')],
                ['value' => '5', 'label' => __('Affiliate')],
                ['value' => '7', 'label' => __('Reel')]
            ];
        }
        return $this->_options;
    }
}

