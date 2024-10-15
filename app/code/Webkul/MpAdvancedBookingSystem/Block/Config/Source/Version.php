<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvancedBookingSystem\Block\Config\Source;

class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    private $readFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Get Module Current version
     *
     * @param string $moduleName
     * @return string
     */
    public function getModuleVersion($moduleName)
    {
        $path = $this->componentRegistrar->getPath(
            \Magento\Framework\Component\ComponentRegistrar::MODULE,
            $moduleName
        );
        $directoryRead = $this->readFactory->create($path);
        $composerJsonData = $directoryRead->readFile('composer.json');
        $data = $this->jsonHelper->jsonDecode($composerJsonData);

        return !empty($data['version']) ? $data['version'] : __('Read error!');
    }
    
    /**
     * Get Html Element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $moduleCode = 'Webkul_MpAdvancedBookingSystem';

        $html = '<div class="mpadvancebooking-version">
        <p>'.__('Author').': 
        <a target="_blank" title="Webkul Software Private Limited" href="https://webkul.com/">Webkul</a></p>
        <p>'.__('Version').': '.$this->getModuleVersion($moduleCode).'</p>
        <p>'.__('User Guide').': 
        <a target="_blank" href="https://webkul.com/blog/magento2-multi-vendor-rental-event-appointment-hotel-booking/">
        Click Here
        </a></p>
        <p>'.__('Store Extension').': 
        <a target="_blank" href="https://store.webkul.com/magento2-marketplace-advanced-booking.html">Click Here</a></p>
        <p>'.__('Ticket/Customisations').': 
        <a target="_blank" href="https://webkul.uvdesk.com/en/customer/create-ticket/">Click Here</a></p>
        <p>'.__('Services').': 
        <a target="_blank" href="https://webkul.com/magento-development/">Click Here</a></p></div>';
        return $html;
    }
}
