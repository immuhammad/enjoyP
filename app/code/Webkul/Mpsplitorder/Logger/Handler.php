<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitorder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpsplitorder\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level.
     * @var int
     */
    public $loggerType = Mpsplitorder::INFO;

    /**
     * File name.
     * @var string
     */
    public $fileName = '/var/log/mpsplitorder.log';
}
