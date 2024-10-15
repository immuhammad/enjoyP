<?php
/**
 * Webkul Affiliate customer tabs.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form\Generic;
use Webkul\Affiliate\Model\Config\Source\CommissionType;
use Webkul\Affiliate\Model\UserFactory;

/**
 * Customer Seller form block.
 */
class Tabs extends Generic implements TabInterface
{
    /**
     * @var \Webkul\Affiliate\Model\Config\Source\CommissionType
     */
    private $commissionType;
    
    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context,
     * @param \Magento\Framework\Registry             $registry,
     * @param \Magento\Framework\Data\FormFactory     $formFactory,
     * @param MpEbayHelperData                        $mpEbayHelper,
     * @param SellerFactory                           $sellerFactory,
     * @param EbayGlobalSitesList                     $ebayGlobalSitesList,
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        UserFactory $userFactory,
        CommissionType $commissionType,
        array $data = []
    ) {
        $this->commissionType = $commissionType;
        $this->userFactory = $userFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Affiliate Detail');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Affiliate Detail');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    public function initForm()
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**
         * @var \Magento\Framework\Data\Form $form
         */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('affiliate_');
        $customerId = $this->_coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Affiliate Information')]);
        $fieldset->addField(
            'enable',
            'select',
            [
                'name' => 'affiliate[enable]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Affiliate Enable'),
                'title' => __('Affiliate Enable'),
                'values' => ['0' => 'No', '1'=> 'Yes']
            ]
        );
        
        $fieldset->addField(
            'pay_per_click',
            'text',
            [
                'name' => 'affiliate[pay_per_click]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Pay Per Click'),
                'title' => __('Pay Per Click'),
                'class' => 'validate-number'
            ]
        );

        $fieldset->addField(
            'pay_per_unique_click',
            'text',
            [
                'name' => 'affiliate[pay_per_unique_click]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Pay Per Unique Click'),
                'title' => __('Pay Per Unique Click'),
                'class' => 'validate-number'
            ]
        );

        $fieldset->addField(
            'commission_type',
            'select',
            [
                'name' => 'affiliate[commission_type]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Commission Type'),
                'title' => __('Commission Type'),
                'values' => $this->commissionType->getAllOptions()
            ]
        );

        $fieldset->addField(
            'commission',
            'text',
            [
                'name' => 'affiliate[commission]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Commission'),
                'title' => __('Commission'),
                'class' => 'validate-number'
            ]
        );
        
        $fieldset->addField(
            'blog_url',
            'text',
            [
                'name' => 'affiliate[blog_url]',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Blog URL'),
                'title' => __('Blog URL'),
                'class' => 'validate-url'
            ]
        );

        if ($customerId) {
            $affUser = $this->userFactory->create()
                                            ->getCollection()->addFieldToFilter('customer_id', $customerId)
                                            ->setPageSize(1)->setCurPage(1)->getFirstItem()->getData();
            $form->setValues($affUser);
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
