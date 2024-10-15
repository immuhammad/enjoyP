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
namespace Webkul\MpVendorAttributeManager\Controller\Adminhtml\Attribute;

use Magento\Eav\Model\Entity;
use Magento\Customer\Model\AttributeFactory;
use Magento\Customer\Model\Customer;

class Validate extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;
    
    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $eavEntity;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var string
     */
    protected $_customerEntityTypeId;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Cache\FrontendInterface $attributeLabelCache
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param Entity $eavEntity
     * @param AttributeFactory $attributeFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Cache\FrontendInterface $attributeLabelCache,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        Entity $eavEntity,
        AttributeFactory $attributeFactory
    ) {
        parent::__construct($context, $attributeLabelCache, $coreRegistry, $resultPageFactory);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->eavEntity = $eavEntity;
        $this->attributeFactory = $attributeFactory;
    }
    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::index');
    }
    /**
     * Dispatch request.
     *
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $this->_customerEntityTypeId = $this->eavEntity->setType(Customer::ENTITY)->getTypeId();

        return parent::dispatch($request);
    }

    /**
     * Execute action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        
        $attributeId = $this->getRequest()->getParam('attribute_id');

        $attribute = $this->attributeFactory->create()->loadByCode(
            $this->_customerEntityTypeId,
            $attributeCode
        );

        if ($attribute->getId() && !$attributeId) {
            if (!empty($this->getRequest()->getParam('attribute_code'))) {
                $response->setMessage(
                    __('An attribute with this code already exists.')
                );
            } else {
                $response->setMessage(
                    __('An attribute with the same code (%1) already exists.', $attributeCode)
                );
            }
            $response->setError(true);
        }
        return $this->resultJsonFactory->create()->setJsonData($response->toJson());
    }
}
