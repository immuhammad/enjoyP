<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Customer\Editor;

class TextareaEditor extends \Magento\Framework\Data\Form\Element\Textarea
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }
    
    /**
     * Retrieve additional html and put it at the end of element html
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $config = $this->_wysiwygConfig->getConfig();
        $config = json_encode($config->getData());
        $html = parent::getAfterElementHtml();
        if ($this->getIsWysiwygEnabled()) {
            $html .= "<<<HTML
          <script>
          require([
              'jquery',
              'mage/adminhtml/wysiwyg/tiny_mce/setup'
          ], function(jQuery){

          var config = $config,
              editor;

          jQuery.extend(config, {
              settings: {
                  theme_advanced_buttons1 : 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
                      'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
                  theme_advanced_buttons2: null,
                  theme_advanced_buttons3: null,
                  theme_advanced_buttons4: null,
                  theme_advanced_statusbar_location: null
              },
              files_browser_window_url: false
          });

          editor = new tinyMceWysiwygSetup(
              '{$this->getHtmlId()}',
              config
          );

          editor.turnOn();

          jQuery('#{$this->getHtmlId()}')
              .addClass('wysiwyg-editor')
              .data(
                  'wysiwygEditor',
                  editor
              );
          });
          </script>
          HTML";
        }
        return $html;
    }

    /**
     * Check whether wysiwyg enabled or not
     *
     * @return bool
     */
    public function getIsWysiwygEnabled()
    {
        return true;
    }
}
