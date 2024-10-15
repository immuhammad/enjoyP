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

use Magento\Framework\App\Action\Context;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\StoreSwitcher;
use Magento\Store\Model\StoreSwitcherInterface;
use Magento\Framework\Session\Generic as Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Api\StoreCookieManagerInterface;
use Magento\Store\Model\StoreIsInactiveException;

class SwitchAction
{
    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context                             $context
     * @param \Webkul\SellerSubDomain\Helper\Data $data
     */
    public function __construct(
        Context $context,
        \Webkul\SellerSubDomain\Helper\Data $data,
        StoreRepositoryInterface $storeRepository,
        Session $session,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\App\Response\Redirect $redirect,
        StoreCookieManagerInterface $storeCookieManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        StoreSwitcherInterface $storeSwitcher = null
    ) {
        $this->response = $context->getResponse();
        $this->_helper = $data;
        $this->storeRepository = $storeRepository;
        $this->session = $session;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->storeCookieManager = $storeCookieManager;
        $this->storeManager = $storeManager;
        $this->storeSwitcher = $storeSwitcher ?: ObjectManager::getInstance()->get(StoreSwitcherInterface::class);
    }

    /**
     * Redirect to main domain
     *
     * @param  $request
     * @return void
     */
    public function aroundExecute(\Magento\Store\Controller\Store\SwitchAction $subject, $proceed)
    {
        if ($this->_helper->isModuleEnable() && !$this->_helper->isLocalDomainSettingEnable()) {
            $targetStoreCode = $this->request->getParam(
                \Magento\Store\Model\StoreManagerInterface::PARAM_NAME,
                $this->storeCookieManager->getStoreCodeFromCookie()
            );
            $fromStoreCode = $this->request->getParam('___from_store');

            $requestedUrlToRedirect = $this->redirect->getRedirectUrl();
            $redirectUrl = $requestedUrlToRedirect;

            $error = null;
            try {
                $fromStore = $this->storeRepository->get($fromStoreCode);
                $targetStore = $this->storeRepository->getActiveStoreByCode($targetStoreCode);
            } catch (StoreIsInactiveException $e) {
                $error = __('Requested store is inactive');
            } catch (NoSuchEntityException $e) {
                $error = __("The store that was requested wasn't found. Verify the store and try again.");
            }
            if ($error !== null) {
                $this->messageManager->addErrorMessage($error);
            } else {
                $redirectUrl = $this->storeSwitcher->switch($fromStore, $targetStore, $requestedUrlToRedirect);
            }
            $refParam = $this->session->getRefParam();
            if ($refParam!="" && !strrpos($redirectUrl, $refParam)!==false) {
                $redirectUrl .= $refParam;
            }
            //$this->session->getRefParam();
            $this->response->setRedirect($this->session->getRefParam());
        } else {
            $proceed();
        }
    }
}
