<?php

class Ebizmarts_BakerlooRestful_Model_Api_Productreviews extends Ebizmarts_BakerlooRestful_Model_Api_Api {

    protected $_model = "review/review";
    public $defaultSort = "created_at";
    public $defaultDir = "DESC";
    protected $_iterator = false;

    protected function _getCollection() {
        return Mage::getModel($this->_model)
            ->getProductCollection()
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);
    }

    protected function _getIndexId() {
        return 'review_id';
    }

    /**
     * Process GET requests.
     *
     * @return array
     * @throws Exception
     */
    public function get() {

        $this->checkGetPermissions();

        $identifier = $this->_getIdentifier();

        if($identifier) { //get item by id

            if(is_numeric($identifier)) {
                $this->_collection = $this->_getCollection()
                    ->addEntityFilter($identifier)
                    ->addStoreData()
                    ->addReviewSummary();

                return $this->_paginateCollection($this->getCollectionSize(), 1);
            }
            else
                throw new Exception('Incorrect request.');
        }
        else {

            //get page
            $page = $this->_getQueryParameter('page');
            if(!$page) {
                $page = 1;
            }

            $filters     = $this->_getQueryParameter('filters');
            $resultArray = $this->_getAllItems($page, $filters);

            return $resultArray;

        }

    }

    public function _createDataObject($id = null, $data = null) {
        $result = array();

        if(!is_null($data))
            $review = $data;
        else
            $review = Mage::getModel($this->_model)->load($id);

        if($review->getId()){
            $result['review_id'] = (int)$review->getReviewId();
            $result['product_id'] = (int)$review->getEntityId();
            $result['store_id'] = (int)$review->getStoreId();
            $result['created_at'] = $review->getCreatedAt();
            $result['customer_id'] = (int)$review->getCustomerId();
            $result['nickname'] = $review->getNickname();
            $result['title'] = $review->getTitle();
            $result['detail'] = $review->getDetail();
            $result['ratings'] = array();

            $votes = Mage::getResourceModel('rating/rating_option_vote_collection')
                ->addFieldToFilter('review_id', array('eq' => $review->getReviewId()));

            foreach($votes as $vote) {
                $ratingCode = Mage::getModel('rating/rating')->load($vote->getRatingId())->getRatingCode();

                $result['ratings'][] = array(
                    'id' => (int)$vote->getId(),
                    'rating_type_id' => (int)$vote->getRatingId(),
                    'rating_type' => $ratingCode,
                    'value' => (int)$vote->getValue()
                );
            }
        }

        return $this->returnDataObject($result);
    }
}