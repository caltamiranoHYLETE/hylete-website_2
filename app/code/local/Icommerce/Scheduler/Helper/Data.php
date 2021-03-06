<?php

class Icommerce_Scheduler_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CUSTOM_CURL_CALL_PATH = 'scheduler/settings/custom_base_url';
    const XML_PATH_DISABLE_START = 'scheduler/settings/disable_time';
    const XML_PATH_DISABLE_END = 'scheduler/settings/enable_time';
    const XML_PATH_RESET_CRASHED_TASKS = 'scheduler/settings/reset_crashed_tasks';

    const OPERATION_STATUS_NONE = 0;
    const OPERATION_STATUS_ENABLED = 1;
    const OPERATION_STATUS_DISABLED = 2;
    const OPERATION_STATUS_RUNNING = 3;
    const OPERATION_STATUS_WAITING = 4;

    const FREQUENCY_ONCE = 0;
    const FREQUENCY_MINUTE = 1;
    const FREQUENCY_HOUR = 2;
    const FREQUENCY_DAY = 3;
    const FREQUENCY_WEEK = 4;
    const FREQUENCY_MONTH = 5;
    const FREQUENCY_YEAR = 6;

    // Must match with Utils...
    const HISTORY_STATUS_NONE = 0;
    const HISTORY_STATUS_SUCCEEDED = 1;
    const HISTORY_STATUS_FAILED = 2;
    const HISTORY_STATUS_EXCEPTIONS = 3;
    const HISTORY_STATUS_NOTHING_TO_DO = 4;

    const PROTECTED_PASSWORD = '**********';

    protected $_websites;
    protected $_errors = array();

    /** @var Icommerce_Scheduler_Helper_Operationrunner_Abstract */
    protected $_operationRunner = null;

    /**
     * Allow substitution of operation run model
     *
     * @param Icommerce_Scheduler_Helper_Operationrunner_Abstract $runner
     */
    public function setOperationRunner(Icommerce_Scheduler_Helper_Operationrunner_Abstract $runner)
    {
        $this->_operationRunner = $runner;
    }

    public function getDefinedSchedulerOperations()
    {
        $schedulerOperations = Mage::getConfig()->getNode('global/scheduler_operations');
        if ($schedulerOperations) {
            return $schedulerOperations->asArray();
        } else {
            return array();
        }
    }

    public function getOperationStatusesOptionArray($userChangeableOnly = false)
    {
        $result = array(
            self::OPERATION_STATUS_ENABLED => $this->__('Enabled'),
            self::OPERATION_STATUS_DISABLED => $this->__('Disabled'),
        );

        if (!$userChangeableOnly) {
            $result[self::OPERATION_STATUS_RUNNING] = $this->__('Running');
            $result[self::OPERATION_STATUS_WAITING] = $this->__('Waiting');
        }

        return $result;
    }

    public function getOperationRecurrenceOptionArray($type)
    {
        switch ($type) {
            case 'frequency':
                $result = array(
//                    self::FREQUENCY_ONCE => $this->__('Run Once'),
                    self::FREQUENCY_MINUTE => $this->__('Every n Minute(s)'),
                    self::FREQUENCY_HOUR => $this->__('Every n Hour(s)'),
                    self::FREQUENCY_DAY => $this->__('Every n Day(s)'),
                    self::FREQUENCY_WEEK => $this->__('Every n Week(s)'),
                    self::FREQUENCY_MONTH => $this->__('Every n Month(s)'),
                    self::FREQUENCY_YEAR => $this->__('Every n Year(s)'),
                );
                break;
            case 'n':
                $result = array();
                for ($i = 1; $i <= 60; $i++) $result[$i] = $i;
                break;
            case 'month':
                $result = array(
                    1 => $this->__('January'),
                    2 => $this->__('February'),
                    3 => $this->__('March'),
                    4 => $this->__('April'),
                    5 => $this->__('May'),
                    6 => $this->__('June'),
                    7 => $this->__('July'),
                    8 => $this->__('August'),
                    9 => $this->__('September'),
                    10 => $this->__('October'),
                    11 => $this->__('November'),
                    12 => $this->__('December'),
                );
                break;
            case 'weekday':
                $result = array(
                    1 => $this->__('Monday'),
                    2 => $this->__('Tuesday'),
                    3 => $this->__('Wednesday'),
                    4 => $this->__('Thursday'),
                    5 => $this->__('Friday'),
                    6 => $this->__('Saturday'),
                    0 => $this->__('Sunday'),
                );
                break;
            case 'day':
                $result = array();
                for ($i = 1; $i <= 31; $i++) $result[$i] = $i;
                break;
            case 'hour':
                $result = array();
                for ($i = 0; $i <= 23; $i++) $result[$i] = sprintf('%02d', $i);
                break;
            case 'minute':
                $result = array();
                for ($i = 0; $i <= 59; $i++) $result[$i] = sprintf('%02d', $i);
                break;
            default:
                $result = array();
                break;
        }

        return $result;
    }

    public function getHistoryStatusesOptionArray($code = -1)
    {
        $statuses = array(
            self::HISTORY_STATUS_NONE => $this->__('None'),
            self::HISTORY_STATUS_SUCCEEDED => $this->__('Succeeded'),
            self::HISTORY_STATUS_FAILED => $this->__('Failed'),
            self::HISTORY_STATUS_EXCEPTIONS => $this->__('Exceptions'),
            self::HISTORY_STATUS_NOTHING_TO_DO => $this->__('Nothing to do'),
        );
        if ($code != -1) {
            if (isset($statuses[$code])) {
                return $statuses[$code];
            }   else {
                return $statuses;
            }
        } else {
            return $statuses;
        }
    }

    public function getHistoryStatusesMultiOptionArray()
    {
        return array(
            array('value' => self::HISTORY_STATUS_NONE, 'label' => $this->__('None')),
            array('value' => self::HISTORY_STATUS_SUCCEEDED, 'label' => $this->__('Succeeded')),
            array('value' => self::HISTORY_STATUS_FAILED, 'label' => $this->__('Failed')),
            array('value' => self::HISTORY_STATUS_EXCEPTIONS, 'label' => $this->__('Exceptions')),
            array('value' => self::HISTORY_STATUS_NOTHING_TO_DO, 'label' => $this->__('Nothing to do')),
        );
    }

    public function getOperationOptionArray()
    {
        /** @var $collection Icommerce_Scheduler_Model_Resource_Operation_Collection */
        $collection = Mage::getModel('scheduler/operation')->getCollection();
        $result = array();
        $operations = $this->getDefinedSchedulerOperations();

        foreach ($collection as $row) {
            $result[$row->getId()] =
                isset($operations[$row->getCode()]['label']) ? $operations[$row->getCode()]['label'] : $row->getCode();
        }

        return $result;
    }

    public function getAuthenticationTypes()
    {
        return array(
            0                       => 'None',
            CURLAUTH_BASIC          => 'HTTP Basic Authentication',
            CURLAUTH_DIGEST         => 'HTTP Digest Authentication',
            CURLAUTH_GSSNEGOTIATE   => 'GSS Negotiate',
            CURLAUTH_NTLM           => 'NTLM',
            CURLAUTH_ANY            => 'Any',
            CURLAUTH_ANYSAFE        => 'Any Safe',
        );
    }

    public function getRerunCountOptionArray()
    {
        $range = range(1, 10);

        return array_combine($range, $range);
    }

    public static function addTimezoneOffsetToRecurrence($recurrenceInfo)
    {
//        $recurrenceInfo['hour'] += 2;

        $date = new Zend_Date(time());
        $a = $date->toArray();
        $s = $date->toString();

        return $recurrenceInfo;
    }

    public function saveMessages($operation, $historyId, $messages)
    {
        foreach ($messages as $message) {
            /** @var $item Icommerce_Scheduler_Model_Message */
            $item = Mage::getModel('scheduler/message');
            $item->setData($message);
            $item->setOperationId($operation->getId());
            $item->setHistoryId($historyId);
            $item->save();
        }
    }

    /**
     * @param Icommerce_Scheduler_Model_Operation $operation
     * @param bool $runChildren
     * @return array|string
     * @throws Exception
     */
    public function runOperation($operation, $runChildren = false, $verbose = false)
    {
        $schedulerOperations = $this->getDefinedSchedulerOperations();

        if ($verbose) {
            if (isset($schedulerOperations[$operation->getCode()]['label'])) {
                echo $schedulerOperations[$operation->getCode()]['label'];
            } else {
                echo $operation->getCode();
            }
            echo '... ';
        }
        $operation->setLastRun(date('Y-m-d H:i:s'));
        $operation->setStatus(self::OPERATION_STATUS_RUNNING);
        $operation->save();

        Mage::app()->dispatchEvent(
            'icommerce_scheduler_run_operation_before',
            array('scheduler' => $this)
        );

        if ($this->_operationRunner === null) {
            $this->_operationRunner = Mage::helper('scheduler/operationrunner_cron');
        }

        return $this->_operationRunner->runOperation($operation, $runChildren, $verbose);
    }

    public function runOperations($verbose = false)
    {
        if (Mage::helper('scheduler')->isSchedulerDisabled()) {
            return;
        }

        /** @var $collection Icommerce_Scheduler_Model_Resource_Operation_Collection */
        $collection = Mage::getModel('scheduler/operation')->getCollection()->loadByNextRun();

        /** @var $operation Icommerce_Scheduler_Model_Operation */
        foreach ($collection as $operation) {
            $this->runOperation($operation, true, $verbose);
        }
    }

    public function createOperation($operationCode, $recurrenceInfo, $parameters = array(), $masterOperation = false)
    {
        $recurrenceInfoDefault = array(
            'frequency' => Icommerce_Scheduler_Helper_Data::FREQUENCY_MINUTE,
            'n' => 1,
            'hour' => 0,
            'minute' => 0,
            'weekday' => 0,
            'day' => 1,
            'month' => 1
        );

        $recurrenceInfo = array_merge($recurrenceInfoDefault, $recurrenceInfo);

        $operation = Mage::getModel('scheduler/operation')
            ->setCode($operationCode)
            ->setRecurrenceInfo($recurrenceInfo)
            ->setParameters($parameters)
            ->setMasterOrder(0)
            ->setStatus(self::OPERATION_STATUS_ENABLED);

        if ($masterOperation) {
            $operation->setMasterId($masterOperation->getId());

            // When you add a slave task, then we generate the order automatically.
            $lastOperation = Mage::getModel('scheduler/operation')->getCollection()
                ->addMasterOperationFilter($masterOperation->getId(), null, Zend_Db_Select::SQL_DESC)
                ->getFirstItem();

            if ($lastOperation->hasData()) {
                $operation->setMasterOrder($lastOperation->getMasterOrder() + 1);
            }
        }

        $operation->save();

        return $operation;
    }

    public function calculateProgress($min, $max, $position)
    {
        if ($min === null || $max === null || $position === null) {
            return false;
        }

        if ($max <= $min || $position < $min || $position > $max) {
            return false;
        }

        return floor(($position - $min) / ($max - $min) * 100);
    }

    public function getStatusHtml($operation)
    {
        switch ($operation->getStatus()) {
            case Icommerce_Scheduler_Helper_Data::OPERATION_STATUS_ENABLED:
                $color = '#3FB853';
                break;
            case Icommerce_Scheduler_Helper_Data::OPERATION_STATUS_DISABLED:
                $color = '#FF0000';
                break;
            case Icommerce_Scheduler_Helper_Data::OPERATION_STATUS_RUNNING:
                $color = '#FFA500';
                break;
            case Icommerce_Scheduler_Helper_Data::OPERATION_STATUS_WAITING:
                $color = '#FFA500';
                break;
            default:
                $color = '#000000';
                break;
        }

        $statuses = $this->getOperationStatusesOptionArray();
        if ($operation->getStatus() == self::OPERATION_STATUS_RUNNING) {
            $progress = $this->calculateProgress($operation->getProgressMin(), $operation->getProgressMax(), $operation->getProgressPos());
        } else {
            $progress = false;
        }

        $html = '<div style="margin-top: 1px; width: 120px; height: 16px; background: ' . $color . '; border-radius: 0px">';
        $html .= '<div style="background-color: cornflowerblue; width: ' . ($progress ? $progress : 0) . '%; height: 100%; border-radius: 0px">';
        $html .= '<div style="width: 120px; height: 16px; color: #FFFFFF; text-align: center; font: bold 10px/16px Arial, Helvetica, sans-serif; text-transform: uppercase">';
        $html .= (isset($statuses[$operation->getStatus()]) ? $statuses[$operation->getStatus()] : $operation->getStatus()) . ($progress ? sprintf(' [%d%%]', $progress) : '');
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function getLastStatusHtml($status)
    {
        switch ($status) {
            case self::HISTORY_STATUS_NONE:
                $color = '#FF9D00';
                break;
            case self::HISTORY_STATUS_SUCCEEDED:
                $color = '#3FB853';
                break;
            case self::HISTORY_STATUS_FAILED:
                $color = '#FF0000';
                break;
            case self::HISTORY_STATUS_EXCEPTIONS:
                $color = '#F45800';
                break;
            case self::HISTORY_STATUS_NOTHING_TO_DO:
                $color = '#3FB853';
                break;
            default:
                $color = '#000000';
                break;
        }

        $statuses = $this->getHistoryStatusesOptionArray();
        $html = '<div style="margin-top: 1px; width: 120px; height: 16px; background: ' . $color . '; border-radius: 0px">';
        $html .= '<div style="width: 120px; height: 16px; color: #FFFFFF; text-align: center; font: bold 10px/16px Arial, Helvetica, sans-serif; text-transform: uppercase">';
        $html .= isset($statuses[$status]) ? $statuses[$status] : $status;
        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    public function setOperationProgress($operationId, $min, $max, $pos)
    {
        $operation = Mage::getModel('scheduler/operation')->load($operationId);

        if ($operation->getId()) {
            $operation->setProgressMax($max)
                ->setProgressMin($min)
                ->setProgressPos($pos)
                ->save();
        }
    }

    /**
     * Checks the disabled time frame
     *
     * @return bool
     */
    public function isSchedulerDisabled()
    {
        $start = Mage::getConfig()->getNode('default/' . self::XML_PATH_DISABLE_START);
        $end = Mage::getConfig()->getNode('default/' . self::XML_PATH_DISABLE_END);
        if (empty($start) || empty ($end)) return false;

        $start_timestamp = strtotime(date("Y-m-d") . " " . $start);
        $end_timestamp   = strtotime(date("Y-m-d") . " " . $end);
        $today_timestamp = strtotime(date("Y-m-d H:i:s"));
        if ($end_timestamp<$start_timestamp) {  // If end time is less than start, start time is before midnight, must check if inside period both early and late in the day
            if ((($today_timestamp >= $start_timestamp - 86400) && ($today_timestamp <= $end_timestamp))) {
                return true;
            } elseif (($today_timestamp >= $start_timestamp) && ($today_timestamp <= $end_timestamp + 86400)) {
                return true;
            }
        } else {
            return (($today_timestamp >= $start_timestamp) && ($today_timestamp <= $end_timestamp));
        }
        return false;
    }

    public function resetCrashedTasks($verbose = false)
    {
        if (!Mage::getStoreConfig(Icommerce_Scheduler_Helper_Data::XML_PATH_RESET_CRASHED_TASKS)) {
            return;
        }

        /** @var $collection Icommerce_Scheduler_Model_Resource_Operation_Collection */
        $collection = Mage::getResourceModel('scheduler/operation_collection');
        $collection->addStatusFilter(array(self::OPERATION_STATUS_RUNNING, self::OPERATION_STATUS_WAITING));

        foreach ($collection as $operation) {
            if ($verbose) {
                echo 'Resuming task id: ' . $operation->getId() . "\n";
            }
            $operation->setStatus(self::OPERATION_STATUS_ENABLED);
            $operation->save();
            $this->saveMessages($operation, null, array(array(
                'created_at' => Mage::getSingleton('core/date')->gmtDate(),
                'status' => self::HISTORY_STATUS_SUCCEEDED,
                'message' => 'Task was resumed automatically by shell script'
            )));
        }
    }
}
