<?php

//require BrennoCache
require_once("/../BrennoCache.php");

//create the cache manager with APC
$cache =  new BrennoCache("myapp", "apc");

//storing values
$cache->store("key1", "data1");
$cache->store(
    "batman", 
    array(
        "name" => "Bruce Wayne",
        "enemies" => array("Joker", "Two Face", "Bane", "Hush", "Mr. Freeze")
    )
);  


$result = $cache->fetch("batman");
echo "<pre>"; var_dump($result); echo "<pre>";