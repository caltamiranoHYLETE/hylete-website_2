<?php
class Icommerce_Messages {

    static function logMessage( $msg, $component, $msg_full="" ){
        self::logAny( "message", $msg, $component, $msg_full );
    }

    static function logWarning( $msg, $component, $msg_full="" ){
        self::logAny( "warning", $msg, $component, $msg_full );
    }

    static function logError( $msg, $component, $msg_full="" ){
        self::logAny( "error", $msg, $component, $msg_full );
    }

    static private function logAny( $type, $msg, $component, $msg_full ){
        $inst = Icommerce_Default::getInstanceName();
        Icommerce_MessageLog::logAny( $type, $msg, $component, $msg_full, $inst );
    }

}
