<?php
/**
 * Affiliate Sale Interface.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Api\Data;

interface TextBannerInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const TITLE = 'title';
    const TEXT = 'text';
    const LINK = 'link';
    const LINK_TITLE = 'link_title';
    const BANNER_SIZE = 'banner_size';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * set ID
     *
     * @return $this
     */
    public function setId($entityId);

    /**
     * Get Title
     * @return string
     */
    public function getTitle();

    /**
     * set Title
     * @return $this
     */
    public function setTitle($title);

    /**
     * Get Text
     * @return string
     */
    public function getText();

    /**
     * set Text
     * @return $this
     */
    public function setText($text);

    /**
     * Get Link
     * @return string
     */
    public function getLink();

    /**
     * set Link
     * @return $this
     */
    public function setLink($link);

    /**
     * Get LinkTitle
     * @return varchar
     */
    public function getLinkTitle();

    /**
     * set LinkTitle
     * @return $this
     */
    public function setLinkTitle($linkTitle);

    /**
     * Get BannerSize
     * @return varchar
     */
    public function getBannerSize();

    /**
     * set BannerSize
     * @return $this
     */
    public function setBannerSize($bannerSize);

    /**
     * Get Status
     * @return int
     */
    public function getStatus();

    /**
     * set Status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Get CreatedAt.
     * @return string
     */
    public function getCreatedAt();

    /**
     * set CreatedAt.
     * @return $this
     */
    public function setCreatedAt($createdAt);
}
