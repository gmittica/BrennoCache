In computer science, a cache is a component that transparently stores data so that future requests for that data can be served faster. In the last years this system has become more and more appreciated by web developers who use caching systems to optimize the performance of their web applications. In PHP, some of the most common cache engine are APC, memcache, memcached...

## Why BrennoCache?

BrennoCache is a small tool, part of the Brenno Library that I'm developing. Each tool in the library has a specified goal: in this case, provide a smart way to:

  - create a layer over various cache systems

  - integrate a tagging service in the cache handler

The last point is very important: usually php cache doesn't provide a way to store and fetch data by  a common tag, that could be useful when you need to split up you data keeping a membership tag.

## First, the interface

The consistency must be guaranteed over the handler chosen. In order to do this, we set a simple interface like this one:

```php

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

```

## Then, the cache handler

Here the implementation of one of the most common open source framework in php: APC (Alternative PHP Cache):

```php

class BrennoCacheApc implements BrennoCacheInterface {

    public function __construct($options = array()) {}

    public function store($key, $value, $expire = 0) {

        return apc_store($key, $value, $expire);

    }

    public function delete($key) {

        return apc_delete($key);

    }

    public function fetch($key) {

        return apc_fetch($key);

    }

    public function flush() {

        return apc_clear_cache();

    }    

} 

```

## Finally, the BrennoCache Class

In less than 100 code lines (without comments), we provide a class that will be able to store and manage data and tags through the selected cache handler (APC, memcache or your own caching system). Take care to the first parameter of the constructor: you have to set a cache domain that will be used such as suffix for all your cache entries (so you'll be able to separate cache repositories of different webapps on the same cache engine).

Here the public methods of the class:      

```php

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

        if($domain) $this-&gt;_domain = $domain;

        else if(defined("APC_DOMAIN")) $this-&gt;_domain = APC_DOMAIN;

        $handlerClassName = "BrennoCache".ucfirst(strtolower($handler));

        if(!class_exists($handlerClassName) && file_exists($handlerClassName).".php") require_once $handlerClassName.".php";

        $this-&gt;_handler = new $handlerClassName($options);

    }

    /**

     * store a value in the cache

     * @param string $key the key name

     * @param mixed $value the value to store

     * @param integer $exprire the life time of the object store (in ms, 0 == no limit)               

     */         

    public function store($key, $value, $expire = 0) {

        return $this-&gt;_storeKey($key, $value, $expire);

    }

    /**

     * store a value in the cache by tag

     * @param string $key the key name

     * @param mixed $value the value to store     

     * @param string $tag the tag name

     * @param integer $exprire the life time of the object store (in ms, 0 == no limit)               

     */      

    public function storeByTag($key, $value, $tag, $expire = 0) {

        $result = $this-&gt;_storeKey($key, $value, $expire);

        if($result) $this-&gt;_updateTag($key, $tag);

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

        return $this-&gt;_fetchKey($key, $default);

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

        $keys = $this-&gt;_getKeysByTag($tag);

        array_pop($keys);

        foreach($keys AS $key) $result[$key] = $this-&gt;_fetchKey($key, $default);

        return $result;

    }

   /**

    * remove one or more keys from the cache

    * @param string|array $keys a key (or an array of keys) to remove

    * @return bool true if successeful, false instead        

    */       

    public function delete($keys) {     

        return $this-&gt;_deleteKey($keys);

    }

    /**

    * remove all keys with the same tag from the cache

    * @param string $tag the tag name

    * @return bool true if successeful, false instead        

    */     

    public function deleteTag($tag) {

        return $this-&gt;_deleteKey($tagList = $this-&gt;_getKeysByTag($tag));    

    }

    /**

    * flush the cace

    * @return bool true if successeful, false instead        

    */       

    public function deleteAll() {

        return $this-&gt;_handler-&gt;flush();    

    }

```      

Then, a set of private methods:      

```php

  /**

    * private method to construct the key id used by cache system

    * @param string $key the key

    * @param string $suffix the suffix     

    * @return string the private key id       

    */     

    private function _getKeyId($key, $suffix = "") {

        return strtolower(implode("_", array($this-&gt;_domain, $key)).$suffix);           

    }

    /**

    * private method to construct the tag id used by cache system

    * @param string $tag the tag

    * @return string the private tag id       

    */     

    private function _getTagId($tag) {

        return $this-&gt;_getKeyId($tag, "#tag");  

    }

    /**

    * private method to fetch keys with the same tag

    * @param string $tag the tag

    * @return array the array of keys   

    */     

    private function _getKeysByTag($tag) {

        return array_merge($this-&gt;fetch($this-&gt;_getKeyId($tag, "#tag"), array()), array($this-&gt;_getTagId($tag))); 

    }

    /**

    * private method to update the list of keys linked to a tag

    * @param string $id the id of the key

    * @param string $tag the tag 

    */     

    private function _updateTag($id, $tag) {

        $tagId = $this-&gt;_getTagId($tag);

        $tagList = $this-&gt;fetch($tagId, array());

        if(!in_array($id, $tagList)) $tagList[] = $id;

        $this-&gt;store($tagId, $tagList);

    }

    /**

    * private method to remove one or more keys from the cache

    * @param string|array $keys the key or an array of keys

    * @return bool true if successeful, false instead        

    */     

    private function _deleteKey($keys) {  

        $result = array(); 

        if(is_array($keys)) {

            foreach($keys AS $key) $result[$key] = $this-&gt;_handler-&gt;delete($this-&gt;_getKeyId($key));

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

        return $this-&gt;_handler-&gt;store($this-&gt;_getKeyId($key), serialize($value), $expire);

    }

    /**

    * private method to fache a value from the cache 

     * @param string $key the key name

     * @param mixed $default the default value to return if key is not fetched

     * @return mixed the value stored or the $default value     

     *       

     */         

    private function _fetchKey($key, $default = false) {

        $result = unserialize($this-&gt;_handler-&gt;fetch($this-&gt;_getKeyId($key)));

        if(!$result) return $default;

        else return $result;

    }

```

## Examples

BrennoCache is very simple to use. You can easily manage your cache increasing the performance of your web application.

In the first example, we'll store and fetch a simple data:     

```php

//require BrennoCache

require_once("/../BrennoCache.php");

//create the cache manager with APC

$cache =  new BrennoCache("myapp", "apc");

//storing values

$cache-&gt;store("key1", "data1");

$cache-&gt;store(

    "batman", 

    array(

        "name" => "Bruce Wayne",

        "enemies" => array("Joker", "Two Face", "Bane", "Hush", "Mr. Freeze")

    )

);  

$result = $cache-&gt;fetch("batman");

var_dump($result);

```

The code printed by the var_dump        

```html

array

  'name' => string 'Bruce Wayne' (length=11)

  'enemies' => 

    array

      0 => string 'Joker' (length=5)

      1 => string 'Two Face' (length=8)

      2 => string 'Bane' (length=4)

      3 => string 'Hush' (length=4)

      4 => string 'Mr. Freeze' (length=10) 

```

In the second example, we'll store, fetch and destroy tagged data:     

```php

//require BrennoCache

require_once("/../BrennoCache.php");

//create the cache manager with APC

$cache =  new BrennoCache("myapp", "apc");

//storing tagged data

$cache-&gt;storeByTag(

    "batman", //the key 

    array(

        "name" => "Bruce Wayne",

        "enemies" => array("Joker", "Two Face", "Bane", "Hush", "Mr. Freeze")

    ),

    "superheroes" //the tag

);

$cache-&gt;storeByTag(

    "spiderman", //the key 

    array(

        "name" => "Peter Parker",

        "enemies" => array("Carnage", "Hobgoblin", "Kingpin", "Venom", "Goblin")

    ),

    "superheroes" //the tag

);  

//fetching all keys stored with superheroes tag

$result = $cache-&gt;fetchByTag("superheroes");

var_dump($result); 

//deleting tag

$cache-&gt;deleteTag("superheroes");

$result = $cache-&gt;fetchByTag("superheroes");

var_dump($result); 

```

The code printed by the two var_dump calls (before and after the delete command)        

```html

array

  'batman' => 

    array

      'name' => string 'Bruce Wayne' (length=11)

      'enemies' => 

        array

          0 => string 'Joker' (length=5)

          1 => string 'Two Face' (length=8)

          2 => string 'Bane' (length=4)

          3 => string 'Hush' (length=4)

          4 => string 'Mr. Freeze' (length=10)

  'spiderman' => 

    array

      'name' => string 'Peter Parker' (length=12)

      'enemies' => 

        array

          0 => string 'Carnage' (length=7)

          1 => string 'Hobgoblin' (length=9)

          2 => string 'Kingpin' (length=7)

          3 => string 'Venom' (length=5)

          4 => string 'Goblin' (length=6)

array

  empty  

```

The tag system can help you to group and list your data in the cache. A cool feature!
