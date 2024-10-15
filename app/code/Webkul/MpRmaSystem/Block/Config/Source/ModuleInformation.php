<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Block\Config\Source;

class ModuleInformation extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var \Magento\Framework\Component\ComponentRegistrarInterface
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Framework\Component\ComponentRegistrarInterface $componentRegistrar,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory        = $readFactory;
        $this->jsonHelper         = $jsonHelper;
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
        $moduleCode = 'Webkul_MpRmaSystem';

        $html = '<div class="wk-module-info">
        <p>'.__("Author:").' '.'
        <a target="_blank" title="Webkul Software Private Limited" href="https://webkul.com/">'.__("Webkul").'</a></p>
        <p>'.__("Version:").' '.$this->getModuleVersion($moduleCode).'</p>
        <p>'.__("User Guide:").' '.' 
        <a target="_blank" href="http://webkul.com/blog/magento2-marketplace-rma-system/">
        '.__("Click Here").'</a></p>
        <p>'.__("Store Extension:").' '.' 
        <a target="_blank" href="https://store.webkul.com/magento2-marketplace-vendor-rma.html">
        '.__("Click Here").'</a></p>
        <p>'.__("Ticket/Customisations:").' '.' 
        <a target="_blank" href="https://webkul.uvdesk.com/en/customer/create-ticket/">'.__("Click Here").'</a></p>
        <p>'.__("Services:").' '.' 
        <a target="_blank" href="https://webkul.com/magento-development/">'.__("Click Here").'</a></p></div>';
        return $html;
    }
}
