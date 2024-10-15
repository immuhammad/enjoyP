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
namespace Webkul\MpVendorAttributeManager\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Json\Helper\Data;
use Magento\Store\Model\ScopeInterface;

class Condition extends Template
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var $filterProvider
     */
    protected $filterProvider;

    /**
     * @var $storeManager
     */
    protected $storeManager;

    /**
     * @param Context $context
     * @param Data $jsonHelper
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $jsonHelper,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->scopeConfig = $context->getScopeConfig();
        $this->helper = $helper;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve information from module's configuration settings.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'marketplace/termcondition/'.$field;
        return preg_replace("/<script>.+?<\/script>/i", "", $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        ) ?? '');
    }

    /**
     * Retrieve Condition Data from module's configuration settings.
     *
     * @return void|false|string
     */
    public function getConditionData()
    {
        if ($this->getConfigData('termcondition') != '') {
            return $this->getConfigData('termcondition');
        }
        return false;
    }

    /**
     * Retrieve Privacy Data from module's configuration settings.
     *
     * @return void|false|string
     */
    public function getPrivacyData()
    {
        if ($this->getConfigData('privacy') != '') {
            return $this->getConfigData('privacy');
        }
        return false;
    }

    /**
     * Get Condition Content on the basis of Condition and Privacy Data
     *
     * @return void|false|string
     */
    public function getConditionContent()
    {
        $condition = $this->getConditionData();
        $privacy = $this->getPrivacyData();
        $conditionData = '';
        $privacyData = '';
        if ($condition !== false) {
            if ($this->getConfigData('is_html')) {
                $conditionData = $condition;
            } else {
                $conditionData = nl2br($this->escapeHtml($condition));
            }
        }

        if ($privacy !== false) {
            if ($this->getConfigData('is_html')) {
                $privacyData = $privacy;
            } else {
                $privacyData = nl2br($this->escapeHtml($privacy));
            }
        }
        $conditionData = preg_replace("/<script>.+?<\/script>/i", "", $conditionData);
        $privacyData = preg_replace("/<script>.+?<\/script>/i", "", $privacyData);

        $storeId = $this->storeManager->getStore()->getId();
        try {
            $conditionData = htmlspecialchars_decode($conditionData, ENT_QUOTES);
            $conditionData = $this->filterProvider->getBlockFilter()
                ->setStoreId($storeId)
                ->filter($conditionData);
        } catch (\Exception $ex) {
            $ex->getMessage();
        }

        try {
            $privacyData = htmlspecialchars_decode($privacyData, ENT_QUOTES);
            $privacyData = $this->filterProvider->getBlockFilter()
                ->setStoreId($storeId)
                ->filter($privacyData);
        } catch (\Exception $ex) {
            $ex->getMessage();
        }

        return $this->jsonHelper->jsonEncode(
            [
                'condition' => $conditionData,
                'privacy' => $privacyData,
                'privacyheading' => preg_replace("/<script>.+?<\/script>/i", "", $this->getModalHeading()),
                'termheading' => preg_replace("/<script>.+?<\/script>/i", "", $this->getConfigData('term_heading')),
                'animate' => preg_replace("/<script>.+?<\/script>/i", "", $this->getConfigData('animate')),
                'buttontitle' => preg_replace("/<script>.+?<\/script>/i", "", $this->getConfigData('button_text'))
            ]
        );
    }

    /**
     * Retrieve Privacy Heading from module's configuration settings.
     *
     * @return void|false|string
     */
    public function getModalHeading()
    {
        if ($this->getConfigData('privacy_heading') != '') {
            return $this->getConfigData('privacy_heading');
        }
        return false;
    }

    /**
     * Function get Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
