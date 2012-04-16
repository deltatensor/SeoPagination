<?php

/**
 * SeoPagination Paginator
 *  - Responsible for creating the rel="next" and rel="prev" links where necessary
 *
 * @category    Dh
 * @package     Dh_SeoPagination
 * @author      Drew Hunter <drewdhunter@gmail.com>
 */
class Dh_SeoPagination_Model_Paginator extends Mage_Core_Model_Abstract
{
    /*
     * @var Mage_Catalog_Model_Resource_Product_Collection $_productCollection
     */
    protected $_productCollection;
    
     /*
     * @var Mage_Catalog_Block_Product_List_Toolbar $_toolbar
     */
    protected $_toolbar;
    
    public function _construct()
    {
        $this->_initProductCollection();
        parent::_construct();
    }
    
    /**
     * Initialise the product collection - it will be prepared based on the current 
     * category and layer
     *
     * @return Dh_SeoPagination_Model_Paginator
     */
    protected function _initProductCollection()
    {
        $toolbar = $this->_getToolbar();
        
        if ($layer = Mage::getSingleton('catalog/layer')) {
            $this->_productCollection = $layer->getProductCollection();
            $this->_productCollection->setPageSize($toolbar->getLimit());
        }

        return $this;
    }
    
    protected function _getToolbar()
    {
        if (is_null($this->_toolbar)) {
            $this->_toolbar = Mage::app()->getLayout()->createBlock('catalog/product_list_toolbar');
        }
        
        return $this->_toolbar;
    }
    
    /**
     * Initialise the pager and toolbar etc
     *
     * @return Mage_Page_Block_Html_Pager
     */
    protected function _getPager()
    {
        return Mage::app()->getLayout()->createBlock('page/html_pager')
                ->setLimit($this->_getToolbar()->getLimit())
                ->setCollection($this->_productCollection);       
    }
    
    /**
     * Create the next and prev rel links. There are 3 possible scenarios:
     * 1. Both next and Prev to be output
     * 2. Only next to be output
     * 3. Only prev to be output
     * 
     * So, decide here what should be output
     *
     * @return Dh_SeoPagination_Model_Paginator
     */
    public function createLinks()
    {
        $pager = $this->_getPager();
        $numPages = count($pager->getPages());

        //Need this to add the links to later on
        $headBlock = Mage::app()->getLayout()->getBlock('head');
        
        //Determine exactly what needs to be output and 
        //add to the head block
        if (!$pager->isFirstPage() && !$pager->isLastPage() && $numPages > 2 ) {
            $headBlock->addLinkRel('prev', $pager->getPreviousPageUrl());
            $headBlock->addLinkRel('next', $pager->getNextPageUrl());
        }
        elseif($pager->isFirstPage() && $numPages > 1) {
            $headBlock->addLinkRel('next', $pager->getNextPageUrl());
        }
        elseif($pager->isLastPage() && $numPages > 1) {
            $headBlock->addLinkRel('prev', $pager->getPreviousPageUrl());
        }
        
        if (! Mage::helper('seopagination')->useCanonical()) {
            $this->removeCanonical($headBlock);
        }
        
        return $this;
    }
    
    /**
     * Should canonical links be used in conjunction with the next and previous
     * seo links?  If not then remove from headblock here
     * 
     * @param Mage_Page_Block_Html_Head
     * @return Dh_SeoPagination_Model_Paginator
     */
    public function removeCanonical($headBlock)
    {
        $categoryUrl = Mage::registry('current_category')->getUrl();
        $headBlock->removeItem('link_rel', $categoryUrl);
        
        return $this;
    }
}