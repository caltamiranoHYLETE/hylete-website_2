<?php

class Vaimo_Cms_Helper_Bootstrap extends Mage_Core_Helper_Abstract
{
    const ALL = 0x1;

    const DESKTOP = 0x2;
    const TABLET = 0x4;
    const MOBILE = 0x8;

    const COLUMN_PREFIX = 'col-';
    const PREFIX_HIDDEN = 'hidden-';

    public function addClasses($row, $devices = self::DESKTOP)
    {
        if ($devices & (self::MOBILE | self::ALL)) {
            $row = $this->_addClassesForDevice($row, self::COLUMN_PREFIX, 'xs', self::MOBILE);
        }

        if ($devices & (self::TABLET | self::ALL)) {
            $row = $this->_addClassesForDevice($row, self::COLUMN_PREFIX ,'sm', self::TABLET);
        }

        if ($devices & (self::DESKTOP | self::ALL)) {
            $row = $this->_addClassesForDevice($row, self::COLUMN_PREFIX, 'md', self::DESKTOP);
            $row = $this->_addClassesForDevice($row, self::COLUMN_PREFIX, 'lg', self::DESKTOP);
        }

        return $row;
    }

    protected function _addClassesForDevice($row, $columnPrefix, $deviceSize, $type)
    {
        $lastEnd = 1;
        $prefix = $columnPrefix . $deviceSize . '-';
        $widthWithContent = 0;

        foreach ($row as &$item) {
            $offset = $item['col'] - $lastEnd;
            $lastEnd = $item['col'] + $item['size_x'];

            if (!isset($item['class'])) {
                $item['class'] = array();
            } else {
                $item['class'] = explode(' ', $item['class']);
            }

            $size = $item['size_x'];

            if ($type == self::TABLET) {
                switch ($size) {
                    case 12: $size = 12;
                        break;
                    case 11: $size = 12;
                        break;
                    case 10: $size = 10;
                        break;
                    case 9: $size = 9;
                        break;
                    case 8: $size = 8;
                        break;
                    case 7: $size = 7;
                        break;
                    case 6: $size = 6;
                        break;
                    case 5: $size = 5;
                        break;
                    case 4: $size = 4;
                        break;
                    case 3: $size = 3;
                        break;
                    case 2: $size = 2;
                        break;
                    case 1: $size = 0;
                        break;
                }
            } elseif ($type == self::MOBILE) {
                switch ($size) {
                    case 12: $size = 12;
                        break;
                    case 11: $size = 12;
                        break;
                    case 10: $size = 12;
                        break;
                    case 9: $size = 12;
                        break;
                    case 8: $size = 12;
                        break;
                    case 7: $size = 12;
                        break;
                    case 6: $size = 12;
                        break;
                    case 5: $size = 12;
                        break;
                    case 4: $size = 12;
                        break;
                    case 3: $size = 12;
                        break;
                    case 2: $size = 0;
                        break;
                    case 1: $size = 0;
                        break;
                }
            }

            $widthWithContent += $size;

            if ($size > 0) {
                $item['class'][] = $prefix . $size;
            } else {
                $item['class'][] = self::PREFIX_HIDDEN . $deviceSize;
            }

            if ($offset > 0 && $type != self::MOBILE) {
                $item['offset'] = $prefix . 'offset-' . $offset;
            }
        }

        foreach ($row as &$item) {
            if ($widthWithContent < 12 && isset($item['offset'])) {
                $item['class'][] = $item['offset'];
            }

            unset($item['offset']);

            $item['class'] = implode(' ', $item['class']);
        }

        return $row;
    }
}