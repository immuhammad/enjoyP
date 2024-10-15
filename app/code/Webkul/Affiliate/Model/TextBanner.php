<?php
/**
 * Affiliate TextBanner Model.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Model;

use Webkul\Affiliate\Api\Data\TextBannerInterface;
use Magento\Framework\DataObject\IdentityInterface;

class TextBanner extends \Magento\Framework\Model\AbstractModel implements TextBannerInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'wk_affiliate_text_banner';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_affiliate_text_banner';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_affiliate_text_banner';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Affiliate\Model\ResourceModel\TextBanner::class);
    }
    /**
     * Get Id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * Set Id.
     */
    public function setId($entityId)
    {
        return $this->setData(self::ID, $entityId);
    }

    /**
     * Get Title
     * @return varchar
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set Title
     */
    public function setTitle($title)
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * Get Text
     * @return varchar
     */
    public function getText()
    {
        return $this->getData(self::TEXT);
    }

    /**
     * Set Text
     */
    public function setText($text)
    {
        return $this->setData(self::TEXT, $text);
    }

    /**
     * Get Link
     * @return int
     */
    public function getLink()
    {
        return $this->getData(self::LINK);
    }

    /**
     * Set Link.
     */
    public function setLink($link)
    {
        return $this->setData(self::LINK, $link);
    }

    /**
     * Get LinkTitle
     *
     * @return int
     */
    public function getLinkTitle()
    {
        return $this->getData(self::LINK_TITLE);
    }

    /**
     * Set LinkTitle.
     */
    public function setLinkTitle($linkTitle)
    {
        return $this->setData(self::LINK_TITLE, $linkTitle);
    }

    /**
     * Get BannerSize
     * @return decimal
     */
    public function getBannerSize()
    {
        return $this->getData(self::BANNER_SIZE);
    }

    /**
     * set BannerSize
     * @return $this
     */
    public function setBannerSize($bannerSize)
    {
        return $this->setData(self::BANNER_SIZE, $bannerSize);
    }

    /**
     * Get Status.
     *
     * @return varchar
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set Status.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
   
    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
