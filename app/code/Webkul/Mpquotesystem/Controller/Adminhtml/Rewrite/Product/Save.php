<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Adminhtml\Rewrite\Product;

use Zend\Stdlib\Parameters;
use Magento\Framework\Validator\NotEmptyFactory;
use Magento\Framework\App\ObjectManager;

class Save extends \Magento\Catalog\Controller\Adminhtml\Product\Save
{
    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Product\Builder $productBuilder
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper
     * @param \Magento\Catalog\Model\Product\Copier $productCopier
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Escaper|null $escaper
     * @param \Psr\Log\LoggerInterface|null $logger
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface|null $categoryLinkManagement
     * @param \Magento\Store\Model\StoreManagerInterface|null $storeManager
     * @param NotEmptyFactory|null $notEmptyValidatorFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $initializationHelper,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Escaper $escaper = null,
        \Psr\Log\LoggerInterface $logger = null,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement = null,
        \Magento\Store\Model\StoreManagerInterface $storeManager = null,
        NotEmptyFactory $notEmptyValidatorFactory = null
    ) {
        parent::__construct(
            $context,
            $productBuilder,
            $initializationHelper,
            $productCopier,
            $productTypeManager,
            $productRepository,
            $escaper,
            $logger,
            $categoryLinkManagement,
            $storeManager,
        );
        $this->notEmptyValidatorFactory = $notEmptyValidatorFactory ?: ObjectManager::getInstance()
            ->get(NotEmptyFactory::class);
    }

    /**
     * Save controller
     *
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $params = $this->getRequest()->getParams();

            if (($params['type'] == 'grouped') ||
            ($params['type'] == 'booking') ||
            ($params['type'] == 'hotelbooking')) {
                return parent::execute();
            } elseif ($params['product']['quote_status'] == 1 && empty($params['product']['min_quote_qty'])) {
                $validator = $this->notEmptyValidatorFactory->create(['options' => []]);
                if (!$validator->isValid(trim($params['product']['min_quote_qty']))) {
                    $error = true;
                }

                $this->handleValidationError($error);
            } else {
                $minQuoteQty = $params['product']['min_quote_qty'];
                if ($minQuoteQty == 0) {
                    $postParams = $this->getRequest()->getPostValue();
                    $updatedPostParams = [];
                    foreach ($postParams['product'] as $key => $value) {
                        if ($key == 'min_quote_qty') {
                            $value = '0';
                        }
                        $updatedPostParams['product'][$key] = $value;
                    }
                    $postParams['product'] = $updatedPostParams['product'];
                    $this->getRequest()->setPost(new Parameters($postParams));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Mimimum Quote Quantity cannot be empty if Quote Status is enabled.')
            );
            
            if (isset($params['id'])) {
                $resultRedirect->setPath('catalog/product/edit/id/'.$params['id']);
            } else {
                $resultRedirect->setPath('catalog/product/new/set/'.$params['set'].'/type/'.$params['type']);
            }
        }
        return parent::execute();
    }

    /**
     * HandleValidationError
     *
     * @param bool $error
     * @return void
     */
    public function handleValidationError($error)
    {
        if ($error) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Mimimum Quote Quantity cannot be empty if Quote Status is enabled')
            );
        }
    }
}
