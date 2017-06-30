<?php

class Icommerce_Cms {

    static function cmsPageExists( $identifier, $title=null ){
        $read = Icommerce_Default::getDbRead();

        $exist = false;
        if( $identifier ){
            $r_cat = $read->query( "SELECT count(*) as cms_cnt FROM `cms_page` WHERE identifier='$identifier'" );
            foreach( $r_cat as $rr ){
                  if( $rr['cms_cnt']>0 ){
                        $exist = true;
                  }
            }
            if( $exist ){
                return true;
            }
        }
        if( $title ){
            $r_cat = $read->query( "SELECT count(*) as cms_cnt FROM `cms_page` WHERE title='$title'" );
            foreach( $r_cat as $rr ){
                  if( $rr['cms_cnt']>0 ){
                        $exist = true;
                  }
            }
        }

        return $exist;
    }

    static function cmsBlockExists( $identifier, $title=null ){
        $read = Icommerce_Default::getDbRead();
        $exist = false;

        if( $identifier ){
            $r_cat = $read->query( "SELECT count(*) as cms_cnt FROM `cms_block` WHERE identifier='$identifier'" );
            foreach( $r_cat as $rr ){
                  if( $rr['cms_cnt']>0 ){
                        $exist = true;
                  }
            }
        }

        if( $title ){
            $r_cat = $read->query( "SELECT count(*) as cms_cnt FROM `cms_block` WHERE title='$title'" );
            foreach( $r_cat as $rr ){
                  if( $rr['cms_cnt']>0 ){
                        $exist = true;
                  }
            }
        }

        return $exist;
    }

    static function cmsBlockIsActive( $identifier ){
        $col = Mage::getModel('cms/block')->getCollection();
        $col->addFieldToFilter('identifier', $identifier);
        $item = $col->getFirstItem();
        $id = $item->getData('is_active');

        if($id == 1){
            return true;
        }else{
            return false;
        }
    }


    static function processBlockDirectives( $content ){
        $processor = Mage::getModel('widget/template_filter'); //extends core/email_template_filter
        $html = $processor->filter($content);
        return $html;
    }

}
