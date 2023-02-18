<?php
 return [
     "enable" => true,
     "layouts" => [
         "extends" => [
             "name" => "frontend.layout.master",
             "params" => ["title"]
         ],
         "section" => [
             "content" => "content",
             "javascript" => "js",
             "style" => "style"
         ]
     ]
 ];