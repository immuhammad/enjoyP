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
namespace Webkul\SellerSubDomain\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul SellerSubDomain ConfigSaveAfter Observer.
 */
class ConfigSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\Marketplace\Helper\Data $mphelper
     */
    protected $_mphelper;

    /**
     * @var \Webkul\SellerSubDomain\Helper\Data $data
     */
    protected $_sshelper;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    protected $configWriter;

    /**
     * @var \Magento\Framework\Message\ManagerInterface $messageManager
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\App\RequestInterface               $request
     * @param \Webkul\Marketplace\Helper\Data                       $mphelper
     * @param \Webkul\SellerSubDomain\Helper\Data                   $sshelper
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     * @param \Magento\Framework\Message\ManagerInterface           $messageManager
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Webkul\SellerSubDomain\Helper\Data $sshelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository
    ) {
        $this->_request = $request;
        $this->_mphelper = $mphelper;
        $this->_sshelper = $sshelper;
        $this->configWriter = $configWriter;
        $this->messageManager = $messageManager;
        $this->_storeRepository = $storeRepository;
    }

    /**
     * Product delete after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->_sshelper->isModuleEnable()) {
                $this->disableUrlRewrite($observer);
                $data = $this->_request->getParams();
                if ($data['section'] === 'sellersubdomain') {
                    if ($this->_sshelper->getMpUrlRewrite() ||
                    $this->_sshelper->getMpAutoUrlRewrite()) {
                        $this->disableUrlRewrite($observer);
                    }
                } elseif ($data['section'] === 'marketplace') {
                    if ($this->_sshelper->getMpUrlRewrite() ||
                    $this->_sshelper->getMpAutoUrlRewrite()) {
                        $this->disableUrlRewrite($observer);
                        $this->messageManager->addNotice(
                            'Please disable seller subdomain to enable seller\'s shop url rewrite.'
                        );
                    }
                }
                $prefixData = !empty($data['groups']['settings']['fields']['prefix']) ?
                    $data['groups']['settings']['fields']['prefix'] : null;
                if (!empty($prefixData["value"])) {
                    $prefix = $data['groups']['settings']['fields']['prefix']['value'];
                    if (!preg_match('/^[0-9a-z-_]+$/', $prefix)) {
                        $this->configWriter->save(
                            'sellersubdomain/settings/prefix',
                            preg_replace(
                                '/[^a-z0-9-_]/',
                                '',
                                $prefix
                            )
                        );
                        $this->messageManager->addNotice(
                            'only characters(a-z), numbers(0,9), - and _ are allowed in prefix'
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    /**
     * Disable marketplace rewrite setting
     *
     */
    public function disableUrlRewrite($observer)
    {
        $stores = $this->_storeRepository->getList();
        foreach ($stores as $store) {
            $this->configWriter->save('marketplace/profile_settings/url_rewrite', 0, 'stores', $store->getId());
            $this->configWriter->save('marketplace/profile_settings/auto_url_rewrite', 0, 'stores', $store->getId());
        }
        $this->configWriter->save('marketplace/profile_settings/url_rewrite', 0);
        $this->configWriter->save('marketplace/profile_settings/auto_url_rewrite', 0);
    }
}
