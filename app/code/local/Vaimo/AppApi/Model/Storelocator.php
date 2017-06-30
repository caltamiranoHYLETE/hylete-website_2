<?php
/**
 * Copyright (c) 2009-2015 Vaimo AB
 *
 * Vaimo reserves all rights in the Program as delivered. The Program
 * or any portion thereof may not be reproduced in any form whatsoever without
 * the written consent of Vaimo, except as provided by licence. A licence
 * under Vaimo's rights in the Program may be available directly from
 * Vaimo.
 *
 * Disclaimer:
 * THIS NOTICE MAY NOT BE REMOVED FROM THE PROGRAM BY ANY USER THEREOF.
 * THE PROGRAM IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Vaimo_AppApi
 * @copyright   Copyright (c) 2009-2015 Vaimo AB
 * @author      Kjell Holmqvist
 */

class Vaimo_AppApi_Model_Storelocator extends Vaimo_AppApi_Model_Abstract
{

    public function listStores()
    {
        $stores = array();

        $countries = $this->_getHelper()->getStoreLocatorCountries();
        $countryArr = array();
        foreach ($countries as $country) {
            $countryArr[$country['country_location_id']] = $country;
        }
        $storesLocators = $this->_getHelper()->getStoreLocatorStores();

        foreach ($storesLocators as $store) {
            $storesInfo = $store->getData();
            if (isset($countryArr[$storesInfo['country_location_id']])) {
                $storesInfo['country_location_title'] = $countryArr[$storesInfo['country_location_id']]['title'];
                $storesInfo['country_location_code']  = $countryArr[$storesInfo['country_location_id']]['country_code'];
            }
            $stores[] = $storesInfo;
        }

        $res = $this->_getHelper()->dispatchUpdateEventArray( 'app_api_list_storelocators', $stores );

        return $res;
    }

}
