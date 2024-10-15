<?php
/**
 * Webkul Affiliate Each Controller Access.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Plugin;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Session\SessionManagerInterface as CoreSession;
use Webkul\Affiliate\Model\ClicksFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Model\UserFactory;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Framework\Filesystem\Io\File as FileSystemIo;

/**
 * Webkul Affiliate
 */
class HttpPlugin
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $coreSession;

    /**
     * @var \Webkul\Affiliate\Model\ClicksFactory
     */
    private $clicksFactory;

    /**
     * @var \Webkul\Affiliate\Model\UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;
    private $helper;

    /**
     * @var UrlRewriteFactory
     */
    private $urlRewriteFactory;

    /**
     * @var FileSystemIo
     */
    private $fileSystemIo;

    /**
     * @param StoreManagerInterface $storeManagerInterface,
     * @param TimezoneInterface $timezone,
     * @param Session $customerSession,
     * @param ClicksFactory $clicksFactory,
     * @param UserBalanceFactory $userBalance,
     * @param UserFactory $userFactory
     * @param UrlRewriteFactory $urlRewriteFactory
     * @param FileSystemIo $fileSystemIo
     */
    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        TimezoneInterface $timezone,
        CoreSession $coreSession,
        CheckoutSession $checkoutSession,
        ClicksFactory $clicksFactory,
        UserBalanceFactory $userBalance,
        UserFactory $userFactory,
        \Webkul\Affiliate\Helper\Data $helper,
        \Webkul\Affiliate\Logger\Logger $logger,
        \Magento\Framework\App\RequestInterface $request,
        UrlRewriteFactory $urlRewriteFactory,
        FileSystemIo $fileSystemIo
    ) {
        $this->storeManager = $storeManagerInterface;
        $this->timezone = $timezone;
        $this->checkoutSession = $checkoutSession;
        $this->coreSession = $coreSession;
        $this->clicksFactory = $clicksFactory;
        $this->userBalance = $userBalance;
        $this->userFactory = $userFactory;
        $this->logger = $logger;
        $this->request = $request;
        $this->helper = $helper;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->fileSystemIo = $fileSystemIo;
    }

    /**
     * @param \Magento\Framework\App\Response\Http $subject
     */
    public function beforeSendResponse(\Magento\Framework\App\Response\Http $subject)
    {
        //product base click increament
        $request = $this->request;
        $this->logger->info('Magento\Framework\App\Response\Http plugin:' . $request->getRouteName());
        $postData = $request->getParams();
        if (isset($postData['aff_id'])) {
            $postData['aff_id'] = $postData['aff_id'];
        } else {
            $serverData = $request->getServer();
            if (($serverData->get('HTTP_REFERER'))) {
                $urlData = explode("aff_id=", $serverData->get('HTTP_REFERER'));
                if (isset($urlData[1])) {
                    $postData['aff_id'] = $urlData[1];
                }    
            }
        }
        $blogUrl = '';
        if (isset($postData['aff_id'])) {
            $affiliateColl = $this->userFactory->create()->getCollection()
                            ->addFieldToFilter('customer_id', $postData['aff_id'])
                            ->getFirstItem();
            $configValue = $this->helper->getAffiliateConfig();
            $blogUrl = $affiliateColl->getBlogUrl();
            $serverData = $request->getServer();
            $urlData = explode("aff_id=", $serverData->get('REQUEST_URI'));
            if (isset($urlData[1])) {
                $postData['aff_id'] = $urlData[1];
            }
            $baseUrl = $this->storeManager->getStore()->getBaseUrl();
            $hitData = explode('catalog/product/view/id/', $serverData->get('REQUEST_URI'));
            if (isset($hitData[1])) {
                $hit =  explode('?', $hitData[1]);
            } else {
                $hitData = explode('catalog/product/view/id/', $serverData->get('HTTP_REFERER'));
                if (isset($hitData[1])) {
                    $hit =  explode('?', $hitData[1]);
                }
            }
            $temp1 = trim($postData['aff_id'], explode("=", $postData['aff_id'])[0]);
            $temp2 = substr($temp1, 1);
            $hitId = isset($hitData[1])?  $hit[0] : $request->getParam('banner');
            $hitType  = isset($hitData[1])? 'product' : 'textbanner';
            if ($hitType  == 'textbanner') {
                $path = $request->getPathInfo();
                /** @var \Magento\Framework\Filesystem\Io\File $fileSystemIo **/
                $fileInfo = $this->fileSystemIo->getPathInfo($path);
                $basename = $fileInfo['basename'];
                $dirname = $fileInfo['dirname'];
                if ($dirname !== 'catalog/product/view/id') {
                    $hitId = $this->getProductId($basename);
                }
            }
            $data = [
                'customer_ip'       =>  $serverData->get('REMOTE_ADDR'),
                'customer_domain'   =>  $serverData->get('HTTP_HOST'),
                'hit_id'            =>  isset($hitData[1])?  $hit[0] : $temp2,
                'hit_type'          =>  $hitType,
                'aff_customer_id'   =>  explode("&", $postData['aff_id'])[0],
                'commission'    =>  '',
                'come_from'         =>  $serverData->get('HTTP_REFERER'),
            ];

            $clickDetail = $this->getAffUserClickAndComm($postData['aff_id'], $data);
            $data['commission'] = $clickDetail['comm'];

            $this->dataSave($postData, $data, $hitId, $serverData);
        }
    }

    public function dataSave($postData, $data, $hitId, $serverData)
    {
        $cilckData = $this->clicksFactory->create()->getCollection()
                ->addFieldToFilter('hit_id', $hitId)
                ->addFieldToFilter('customer_ip', $serverData->get('REMOTE_ADDR'));

        if ($cilckData->getSize() < 1) {
            $clickDetail = $this->getAffUserClickAndComm($postData['aff_id'], $data);
            /** save click detail*/
            $clickTmpColl = $this->clicksFactory->create()->getCollection()
                ->addFieldToFilter('customer_ip', $data['customer_ip'])
                ->addFieldToFilter('customer_domain', $data['customer_domain'])
                ->addFieldToFilter('hit_id', $data['hit_id'])
                ->addFieldToFilter('hit_type', $data['hit_type'])
                ->addFieldToFilter('aff_customer_id', $data['aff_customer_id'])
                ->addFieldToFilter('commission', $data['commission'])
                ->addFieldToFilter('come_from', $data['come_from']);
            $clickTmpColl->getSelect()->where('created_at = CURRENT_TIMESTAMP');
            if ($clickTmpColl->getSize()) {
                return false;
            }
            $clickTmp = $this->clicksFactory->create();
            $clickTmp->setData($data);
            $clickTmp->save();
            // update balance data
            if ($clickDetail) {
                $userBalanceColl = $this->userBalance->create()->getCollection()
                                        ->addFieldToFilter(
                                            'aff_customer_id',
                                            ['eq' => $postData['aff_id']]
                                        );
                if ($userBalanceColl->getSize()) {
                    foreach ($userBalanceColl as $userBalance) {
                        $clicks = $clickDetail['click'] + (int) $userBalance->getClicks();
                        $uniqueClicks = $clickDetail['unique_click']
                                        + (int) $userBalance->getUniqueClicks();
                        $balanceAmount = $userBalance->getBalanceAmount()
                                        +$clickDetail['comm'];

                        $userBalance->setClicks($clicks);
                        $userBalance->setUniqueClicks($uniqueClicks);
                        $userBalance->setBalanceAmount($balanceAmount);
                        $this->_saveObject($userBalance);
                    }
                } else {
                    $dataTmp = [
                        'aff_customer_id' => $postData['aff_id'],
                        'clicks' => $clickDetail['click'],
                        'unique_clicks' => $clickDetail['unique_click'],
                        'balance_amount' => $clickDetail['comm']
                    ];
                    $tempBal = $this->userBalance->create();
                    $tempBal->setData($dataTmp);
                    $tempBal->save();
                }
            }
        }
        // save in session
        $this->saveInSession($data);
    }

    public function saveInSession($data)
    {
        $cookie_name = 'aff_ids';
        $totalAffIds = [];
        if (!empty($totalAffIds)) {
            $status = true;
            foreach ($totalAffIds as $affData) {
                if ($affData['hit_id'] == $data['hit_id']) {
                    $status = false;
                }
            }
            if ($status) {
                array_push($totalAffIds, $data);
                $this->coreSession->setData('aff_ids', $totalAffIds);
            }
        } else {
            $totalAffIds = [$data];
            $this->coreSession->setData('aff_ids', $totalAffIds);
        }
    }

    /**
     * Check if unique click
     * @param array
     * @return bool
     */

    private function getIsUniqueClick($data)
    {
        $clickColl = $this->clicksFactory->create()->getCollection()
                                            ->addFieldToFilter('customer_ip', ['eq' => $data['customer_ip']])
                                            ->addFieldToFilter('aff_customer_id', ['eq' => $data['aff_customer_id']])
                                            ->setPageSize(1)->setCurPage(1)->getFirstItem();
        return $clickColl->getEntityId() ? false : true;
    }

    /**
     * Get Affiliate user click and commission detail
     * @param int affId
     * @param array $data
     * @return false|array
     */

    private function getAffUserClickAndComm($affId, $data)
    {
        $affUserColl = $this->userFactory->create()->getCollection()->addFieldToFilter('customer_id', $affId)
                                                    ->setPageSize(1)->setCurPage(1)->getFirstItem();
        $response = false;
        if ($affUserColl->getEntityId()) {
            $response = [];
            $response['comm'] = 0;
            if ($this->getIsUniqueClick($data)) {
                $response['comm'] =  $affUserColl->getPayPerUniqueClick();
                $response['unique_click'] = 1;
                $response['click'] = 1;
            } else {
                $response['unique_click'] = 0;
                $response['click'] = 1;
            }
            $response['comm'] = $response['comm'] +  $affUserColl->getPayPerClick();
        }
        
        return $response;
    }

    /**
     * save object
     * @param Object $object
     */
    private function _saveObject($object)
    {
        $object->save();
    }

    /**
     * Retrieve current product id
     *
     * @return string
     */
    public function getProductId($path)
    {
        $productId = '';
        $urlColl = $this->urlRewriteFactory->create()->getCollection()
                ->addFieldToFilter('entity_type', 'product')
                ->addFieldToFilter('request_path', $path)
                ->getLastItem()
                ->getData();
        if (!empty($urlColl['entity_id'])) {
            $productId = $urlColl['entity_id'];
        }
        return $productId;
    }
}
