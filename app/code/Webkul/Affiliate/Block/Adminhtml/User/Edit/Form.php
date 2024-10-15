<?php
/**
 * Webkul Affiliate User EmailNotify Form
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Block\Adminhtml\User\Edit;

/**
 * Adminhtml MpEbaymagentoconnect Map Category Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwygConfig;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Magento\Framework\Data\FormFactory     $formFactory
     * @param \Magento\Store\Model\System\Store       $systemStore
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\Affiliate\Model\UserFactory $userFactory,
        array $data = []
    ) {
    
        $this->wysiwygConfig = $wysiwygConfig;
        $this->userFactory = $userFactory;
        $this->customerRepository = $customerRepository;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form.
     *
     * @return $this
     */
    public function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                            'id' => 'edit_form',
                            'enctype' => 'multipart/form-data',
                            'action' => $this->getData('action'),
                            'method' => 'post', ],
                        ]
        );
        $form->setHtmlIdPrefix('affiliate_email_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Send Mail To Affiliate'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'affiliate_user',
            'select',
            [
                'name' => 'affiliate_user',
                'label' => __('Affiliate User'),
                'id' => 'affiliate_user',
                'title' => __('Affiliate User'),
                'values' => $this->_getAffiliateUserList(),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'email_subject',
            'text',
            [
                'name' => 'email_subject',
                'label' => __('Subject'),
                'id' => 'email_subject',
                'title' => __('Subject'),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        
        $fieldset->addField(
            'email_content',
            'editor',
            [
                'name' => 'email_content',
                'label' => __('Message'),
                'id' => 'email_content',
                'title' => __('Email Content'),
                'config' => $this->wysiwygConfig->getConfig(),
                'required' => true,
                'rows' => 7
            ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * getAffiliateUserList
     * @var $affiliateUserList \Magento\Customer\Api\CustomerRepositoryInterface
     * @return array
     */
    private function _getAffiliateUserList()
    {
        $affiliateUserList = $this->userFactory->create()->getCollection();
        $userArr[] = ['value' => 'all', 'label'=> __('All Affiliate User')];
        foreach ($affiliateUserList as $affUser) {
            $customer = $this->customerRepository->getById($affUser->getCustomerId());
            $name = $customer->getFirstName().' '.$customer->getLastName().' ('.$customer->getEmail().')';
            $userArr[] = ['value'=> $affUser->getCustomerId(), 'label' => $name];
        }
        return $userArr;
    }
}
