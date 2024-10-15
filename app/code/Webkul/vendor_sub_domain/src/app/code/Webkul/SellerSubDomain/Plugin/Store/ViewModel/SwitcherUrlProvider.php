<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Webkul\SellerSubDomain\Plugin\Store\ViewModel;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use \Magento\Customer\Model\SessionFactory;
use Magento\Framework\Url\DecoderInterface;

/**
 * Provides target store redirect url.
 */
class SwitcherUrlProvider implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param EncoderInterface $encoder
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        EncoderInterface $encoder,
        DecoderInterface $dcoder,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Webkul\SellerSubDomain\Helper\Data $helper,
        \Webkul\SellerSubDomain\Model\DomainFactory $_domainFactory,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->encoder = $encoder;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->helper = $helper;
        $this->_domainFactory = $_domainFactory;
        $this->request = $request;
        $this->urlInterface = $urlInterface;
        $this->dcoder = $dcoder;
    }

    /**
     * Returns target store redirect url.
     *
     * @param Store $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetTargetStoreRedirectUrl(
        \Magento\Store\ViewModel\SwitcherUrlProvider $subject,
        $result,
        Store $store
    ) {
        $domainData = $this->_domainFactory->create()
                           ->getCollection()
                           ->addFieldTofilter(
                               "vendor_url",
                               $this->urlInterface->getBaseUrl()
                           );
        if ($this->helper->isModuleEnable() &&
            $this->helper->isLocalDomainSettingEnable() &&
            count($domainData)
        ) {
            $currentUrl = $this->urlInterface->getCurrentUrl();
            $baseUrl = $this->urlInterface->getBaseUrl();
            $extension = explode($baseUrl, $currentUrl);
            $seller_id = $this->helper->getProfileDetail()->getSellerId();
            $store_id = $store->getId();
            $domainData = $this->_domainFactory->create()
                            ->getCollection()
                            ->addFieldTofilter('seller_id', $seller_id)
                            ->addFieldToFilter('vendor_store_id', $store_id)
                            ->setPageSize(1)
                            ->getFirstItem();
            return $domainData['vendor_url'].$extension[1];
        }

        if ($this->helper->isModuleEnable() &&
        !$this->helper->isLocalDomainSettingEnable()
        ) {
            $encoded = $this->encoder->encode($this->urlInterface->getCurrentUrl());
            return $result."wk_uenc/".$encoded;
        }

        return $result;
    }
}
