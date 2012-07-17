<?php

/**
 * BrennoCache 
 * Copyright (c) 2012 Gabriele Mittica
 * @author Gabriele Mittica 
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */ 
 
 
interface BrennoCacheInterface {
    
    /**
     * constructor
     * @param array $options
     */               
    public function __construct($options = array());
    
    /**
     * store a value in the cache
     * @param string $key the identifier for the value
     * @param mixed $value the data to store in the cache
     * @param integer $expire the expire time (seconds)
     * @return bool
     */                                   
    public function store($key, $value, $expire = 0);
    
    /**
     * delete a value from the cache
     * @param string $key the identifier for the value
     * @return bool
     */    
    public function delete($key);
                        
    /**
     * get a value from the cache
     * @param string $key the identifier for the value
     * @return mixed the value store in the cache
     */    
    public function fetch($key);
                         
    /**
     * destroy the cache (delete all entries in the cache)
     * @return bool
     */    
    public function flush();

}