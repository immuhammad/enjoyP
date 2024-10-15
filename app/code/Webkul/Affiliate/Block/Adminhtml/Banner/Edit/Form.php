<?php
/**
 * Webkul Affiliate Banner Form
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Block\Adminhtml\Banner\Edit;

use Webkul\Affiliate\Model\TextBannerFactory;

/**
 * Adminhtml Affiliate Banner Form.
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwygConfig;

    /**
     * @var \Magento\Cms\Model\PageFactory
     */
    private $pageFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Framework\Url
     */
    private $urlHelper;

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
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Url $urlHelper,
        TextBannerFactory $textBanner,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->pageFactory = $pageFactory;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->textBanner = $textBanner;
        $this->urlHelper = $urlHelper;
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
                    'method' => 'post'
                ]
            ]
        );
        $form->setHtmlIdPrefix('affiliate_email_');

        $bannerId = $this->getRequest()->getParam('id');
        $model = $this->textBanner->create()->load($bannerId);

        if ($model->getEntityId()) {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Edit Banner'), 'class' => 'fieldset-wide']
            );
            $fieldset->addField('entity_id', 'hidden', ['name' => 'entity_id']);
        } else {
            $fieldset = $form->addFieldset(
                'base_fieldset',
                ['legend' => __('Add Banner'), 'class' => 'fieldset-wide']
            );
        }

        $fieldset->addField(
            'banner_title',
            'text',
            [
                'name' => 'title',
                'label' => __('Banner Title'),
                'id' => 'banner_title',
                'title' => __('Banner Title'),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'banner_text',
            'editor',
            [
                'name' => 'text',
                'label' => __('Content'),
                'id' => 'banner_text',
                'title' => __('Content'),
                'config' => $this->wysiwygConfig->getConfig(),
                'required' => true,
                'rows' => 7
            ]
        );

        $fieldset->addField(
            'banner_link',
            'select',
            [
                'name' => 'link',
                'label' => __('Link'),
                'id' => 'banner_link',
                'title' => __('Link'),
                'values' => $this->_getCmsPageList(),
                'class' => 'required-entry',
                'required' => true,
            ]
        );

        $fieldset->addField(
            'banner_size',
            'select',
            [
                'name' => 'banner_size',
                'label' => __('Size'),
                'id' => 'banner_size',
                'title' => __('Size'),
                'class' => 'required-entry',
                'values' => $this->_getBannerSizeList(),
                'required' => true,
            ]
        );

        $tmpData = $model->getData();
        if (!empty($tmpData)) {
            $data = [
                'entity_id' => $tmpData['entity_id'],
                'banner_title' => $tmpData['title'],
                'banner_text' => $tmpData['text'],
                'banner_link' => $tmpData['link'],
                'banner_size' => $tmpData['banner_size']
            ];
            $form->setValues($data);
        }
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * getAffiliateUserList
     * @var $affiliateUserList \Magento\Customer\Api\CustomerRepositoryInterface
     * @return array
     */
    private function _getCmsPageList()
    {
        $pageList = $this->pageFactory->create()->getCollection();
        $linkArr[] = ['value' => '', 'label'=> __('Select Page')];
        $temp = [];
        foreach ($pageList as $page) {
            $temp[] = ['value'=> $this->getBaseUrl().$page->getIdentifier(), 'label' => $page->getTitle()];
        }
        $linkArr[] = ['label' => __('Cms Pages'), 'value' => $temp];

        $collection = $this->_productCollectionFactory->create();
        $collection =  $collection->addAttributeToSelect('name');
        $productList = $collection->addAttributeToSelect('product_url');
        $productList = $collection->addFieldToFilter(
            'visibility',
            ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE]
        );
        $temp = [];
        foreach ($productList as $product) {
            $productUrl = $product->getProductUrl();
            $productUrlArr = explode('catalog/product/view', $productUrl);
            if (!empty($productUrlArr[1])) {
                $productUrl = $this->urlHelper->getUrl(
                    'catalog/product/view',
                    [
                        '_scope' => 1,
                        'id' => $product->getId(),
                        '_nosid' => true
                    ]
                );
            }
            $temp[] = ['value'=> $productUrl, 'label' => $product->getName()];
        }

        $linkArr[] = ['label' => __('Product Link'), 'value' => $temp];
        return $linkArr;
    }

    /**
     * getAffiliateUserList
     * @var $affiliateUserList \Magento\Customer\Api\CustomerRepositoryInterface
     * @return array
     */
    private function _getBannerSizeList()
    {
        $bannerSize = [
            ['value' => 'auto', 'label' => __('∞ (Fluid)')],
            ['value' => '88x31', 'label' => __('88x31 (Micro Bar)')],
            ['value' => '240x120', 'label' => __('240x120 (Vertical Banner)')],
            ['value' => '160x600', 'label' => __('160x600 (Wide Skyscraper)')],
            ['value' => '234x60', 'label' => __('234x60 (Half Banner)')],
            ['value' => '300x600', 'label' => __('300x600 (Half Page Ad)')],
            ['value' => '468x60', 'label' => __('468x60 (Full Banner)')],
            ['value' => '728x90', 'label' => __('728x90 (Leaderboard)')]
        ];
        
        return [['label' => __('Select Size'),'value' => $bannerSize]];
    }
}
