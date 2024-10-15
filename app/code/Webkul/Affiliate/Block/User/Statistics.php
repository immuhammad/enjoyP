<?php
/**
 * Webkul Affiliate Statistics.
 *
 * @category Webkul
 * @package  Webkul_Affiliate
 * @author   Webkul
 * @license  https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Webkul\Affiliate\Model\ResourceModel\Clicks\CollectionFactory;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class Statistics extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * Helper
     *
     * @var \Webkul\Affiliate\Helper\Data
     */
    public $affDataHelper;

    /**
     * @var Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfigDate;

    /**
     * Affiliate User statistics graph width
     *
     * @var string
     */
    private $width = '800';
    
    /**
     * Affiliate User statistics graph height
     *
     * @var string
     */
    private $height = '375';

    /**
     * @param Context                $context
     * @param Session                $customerSession,
     * @param AffDataHelper          $affDataHelper,
     * @param CollectionFactory      $collectionFactory,
     * @param DeploymentConfig       $deploymentConfig
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AffDataHelper $affDataHelper,
        CollectionFactory $collectionFactory,
        DeploymentConfig $deploymentConfig,
        array $data = []
    ) {
        $this->help=$affDataHelper;
        $this->collectionFactory = $collectionFactory;
        $this->deploymentConfigDate = $deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE);
        parent::__construct($context, $customerSession, $affDataHelper, $data);
    }

    /**
     * Get Affiliate statistics graph image url
     *
     * @return string
     */
    public function getAffilaiteUserStatisticsGraph()
    {
        $view=$this->getRequest()->getParam('view');
        if ($view!="monthly") {
            //daily
            $data = $this->getDailyClicks();
        } else {
            $data = $this->getMonthlyClicks();
        }
        return $data;
    }

    /**
     * Get Monthly Click data
     * @return array
     */
    public function getMonthlyClicks()
    {
        $affiUserId = $this->getCustomerSession()->getCustomerId();
        $data=[];
        $curryear = date('Y');
        for ($month=1; $month<=12; $month++) {
            $dateFrom = $curryear."-".$month."-01 00:00:00";
            $dateTo = $curryear."-".$month."-31 23:59:59";
            $data[$month] = $this->collectionFactory->create()
                                    ->addFieldToFilter('aff_customer_id', ['eq' => $affiUserId])
                                    ->addFieldToFilter(
                                        'created_at',
                                        ['datetime' => true,'from' =>  $dateFrom,'to' =>  $dateTo]
                                    )->getSize();
        }
        return $data;
    }

    /**
     * Get Daily Click Data
     *
     * @return array
     */
    public function getDailyClicks()
    {
        $affiUserId = $this->getCustomerSession()->getCustomerId();
        $data=[];
        $curryear = date('Y');
        $month = date('m');
        for ($day=1; $day<=31; $day++) {
            $dateFrom = $curryear."-".$month."-".$day." 00:00:00";
            $dateTo = $curryear."-".$month."-".$day." 23:59:59";
            $data[$day] = $this->collectionFactory->create()
                                    ->addFieldToFilter('aff_customer_id', ['eq' => $affiUserId])
                                    ->addFieldToFilter(
                                        'created_at',
                                        ['datetime' => true,'from' =>  $dateFrom,'to' =>  $dateTo]
                                    )->getSize();
        }
        return $data;
    }
    
    /**
     * Check if Affiliate user
     *
     * @return boolean
     */
    public function checkAffUser()
    {
        $affiUserId = $this->getCustomerSession()->getCustomerId();
        return  $this->help->isAffiliateUser($affiUserId);
    }
}
