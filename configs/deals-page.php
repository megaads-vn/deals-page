<?php
 return [
     "enable" => true,
     "app_url" => 'http://couponforless.test',
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