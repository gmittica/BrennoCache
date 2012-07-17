<?php

/**
 * BrennoCache 
 * Copyright (c) 2012 Gabriele Mittica
 * @author Gabriele Mittica 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */ 
 
class BrennoCache {
    
    /**
     * the handler of the cache (must bn instance of a class that implements the CacheHandler interface)
     * @var bool
     */
    private $_handler;   
    
    /**
     * The domain used such as prefix for all cache entries
     * @var string
     */
    private $_domain;
    
    /** 
     * Constructor
     * @param string $cacheHandler the chache handle (apc, memcached)
     */                  
    public function __construct( $domain, $handler, $options = array() ) {
        if($domain) $this->_domain = $domain;
        else if(defined("APC_DOMAIN")) $this->_domain = APC_DOMAIN;
        $handlerClassName = "BrennoCache".ucfirst(strtolower($handler));
        if(!class_exists($handlerClassName) && file_exists($handlerClassName).".php") require_once $handlerClassName.".php";
        $this->_handler = new $handlerClassName($options);
    }
    
    /**
     * store a value in the cache
     * @param string $key the key name
     * @param mixed $value the value to store
     * @param integer $exprire the life time of the object store (in ms, 0 == no limit)               
     */         
    public function store($key, $value, $expire = 0) {
        return $this->_storeKey($key, $value, $expire);
    }
    
    /**
     * store a value in the cache by tag
     * @param string $key the key name
     * @param mixed $value the value to store     
     * @param string $tag the tag name
     * @param integer $exprire the life time of the object store (in ms, 0 == no limit)               
     */      
    public function storeByTag($key, $value, $tag, $expire = 0) {
        $result = $this->_storeKey($key, $value, $expire);
        if($result) $this->_updateTag($key, $tag);
        return $result;
    }
    /**
     * fetch a key from the cache   
     * @param string $key the key name
     * @param mixed $default the default value to return if key is not fetched
     * @return mixed the value stored or the $default value     
     *       
     */         
    public function fetch($key, $default = false) {
        return $this->_fetchKey($key, $default);
    }
    
     /**
     * fetch tagged keys from the cache   
     * @param string $key the key name
     * @param mixed $default the default value to return if key is not fetched
     * @return mixed the value stored or the $default value     
     *       
     */         
    public function fetchByTag($tag, $default = false) {
        $result = array();
        $keys = $this->_getKeysByTag($tag);
        array_pop($keys);
        foreach($keys AS $key) $result[$key] = $this->_fetchKey($key, $default);
        return $result;
    }
   
   /**
    * remove one or more keys from the cache
    * @param string|array $keys a key (or an array of keys) to remove
    * @return bool true if successeful, false instead        
    */       
    public function delete($keys) {     
        return $this->_deleteKey($keys);
    }
    
    /**
    * remove all keys with the same tag from the cache
    * @param string $tag the tag name
    * @return bool true if successeful, false instead        
    */     
    public function deleteTag($tag) {
        return $this->_deleteKey($tagList = $this->_getKeysByTag($tag));    
    }
   
    /**
    * flush the cace
    * @return bool true if successeful, false instead        
    */       
    public function deleteAll() {
        return $this->_handler->flush();    
    }
    
    
    /**
    * private method to construct the key id used by cache system
    * @param string $key the key
    * @param string $suffix the suffix     
    * @return string the private key id       
    */     
    private function _getKeyId($key, $suffix = "") {
        return strtolower(implode("_", array($this->_domain, $key)).$suffix);           
    }
    
    /**
    * private method to construct the tag id used by cache system
    * @param string $tag the tag
    * @return string the private tag id       
    */     
    private function _getTagId($tag) {
        return $this->_getKeyId($tag, "#tag");  
    }
    
    /**
    * private method to fetch keys with the same tag
    * @param string $tag the tag
    * @return array the array of keys   
    */     
    private function _getKeysByTag($tag) {
        return array_merge($this->fetch($this->_getKeyId($tag, "#tag"), array()), array($this->_getTagId($tag))); 
    }
    
    /**
    * private method to update the list of keys linked to a tag
    * @param string $id the id of the key
    * @param string $tag the tag 
    */     
    private function _updateTag($id, $tag) {
        $tagId = $this->_getTagId($tag);
        $tagList = $this->fetch($tagId, array());
        if(!in_array($id, $tagList)) $tagList[] = $id;
        $this->store($tagId, $tagList);
    }
    
    /**
    * private method to remove one or more keys from the cache
    * @param string|array $keys the key or an array of keys
    * @return bool true if successeful, false instead        
    */     
    private function _deleteKey($keys) {  
        $result = array(); 
        if(is_array($keys)) {
            foreach($keys AS $key) $result[$key] = $this->_handler->delete($this->_getKeyId($key));
        }
        return $result;
    }

    /**
    * private method to store a value in the cache
    * @param string $key the key name
    * @param integer $exprire the life time of the object store (in ms, 0 == no limit)      
    * @return the handler result        
    */     
    private function _storeKey($key, $value, $expire = 0) {
        return $this->_handler->store($this->_getKeyId($key), serialize($value), $expire);
    }

    /**
    * private method to fache a value from the cache 
     * @param string $key the key name
     * @param mixed $default the default value to return if key is not fetched
     * @return mixed the value stored or the $default value     
     *       
     */         
    private function _fetchKey($key, $default = false) {
        $result = unserialize($this->_handler->fetch($this->_getKeyId($key)));
        if(!$result) return $default;
        else return $result;
    }
    
}