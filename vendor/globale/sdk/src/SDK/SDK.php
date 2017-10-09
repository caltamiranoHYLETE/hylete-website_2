<?php
namespace GlobalE\SDK;

use GlobalE\SDK\Models\Common;
use GlobalE\SDK\Core\Settings;
use GlobalE\SDK\Core\Profiler;

/**
 * SDK - Base Public Interfaces
 * contains all the basic public interfaces for SDK usage and Global-E API
 * @since 1.0.0
 * @version 1.0.0
 * @copyright Global-E
 */
final class SDK extends Controllers\BaseController {
    
    /**
     * Static global SDK settings instance.
     * @var Common\VatRateType
     * @access static public
     */
    static public $MerchantVatRateType;

    /**
     * Static global SDK Base CCC and Vat setup.
     * @var Common\BaseInfo
     * @access static public
     */
    static public $baseInfo;

    /**
     * Globale_SDK constructor.
     * Globale_SDK constructor Initialize Settings and Profiler.
     * @param null $vatRate
     * @param null $baseCurrency
     * @throws \Exception
     * @access public
     */
    public function __construct($vatRate = null, $baseCurrency = null) {
        if($this->IsEnabled()){
            // init default vat rate type
            Profiler::startTimer('SDK_Construct');
            if(!$vatRate){
                $vatRate = Settings::get('Base.VatRate');
            }
            if($vatRate){
                self::$MerchantVatRateType = new Common\VatRateType($vatRate, 'DEFAULT', '1');
            }
            Core\Log::log('SDK VatRate initialized', Core\Log::LEVEL_DEBUG, array('VatRateType'=>self::$MerchantVatRateType));
            // init BaseInfo
            if(!$baseCurrency){
                $baseCurrency = Settings::get('Base.Currency');
            }
            self::$baseInfo = new Common\BaseInfo(
                Settings::get('Base.Country'),
                $baseCurrency,
                Settings::get('Base.Culture')
            );
			//@TODO make sure all SDK Settings were initialized - if not - throw exception

			$Version = Settings::get('EnvDetails.Version');
			Core\Log::log('SDK Version '.$Version, Core\Log::LEVEL_INFO);
            Core\Log::log('SDK BaseInfo initialized', Core\Log::LEVEL_DEBUG, array('baseInfo'=>self::$baseInfo));
            Profiler::endTimer('SDK_Construct');
        }
        else{
            Core\Log::log('SDK is NOT enabled in settings.', Core\Log::LEVEL_ERROR);
            throw new \Exception('SDK is NOT enabled in settings.');
        }
    }

    /**
     * All public interfaces that display on frontend side.
     * @return Controllers\Browsing|Controllers\NullController
     * @access public
     */
    public function Browsing() {

        $Browsing = new Controllers\Browsing();
        if(!$Browsing->IsEnabled()){
            $Browsing = new Controllers\NullController('Browsing');
        }
        return $Browsing;
    }

    /**
     * All public interfaces that are used for the checkout.
     * @return Controllers\Checkout|Controllers\NullController
     * @access public
     */
    public function Checkout() {

        $Checkout = new Controllers\Checkout();
        if(!$Checkout->IsEnabled()){
            $Checkout = new Controllers\NullController('Checkout');
        }
        return $Checkout;
    }

    /**
     * All public interfaces that are used for communication between Global-e API and the SDK.
     * @return Controllers\Merchant|Controllers\NullController
     * @access public
     */
    public function Merchant() {

        $Merchant = new Controllers\Merchant();
        if(!$Merchant->IsEnabled()){
            $Merchant = new Controllers\NullController('Merchant');
        }
        return $Merchant;
    }

    /**
     * All public interfaces that are used in the platform backend.
     * @return Controllers\Admin|Controllers\NullController
     * @access public
     */
    public function Admin() {

        $Admin = new Controllers\Admin();
        if(!$Admin->IsEnabled()){
            $Admin = new Controllers\NullController('Admin');
        }
        return $Admin;
    }

    /**
     * Globale_SDK destructor.
     * If profiler enabled, display it
     * @access public
     */
    public function __destruct(){

        // Check if profile enabled and client asked to display it
        $profiler_enabled = Core\Settings::get('Profiler.Enable');
        $show_profiler = (isset($_GET['GlobalE_Profiler']) && $_GET['GlobalE_Profiler'] == '1') || Core\Settings::get('Profiler.AlwaysShow');
        if($profiler_enabled === true && $show_profiler === true){
            Core\Profiler::render(true);
        }
    }
}