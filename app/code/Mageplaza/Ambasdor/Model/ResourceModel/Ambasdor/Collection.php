<?php
namespace Mageplaza\Ambasdor\Model\ResourceModel\Ambasdor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
	protected $_idFieldName = 'post_id';
	protected $_eventPrefix = 'mageplaza_ambasdor_collection';
	protected $_eventObject = 'id_collection';

	/**
	 * Define resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mageplaza\Ambasdor\Model\Ambasdor', 'Mageplaza\Ambasdor\Model\ResourceModel\Ambasdor');
	}

}