<?php
/**
 * OnePica_AvaTax
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0), a
 * copy of which is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 * @copyright  Copyright (c) 2015 One Pica, Inc.
 * @license    http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

/**
 * The AvaTax Bag Items model
 *
 * @category   OnePica
 * @package    OnePica_AvaTax
 * @author     OnePica Codemaster <codemaster@onepica.com>
 */
class OnePica_AvaTax_Model_Service_Avatax_BagItems extends OnePica_AvaTax_Model_Service_Avatax_Abstract
{
    /**
     * @param int $storeId
     * @return array|Varien_Object
     */
    public function getAllParameterBagItems($storeId = null)
    {
        /** @var \OnePica_AvaTax_Model_Service_Avatax_Config $config */
        $config = $this->getServiceConfig();
        $connection = $config->getTaxConnection();
        $result = null;
        $message = null;

        try {
            /** @var \BaseResult $response */
            $response = $connection->getAllParameterBagItems();
            $result = $response->ParameterBags->ParameterBag;
        } catch (Exception $exception) {
            $message = $exception->getMessage();
        }

        if (!isset($response) || !is_object($response) || !$response->getResultCode()) {
            $actualResult = $result;
            $result = new Varien_Object();
            $result->setResultCode(SeverityLevel::$Exception);
            $result->setActualResult($actualResult);
            $result->setMessage($message);
        }

        return $result;
    }
}
