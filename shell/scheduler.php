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
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE PROGRAM OR THE USE OR OTHER DEALINGS
 * IN THE PROGRAM.
 *
 * @category    Vaimo
 * @package     Icommerce_Scheduler
 * @copyright   Copyright (c) 2009-2014 Vaimo AB
 * @author      Urmo Schmidt <urmo.schmidt@vaimo.com>
 *
 * If you want to run it locally, and this file is sym linked (aja), then cd to shell/vaimo and run it from there
 *   cd shell/vaimo
 *   php scheduler.php
 *
 * If you want to debug:
 *   export XDEBUG_CONFIG="idekey=PHPSTORM"
 *   XDEBUG_SESSION_START=1 php scheduler.php
 */

require_once 'abstract.php';

class Vaimo_Padded_Table
{
    protected $_columns = array();
    protected $_rows = array();
    protected $_colSpacing = 2;

    public function setColumns(array $columns)
    {
        $this->_columns = $columns;
    }

    public function addRow(array $row)
    {
        $this->_rows[] = $row;
    }

    public function printTable()
    {
        $maxWidths = array();
        $totalWidth = 0;

        foreach ($this->_columns as $key => $value) {
            $maxWidths[$key] = strlen($value);
        }

        foreach ($this->_rows as $row) {
            foreach ($this->_columns as $key => $value) {
                $maxWidths[$key] = max($maxWidths[$key], strlen($row[$key]));
            }
        }

        foreach ($this->_columns as $key => $value) {
            echo str_pad($value, $maxWidths[$key] + $this->_colSpacing, ' ');
            $totalWidth += $maxWidths[$key];
        }

        echo "\n" . str_pad('', $totalWidth + count($this->_columns) * $this->_colSpacing - $this->_colSpacing, '-') . "\n";

        foreach ($this->_rows as $row) {
            foreach ($this->_columns as $key => $value) {
                echo str_pad($row[$key], $maxWidths[$key] + $this->_colSpacing, ' ');
            }
            echo "\n";
        }
    }
}

class Vaimo_Shell_Scheduler extends Mage_Shell_Abstract
{
    public function run()
    {
        if ($this->getArg('status')) {
            $table = new Vaimo_Padded_Table();
            $statuses = Mage::helper('scheduler')->getOperationStatusesOptionArray();
            $schedulerOperations = Mage::helper('scheduler')->getDefinedSchedulerOperations();
            $columns = array('id', 'code', 'status');

            /** @var Icommerce_Scheduler_Model_Resource_Operation_Collection $collection */
            $collection = Mage::getResourceModel('scheduler/operation_collection');
            $collection->addFieldToSelect($columns);

            $table->setColumns(array(
                'id' => 'Id',
                'code' => 'Code',
                'status' => 'Status',
            ));

            foreach ($collection as $operation) {
                if (isset($schedulerOperations[$operation->getCode()]['label'])) {
                    $operation->setCode($schedulerOperations[$operation->getCode()]['label']);
                }
                if (isset($statuses[$operation->getStatus()])) {
                    $operation->setStatus($statuses[$operation->getStatus()]);
                }
                $table->addRow($operation->getData());
            }

            $table->printTable();
        } else if ($this->getArg('run')) {
            Mage::helper('scheduler')->runOperations(true);
            Mage::helper('scheduler')->resetCrashedTasks(true);
        } else {
            echo $this->usageHelp();
        }
    }

    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f scheduler.php [options]

  status            Show Task(s) Status
  run               Run all scheduled tasks
  help              This help


USAGE;
    }
}

$shell = new Vaimo_Shell_Scheduler();
$shell->run();
