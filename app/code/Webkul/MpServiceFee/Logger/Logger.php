<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Logger;

class Logger extends \Monolog\Logger
{
    /**
     * @param string             $name       The logging channel
     * @param HandlerInterface[] $handlers   Optional stack of handlers
     * @param callable[]         $processors Optional array of processors
     */
    public function __construct($name, $handlers = [], $processors = [])
    {
        $this->name = $name;
        $this->handlers = $handlers;
        $this->processors = $processors;
    }
}
