<?php
/**
 * Copyright (c) 2009-2014 Vaimo AB
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
 * FITNESS FOR A PARTICULAR PURPOSE AND NON-INFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Icommerce
 * @package     Icommerce_Checklist
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 */

class Icommerce_Checklist_Model_Listshow
{
    function getProjects()
    {
        
        $r = Icommerce_Db::getDbRead();
        $rows = $r->query( "SELECT * FROM icommerce_checklist ORDER BY position ASC" );
        
        $returnArray = array();
        
        foreach($rows as $key => $value)
            $returnArray[$key] = $value;
        
        return $returnArray;
    }
    
    function getProject($listshowId)
    {
        $row = Icommerce_Db::getRow("SELECT * FROM icommerce_checklist WHERE id = ?", $listshowId);
        
        return $row;
    }
}
