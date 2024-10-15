<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubDomain\Plugin\Store\Controller\Store;

use Magento\Store\Model\StoreResolver;

class Redirect
{
    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Webkul\SellerSubDomain\Helper\Data $helepr
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Webkul\SellerSubDomain\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\Url\DecoderInterface $dcoder,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        // \Magento\Framework\App\Response\RedirectInterface $resultRedirect
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->_helper = $helper;
        $this->_request = $request;
        $this->_urlInterface = $urlInterface;
        $this->dcoder = $dcoder;
        $this->storeRepository = $storeRepository;
        // $this->resultRedirect = $resultRedirect;
        // $this->_response = $context->getResponse();
        $this->session = $session;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param \Magento\Store\Controller\Store\Redirect $subject
     * @param $result
     * @return string
     */
    public function aroundExecute(
        \Magento\Store\Controller\Store\Redirect $subject,
        callable $proceed
    ) {
        if ($this->_helper->isModuleEnable() &&
        !$this->_helper->isLocalDomainSettingEnable()
        ) {
            $decodedTargetUrl = $this->dcoder->decode($subject->getRequest()->getParam('wk_uenc'));
            $currentUrl = $this->_urlInterface->getCurrentUrl();
            $targetStoreCode = $subject->getRequest()->getParam('___store');
            $fromStoreCode = $subject->getRequest()->getParam('___from_store');

            $currentStoreId= $this->storeRepository->get($fromStoreCode);
            $targetStoreId= $this->storeRepository->get($targetStoreCode);

            $currentPrefix = $this->_helper->getPrefixByStoreId($currentStoreId);
            $targetPrefix = $this->_helper->getPrefixByStoreId($targetStoreId)??$this->_helper->getPrefix();
            $redirectUrl = str_replace($currentPrefix, $targetPrefix, $decodedTargetUrl);
            $this->_helper->getMpHelper()->setCurrentStore($targetStoreId);

            $this->session->setRefParam($redirectUrl);
        }
        $proceed();
    }
}
