<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpquotesystem\Block\Adminhtml;

class Notification extends \Magento\Backend\Block\Template
{

    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    protected $configProvider;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Webkul\Mpquotesystem\Model\Notification\MpquotesystemConfigProvider $configProvider
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Webkul\Mpquotesystem\Model\Notification\MpquotesystemConfigProvider $configProvider,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        array $data = []
    ) {
        $this->configProvider = $configProvider;
        $this->jsonSerializer = $jsonSerializer;
        parent::__construct($context, $data);
    }

    /**
     * Return quote config data
     *
     * @return array
     */
    public function getNotificationConfig()
    {
        $configData = $this->configProvider->getConfig();
        return $configData;
    }

    /**
     * Return quote config data
     *
     * @return array
     */
    public function getJsonSerializer()
    {
        return $this->jsonSerializer;
    }
}
