<?php
use Mage_Catalog_Model_Product as P;
// 2019-10-30
final class Justuno_Jumagext_Catalog_Images {
	/**
	 * 2019-10-30
	 * @used-by \Justuno_Jumagext_ResponseController::catalogAction()
	 * @param P $p
	 * @return array(array(string => mixed))
	 */
	static function p(P $p) { /** @var array(array(string => mixed)) $r */
		$r = [];
		$h = Mage::helper('catalog/image'); /** @var Mage_Catalog_Helper_Image $h */
		// 2019-10-30
		// «"ImageURL" should be "imageURL1" and we should have "imageURL2" and "ImageURL3"
		// if there are image available»: https://github.com/justuno-com/m1/issues/17
		foreach (array_values($p->getMediaGalleryImages()->getItems()) as $idx => $i) {$idx++;
			// 2019-10-30
			// «the feed currently links to the large version of the first image only.
			// Could we change it to link to the small image?»: https://github.com/justuno-com/m1/issues/18
			$r["ImageURL$idx"] = (string)$h
				->init($p, 'image', $i['file'])
				->keepAspectRatio(true)
				->constrainOnly(true)
				->keepFrame(false)
				->resize(200, 200)
			;
		}
		return $r;
	}
}