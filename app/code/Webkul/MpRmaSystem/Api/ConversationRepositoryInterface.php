<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRmaSystem\Api;

interface ConversationRepositoryInterface
{
    /**
     * Get by id
     *
     * @param int $id
     * @return \Webkul\MpRmaSystem\Model\Conversation
     */
    public function getById($id);
    
    /**
     * Save
     *
     * @param \Webkul\MpRmaSystem\Model\Conversation $subject
     * @return void
     */
    public function save(\Webkul\MpRmaSystem\Model\Conversation $subject);

    /**
     * Get list
     *
     * @param Magento\Framework\Api\SearchCriteriaInterface $creteria
     * @return Magento\Framework\Api\SearchResults
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $creteria);

    /**
     * Delete
     *
     * @param \Webkul\MpRmaSystem\Model\Conversation $subject
     * @return boolean
     */
    public function delete(\Webkul\MpRmaSystem\Model\Conversation $subject);
    
    /**
     * Delete by id
     *
     * @param int $id
     * @return boolean
     */
    public function deleteById($id);
}
