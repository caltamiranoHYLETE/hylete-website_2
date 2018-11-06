<?php

class Mediotype_HyleteBase_Model_Caching_Container_Justuno extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get container individual cache id
     *
     * @return string
     */
    protected function _getCacheId()
    {
        return 'MEDIOTYPE_JUSTUNO' . $this->_getIdentifier();
    }

    /**
     * Get unique identifier for cache id
     *
     * @return mixed
     */
    protected function _getIdentifier()
    {
        return microtime();
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        $blockClass = $this->_placeholder->getAttribute('block');
        $template = $this->_placeholder->getAttribute('template');

        $block = new $blockClass;
        $block->setTemplate($template);
        return $block->toHtml();
    }
}