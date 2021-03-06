<?php
use Justuno_M1_Lib as L;
use Mage_Catalog_Model_Resource_Product_Collection as PC;
use Mage_Sales_Model_Resource_Order_Collection as OC;
use Varien_Data_Collection_Db as C;
// 2019-10-31
final class Justuno_M1_Filter {
	/**
	 * 2019-10-31
	 * @used-by Justuno_M1_Catalog::p()
	 * @used-by Justuno_M1_Orders::p()
	 * @param C|OC|PC $r
	 * @return OC|PC
	 */
	static function p(C $r) {
		self::byDate($r);
		/** @var string $dir */ /** @var string $suffix */
		list($dir, $suffix) = $r instanceof PC ? ['DESC', 'Products'] : ['ASC', 'Orders'];
		if ($field = L::req("sort$suffix")) { /** @var string $field */
			$r->getSelect()->order("$field $dir");
		}
		// 2019-11-06
		// Fix the `offset` argument of the `Varien_Db_Select::limit()` call
		// from the `Justuno_M1_Filter::p()` method: https://github.com/justuno-com/m1/issues/34
		$size = L::reqI('pageSize', 10); /** @var int $size */
		$r->getSelect()->limit($size, $size * (L::reqI('currentPage', 1) - 1));
		return $r;
	}

	/**
	 * 2019-10-31
	 * @used-by p()
	 * @param $c $c
	 */
	private static function byDate(C $c) {
		if ($since = L::req('updatedSince')) { /** @var string $since */
			/**
			 * 2019-10-31
			 * @param string $s
			 * @return string
			 */
			$d = function($s) {
				$f = 'Y-m-d H:i:s'; /** @var string $f */
				$tz = Mage::getStoreConfig('general/locale/timezone'); /** @var string $tz */
				$dt = new DateTime(date($f, strtotime($s)), new DateTimeZone($tz));	/** @var DateTime $dt */
				return date($f, $dt->format('U'));
			};
			$c->addFieldToFilter('updated_at', ['from' => $d($since), 'to' => $d('2035-01-01 23:59:59')]);
		}
	}
}