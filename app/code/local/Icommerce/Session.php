<?php

class Icommerce_Session {

    static protected function getSessionId(){
        //$id = session_id();
        $str = "";
        if (isset($_SERVER["HTTP_USER_AGENT"])) $str = $_SERVER["HTTP_USER_AGENT"];
        if ($str!="") $str = $str . "-";
        $str = $str . $_SERVER["REMOTE_ADDR"];
        if ($str=="") $str = "non-unique-id";
//        $str = $_SERVER["HTTP_USER_AGENT"] . "-" . $_SERVER["REMOTE_ADDR"];
        $sid = md5( $str );
        return $sid;
        //return session_id();
    }

    static protected function getSessFileName(){
        $sid = self::getSessionId();
        if( $sid===null ){
            return null;
        }
        return Icommerce_Default::getSiteRoot(true) . "var/session/sess_".$sid."_ic";
    }

    static protected $delayed;
    static protected $n_change = 0;

    static protected function save(){
        // Allow late saving, to reduce file writing
        if( self::$delayed ){
            self::$n_change++;
            return;
        }

        $file = self::getSessFileName();
        try {
            if( $f = fopen($file,"w") ){
                // Set session time
                self::$_vars["__time"] = time();
                foreach( self::$_vars as $k => $v ){
                    fwrite( $f, "$k=$v\n" );
                }
                fclose( $f );
                return true;
            }
        } catch( Exception $e ){ }
    }

    static public function setDelayedSave( $do_begin ){
        self::$delayed = $do_begin;
        if( !$do_begin && self::$n_change ){
            self::save();
            self::$n_change = 0;
        }
    }

    static protected $_vars;
    static protected function init(){
        if( !self::$_vars ){
            self::$_vars = array();
            if( $file = self::getSessFileName() ){
                if( file_exists($file) ){
                    $f = fopen( $file, "r" );
                    while( ($s=fgets($f))!==FALSE ){
                        $kv = explode( "=", $s );
                        if( count($kv)>1 ){
                            $val = trim($kv[1]);
                            self::$_vars[$kv[0]] = $val;
                        }
                    }
                    if( isset(self::$_vars["__time"]) ){
                        if( time()-self::$_vars["__time"]>60*60 ){
                            // Old session!
                            self::$_vars = array();
                        } else {
                            // Update the time stamp of the session
                            self::set( null, null );
                        }
                    } else {
                        // Session without __time: throw away
                        self::$_vars = array();
                    }
                }
            }
        }
    }

    static public function set( $key, $val ){
        self::init();
        if( $key ){
            self::$_vars[$key] = $val;
            self::save();
        }
        return false;
    }

    static public function get( $key ){
        self::init();
        if( array_key_exists($key,self::$_vars) ){
            return self::$_vars[$key];
        }
        return null;
    }

    static public function erase( $key, $is_reg_exp=false ){
        self::init();
        if( !$is_reg_exp ){
            if( !isset( self::$_vars[$key] ) ) return;
            unset( self::$_vars[$key] );
        } else {
            // Rex expr key erase
            $changed = 0;
            foreach( self::$_vars as $k => $v ){
                if( preg_match($key, $k) ){
                    unset( self::$_vars[$k] );
                    $changed++;
                }
            }
            if( !$changed ) return;
        }
        // Commit changes
        self::save();
    }

}
