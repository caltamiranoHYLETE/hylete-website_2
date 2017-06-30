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

class Icommerce_Checklist_Model_Item
{
    function getItems($listshowId)
    {
        
        $r = Icommerce_Db::getDbRead();
        
        $sql = "";
        $sql .= "SELECT * FROM icommerce_checklist_item ";
        $sql .= "WHERE project_id = ? ";
        $sql .= "ORDER BY position ASC";
        
        $rows = $r->query( $sql, $listshowId );
        
        $returnArray = array();
        
        foreach($rows as $key => $value)
            $returnArray[$key] = $value;
        
        return $returnArray;
    }
    
    function getItem($itemId)
    {
        $row = Icommerce_Db::getRow("SELECT * FROM icommerce_checklist_item WHERE id = ?", $itemId);
        return $row;
    }
    
    function getItemForCheckbox($itemId)
    {
        $row = Icommerce_Db::getRow("SELECT * FROM icommerce_checklist_item WHERE id = ?", $itemId);
        return $row;
    }
    
    /**
     * This function is used to get the published items for frontend. 
    */
    
    function getListshowItems($listshowId)
    {
    
        $r = Icommerce_Db::getDbRead();
        
        $sql = "";
        $sql .= "SELECT si.* ";
        $sql .= "FROM icommerce_checklist_item AS si ";
        $sql .= "INNER JOIN icommerce_checklist as s ";
        $sql .= "ON si.project_id = s.id ";
        $sql .= "WHERE si.project_id = ? AND s.status = '1' AND si.status = '1' ";
        $sql .= "ORDER BY si.position ASC";
        
        $rows = $r->query( $sql, $listshowId );
        
        $returnArray = array();
        
        foreach($rows as $key => $value)
            $returnArray[$key] = $value;
        
        return $returnArray;
    }
}
