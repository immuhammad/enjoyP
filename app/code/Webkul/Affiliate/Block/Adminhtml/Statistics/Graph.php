<?php
/**
 * Webkul Affiliate Graph.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Block\Adminhtml\Statistics;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;
use Webkul\Affiliate\Model\ResourceModel\Clicks\CollectionFactory;

class Graph extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    public $_template = 'statistics/graph.phtml';

    /**
     * Affiliate statistics graph width
     *
     * @var string
     */
    private $width = '800';

    /**
     * Affiliate statistics graph height
     *
     * @var string
     */
    private $height = '375';

    /**
     * @var Magento\Framework\App\DeploymentConfig
     */
    private $deploymentConfigDate;

    /**
     * @var Webkul\Affiliate\Model\ResourceModel\Clicks\CollectionFactory
     */
    private $collectionFactory;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param DeploymentConfig $deploymentConfig
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        DeploymentConfig $deploymentConfig,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
    
        $this->deploymentConfigDate = $deploymentConfig->get(ConfigOptionsListConstants::CONFIG_PATH_INSTALL_DATE);
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Get Affiliate Trafic yearly graph image url
     *
     * @return string
     */
    public function getAffiYearTraficStatisticsGraph()
    {
        $monthlyClicks = $this->getMonthlyClicks();
        return $monthlyClicks;
    }

    /**
     * getMonthlyClicks
     * @return array
     */
    public function getMonthlyClicks()
    {
        $data=[];
        $curryear = date('Y');
        for ($month=1; $month<=12; $month++) {
            $dateFrom = $curryear."-".$month."-01 00:00:00";
            $dateTo = $curryear."-".$month."-31 23:59:59";
            $data[$month] = $this->collectionFactory->create()->addFieldToFilter(
                'created_at',
                ['datetime' => true,'from' =>  $dateFrom,'to' =>  $dateTo]
            )->getSize();
        }
        return $data;
    }

    /**
     * Get Affiliate Trafic yearly graph image url
     *
     * @return string
     */
    public function getAffiMonthTraficGraph()
    {
        $dailyClicks = $this->getDailyClicks();
        return  $dailyClicks;
    }

    /**
     * Get Daily Clicks
     * @return array
     */
    public function getDailyClicks()
    {
        $data=[];
        $curryear = date('Y');
        $currentMonth = date('m');
        for ($day=1; $day<=31; $day++) {
            $dateFrom = $curryear."-".$currentMonth."-".$day." 00:00:00";
            $dateTo = $curryear."-".$currentMonth."-".$day." 23:59:59";
            $data[$day] = $this->collectionFactory->create()->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' =>  $dateFrom, 'to' =>  $dateTo]
            )->getSize();
        }
        return $data;
    }
}
