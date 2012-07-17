<?php

//require BrennoCache
require_once("/../BrennoCache.php");


//create the cache manager with APC
$cache =  new BrennoCache("myapp", "apc");

//storing tagged data
$cache->storeByTag(
    "batman", //the key 
    array(
        "name" => "Bruce Wayne",
        "enemies" => array("Joker", "Two Face", "Bane", "Hush", "Mr. Freeze")
    ),
    "superheroes" //the tag
);
$cache->storeByTag(
    "spiderman", //the key 
    array(
        "name" => "Peter Parker",
        "enemies" => array("Carnage", "Hobgoblin", "Kingpin", "Venom", "Goblin")
    ),
    "superheroes" //the tag
);  

//fetching all keys stored with superheroes tag
$result = $cache->fetchByTag("superheroes");
echo "<pre>"; 
var_dump($result); 
echo "<pre>";

//deleting tag
$cache->deleteTag("superheroes");
$result = $cache->fetchByTag("superheroes");
echo "<pre>"; 
var_dump($result); 
echo "<pre>";
