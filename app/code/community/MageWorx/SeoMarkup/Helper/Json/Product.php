<?php
/**
 * MageWorx
 * MageWorx SeoMarkup Extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoMarkup
 * @copyright  Copyright (c) 2017 MageWorx (http://www.mageworx.com/)
 */

class MageWorx_SeoMarkup_Helper_Json_Product extends MageWorx_SeoMarkup_Helper_Abstract
{
    protected $_product;

    public function getJsonProductData($product)
    {
        $speakableData = array();
        if ($this->_helperConfig->isProductGaEnabled()) {
            $speakableData['@context']  = 'http://schema.org/';
            $speakableData['@type']     = 'WebPage';
            $speakable                  = array();
            $speakable['@type']         = 'SpeakableSpecification';
            $speakable['cssSelector']   = explode(',', $this->_helperConfig->getProductGaCssSelectors());
            $speakable['xpath']         = array('/html/head/title');
            $speakableData['speakable'] = $speakable;
        }

        if (!$this->_helperConfig->isProductRichsnippetEnabled()) {
            return !empty($speakableData) ? $speakableData : false;
        }

        if (!$this->_helperConfig->isProductJsonLdMethod()) {
            return !empty($speakableData) ? $speakableData : false;
        }

        $this->_product = $product;
        $data = array();
        $data['@context']    = 'http://schema.org';
        $data['@type']       = 'Product';
        $data['name']        = $product->getName();
        $data['description'] = $this->_helper->getDescriptionValue($this->_product);
        $data['image']       = $this->_helper->getProductImage($this->_product);


        if ($this->_helperConfig->getProductOffer()) {
            $data['offers'] = $this->_getOfferData();

            if (empty($data['offers']['price'])) {
                return !empty($speakableData) ? $speakableData : false;
            }
        }

        if ($this->_helperConfig->isRatingEnabled()) {
            $aggregateRatingData = $this->_helper->getAggregateRatingData($this->_product, false);

            if ($this->_helper->isValidReviewData($aggregateRatingData)) {
                $data['aggregateRating'] = $aggregateRatingData;

                if ($this->_helperConfig->isAddReviews()) {

                    $reviews = $this->_getCustomersReviews();

                    if ($reviews) {
                        $data['review'] = $reviews;
                    }
                }
            }
        }

        $productIdValue = $this->_helper->getProductIdValue($this->_product);
        if ($productIdValue) {
            $data['productID'] = $productIdValue;
        }

        $color = $this->_helper->getColorValue($this->_product);
        if ($color) {
            $data['color'] = $color;
        }

        $brand = $this->_helper->getBrandValue($this->_product);
        if ($brand) {
            $data['brand'] = $brand;
        }

        $manufacturer = $this->_helper->getManufacturerValue($this->_product);
        if ($manufacturer) {
            $data['manufacturer'] = $manufacturer;
        }

        $model = $this->_helper->getModelValue($this->_product);
        if ($model) {
            $data['model'] = $model;
        }

        $gtin =  $this->_helper->getGtinData($this->_product);
        if (!empty($gtin['gtinType']) && !empty($gtin['gtinValue'])) {
            $data[$gtin['gtinType']] = $gtin['gtinValue'];
        }

        $skuValue = $this->_helper->getSkuValue($this->_product);
        if ($skuValue) {
            $data['sku'] = $skuValue;
        }

        $data['url'] = $this->_helper->getProductCanonicalUrl($this->_product);

        $heightValue = $this->_helper->getHeightValue($this->_product);
        if ($heightValue) {
            $data['height'] = $heightValue;
        }

        $widthValue = $this->_helper->getWidthValue($this->_product);
        if ($widthValue) {
            $data['width'] = $widthValue;
        }

        $depthValue = $this->_helper->getDepthValue($this->_product);
        if ($depthValue) {
            $data['depth'] = $depthValue;
        }

        $weightValue = $this->_helper->getWeightValue($this->_product);
        if ($weightValue) {
            $data['weight'] = $weightValue;
        }

        $categoryName = $this->_helper->getCategoryValue($this->_product);
        if ($categoryName) {
            $data['category'] = $categoryName;
        }

        $customProperties = $this->_helperConfig->getCustomProperties();
        if ($customProperties) {
            foreach ($customProperties as $propertyName => $propertyValue) {
                if ($propertyName && $propertyValue) {
                    $value = $this->_helper->getCustomPropertyValue($product, $propertyValue);
                    if ($value) {
                        $data[$propertyName] = $value;
                    }
                }
            }
        }

        return !empty($speakableData) ? array($data, $speakableData) : $data;
    }

    protected function _getOfferData()
    {
        $data   = array();
        $data['@type'] = MageWorx_SeoMarkup_Helper_Data::OFFER;

        $prices = Mage::helper('mageworx_seomarkup/price')->getPricesByProductType($this->_product->getTypeId());
        if (is_array($prices) && count($prices)) {
            $data['price'] = $prices[0];
        }

        $data['priceCurrency'] = Mage::app()->getStore()->getCurrentCurrencyCode();

        $availability = $this->_helper->getAvailability($this->_product);
        if ($availability) {
            $data['availability'] = $availability;
        }

        $data['url'] = $this->_helper->getProductCanonicalUrl($this->_product);

        $priceValidUntil = $this->_helper->getPriceValidUntilValue($this->_product);
        if ($priceValidUntil) {
            $data['priceValidUntil'] = $priceValidUntil;
        }

        $condition = $this->_helper->getConditionValue($this->_product);
        if ($condition) {
            $data['itemCondition'] = $condition;
        }

        $paymentMethods = $this->_helper->getPaymentMethods();
        if ($paymentMethods) {
            $data['acceptedPaymentMethod'] = $paymentMethods;
        }

        $deliveryMethods = $this->_helper->getDeliveryMethods();
        if ($deliveryMethods) {
            $data['availableDeliveryMethod'] = $deliveryMethods;
        }

        return $data;
    }

    /**
     * @return array
     */
    protected function _getCustomersReviews()
    {
        $reviews = array();

        $reviewsArray = Mage::getModel('review/review')->getCollection()
                            ->addStoreFilter(Mage::app()->getStore()->getId())
                            ->addEntityFilter('product', $this->_product->getId())
                            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                            ->setDateOrder()
                            ->addRateVotes()
                            ->getItems();

        foreach($reviewsArray as $review)
        {
            $reviewData   = array();
            $reviewData['@type'] = MageWorx_SeoMarkup_Helper_Data::REVIEW;

            $name = $review->getTitle();
            if ($name) {
                $reviewData['name'] = $name;
            }

            $datePublished =  $this->formatDate($review->getCreatedAt());
            if ($datePublished) {
                $reviewData['datePublished'] = $datePublished;
            }

            $description = $review->getDetail();
            if ($description) {
                $reviewData['description'] = htmlspecialchars(strip_tags($description));
            }

            $author = $review->getNickname();
            if ($author) {
                $reviewData['author'] = array(
                    '@type' => MageWorx_SeoMarkup_Helper_Data::PERSON,
                    'name'  => $author
                );
            }
            $reviewRating = $this->_getReviewRating($review);
            if ($reviewRating) {
                $reviewData['reviewRating'] = $reviewRating;
            }

            array_push($reviews,  $reviewData);
        }

        return $reviews;
    }

    /**
     * @param $review
     * @return array
     */
    protected function _getReviewRating($review)
    {
        $reviewRatingData = array();
        $votes = $review->getRatingVotes()->getItems();

        if (is_array($votes)) {
            $total = 0;
            foreach($votes AS $vote){
                $total += $vote->getPercent();
            }
            $reviewRating = $total / count($votes);
        }

        if ($reviewRating) {
            $reviewRatingData = array();
            $reviewRatingData['@type'] = MageWorx_SeoMarkup_Helper_Data::RATING;

            if (Mage::helper('mageworx_seomarkup/config')->getBestRating()) {
                $bestRating   = Mage::helper('mageworx_seomarkup/config')->getBestRating();
                $reviewRating = round(($reviewRating / (100 / $bestRating)), 1);
            } else {
                $bestRating = 100;
            }

            $reviewRatingData['ratingValue'] = $reviewRating;
            $reviewRatingData['bestRating']  = $bestRating;
            $reviewRatingData['worstRating'] = 0;
        }

        return $reviewRatingData;
    }

    /**
     * @param $date
     * @return string
     */
    public function formatDate($date)
    {
        return Mage::helper('core')->formatDate($date, Mage_Core_Model_Locale::FORMAT_TYPE_SHORT, true);
    }
}
