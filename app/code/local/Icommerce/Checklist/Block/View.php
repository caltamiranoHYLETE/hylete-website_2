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

class Icommerce_Checklist_Block_View extends Mage_Core_Block_Template
{        
    protected function _construct()
    {
        parent::_construct();
        $r = $this->getRequest();
        $this->setCustomTemplate($r->getParam("mode"));
    }
        
    public function setCustomTemplate($mode)
    {
        switch ($mode) 
        {
            case 'view':
                    $this->setTemplate("icommerce/checklist/view.phtml");
            break;

            default:
                   $this->setTemplate("icommerce/checklist/view.phtml");
        }
    }
    
    public function getProject()
    {
        $params = $this->getRequest()->getParams();
        $posts = $this->getRequest()->getPost();
        $urlkey = $params['key'];
        $project = Icommerce_Db::getRow("SELECT * FROM icommerce_checklist WHERE urlkey=?", $urlkey);
        
        try
        {
            if($posts['save'] == $project['id'])
                $this->saveProject($project['id']);
        }
        catch(Exception $e ){}
        
        return Icommerce_Db::getRow("SELECT * FROM icommerce_checklist WHERE urlkey=?", $urlkey);
    }
    
    public function getCheckLists($projectId)
    {
        $r = Icommerce_Db::getDbRead();
        $sql = "SELECT * FROM icommerce_checklist_item WHERE project_id = ? ORDER BY position ASC";
        $rows = $r->query( $sql, $projectId);
        
        foreach($rows as $key => $value)
            $lists[$key] = $value;
        
        return $lists;           
    }
    
    public function getCheckboxes($listId)
    {
        $r = Icommerce_Db::getDbRead();
        $sql = "SELECT * FROM icommerce_checklist_item_checkbox WHERE item_id = ? ORDER BY position ASC";
        $rows = $r->query( $sql, $listId );
        
        foreach($rows as $key => $value)
            $checkboxes[$key] = $value;
        
        return $checkboxes;            
    }
    
    public function saveProject($projectId)
    {
        $posts = $this->getRequest()->getPost();
        $date = Mage::getModel('core/date')->date('Y-m-d H:i:s');
        $pm_email = Icommerce_Db::getDbSingleton("SELECT pm_email FROM icommerce_checklist WHERE id=?", $projectId);
        $name = Icommerce_Db::getDbSingleton("SELECT name FROM icommerce_checklist WHERE id=?", $projectId);
        $email = $posts['email'];
        $url = $posts['url'];
        
        $wr = Icommerce_Db::getDbWrite();
        $sql = "UPDATE icommerce_checklist SET updated_on=? WHERE id=?";
        $wr->query( $sql, array($date, $projectId));
        
        $sql = "UPDATE icommerce_checklist SET updated_by=? WHERE id=?";
        $wr->query( $sql, array($email, $projectId));
        
        $keys = array_keys($posts);    
        $checkboxes = array();
        foreach ($keys as $key)
        {    
            /* Checkboxes */
            $sql = "UPDATE icommerce_checklist_item_checkbox SET updated_on=? WHERE id=? AND checked='0'";
            $wr->query( $sql , array($date, $key));
                
            $sql = "UPDATE icommerce_checklist_item_checkbox SET updated_by=? WHERE id=? AND checked='0'";
            $wr->query( $sql , array($email, $key));
            
            $text = Icommerce_Db::getDbSingleton("SELECT text FROM icommerce_checklist_item_checkbox 
                                                    WHERE id=? AND updated_on=? AND checked='0'", array($key, $date));
            $itemId = Icommerce_Db::getDbSingleton("SELECT item_id FROM icommerce_checklist_item_checkbox 
                                                    WHERE id=? AND updated_on=? AND checked='0'", array($key, $date));
            $itemName = Icommerce_Db::getDbSingleton("SELECT name FROM icommerce_checklist_item WHERE id=?", $itemId);
            if($text)
                array_push($checkboxes, ($itemName."</b><br />".$text));
            /* End Checkboxes */    
            
            /* Comments */
            if(strstr($key, "comment-") && $posts[$key])
            {    
                $itemId = substr($key,8);
                $itemName = Icommerce_Db::getDbSingleton("SELECT name FROM icommerce_checklist_item WHERE id=?", $itemId);
                array_push($checkboxes, ($itemName."</b><br />".$this->__('Comment: ').$posts[$key]));
            }            
            /* End Comments */
        }  
        
        $sql = "UPDATE icommerce_checklist_item_checkbox SET checked='0' WHERE project_id=?";
        $wr->query( $sql, $projectId );
        
        foreach ($keys as $key)
        {    
            $sql = "UPDATE icommerce_checklist_item_checkbox SET checked='1' WHERE id=?";
            $wr->query( $sql, $key );
        }  
        
        $this->notify($email, $pm_email, $date, $url, $name, $checkboxes);
        
    }
    
    public function notify($email, $pm_email, $date, $url, $name, $checkboxes)
    {
        $subject = $this->__('Checklist: ').$name." - ".$this->__('Confirmation');
        $from = "no-reply@icommerce.se" . 
                "\r\n" . "MIME-Version: 1.0\r\n" . "Content-type: text/html; charset=UTF-8\r\n" . 
                "X-Mailer: PHP/" . phpversion() . "";
        $headers = "From: $from";
        
        
        $message = 
        "
        <html>
            <table>
                <tr>
                    <td>".$this->__('Project manager')."</td>
                    <td>".$pm_email."</td>
                </tr>
                <tr>
                    <td>".$this->__('Approver')."</td>
                    <td>".$email."</td>
                </tr>
                <tr>
                    <td>".$this->__('URL of the checklist')."</td>
                    <td><a href='".$url."'>".$url."</a></td>
                </tr>
                <tr>
                    <td>".$this->__('Date of approval')."</td>
                    <td>".$date."</td>
                </tr>
            </table>
            <br />
        ";
        
        $i = 1;
        $checkboxText = "";
        foreach ($checkboxes as $checkbox)
        {
            if($checkbox)
            {
                $checkboxText .= "<b>$i. ". $checkbox . "<br /><br />";
                $i++;
            }
        }
        if($checkboxText)
        {
            $message .= "<b>".$this->__('The following items have been approved:') ."</b><br />".$checkboxText."</html>";
            mail($email,'=?UTF-8?B?'.base64_encode($subject).'?=',$message,$headers);
            mail($pm_email,'=?UTF-8?B?'.base64_encode($subject).'?=',$message,$headers);
        }
    }
    
    
    /*
     * Loads the html file named 'checklist_email_template.html' from
     * app/locale/en_US/template/email/ic_checklist.html
     */
    public function sendMail($projectId, $toCustomer, $url)
    {
    
        $project = Icommerce_Db::getRow("SELECT * FROM icommerce_checklist WHERE id=?", $projectId);
    
           $emailTemplate = Mage::getModel('core/email_template')->loadDefault('checklist_email_template');  
           $emailTemplate->setSenderName('Pablo');
        $emailTemplate->setSenderEmail('habash88@gmail.com');
        $emailTemplate->setTemplateSubject('Checklist approved');
           
                                         
        $emailTemplateVariables = array();
        #$emailTemplateVariables['from'] = $project['pm_email'];
        #$emailTemplateVariables['to'] = $toCustomer;
        $emailTemplateVariables['to'] = 'digitalmail@me.com';
        $emailTemplateVariables['from'] = 'habash88@gmail.com';
        $emailTemplateVariables['urlkey'] = $project['urlkey'];
        $emailTemplateVariables['url'] = $url;
        $emailTemplateVariables['name'] = $project['name'];
        
        $emailTemplate->getProcessedTemplate($emailTemplateVariables);    
    }    
    

}