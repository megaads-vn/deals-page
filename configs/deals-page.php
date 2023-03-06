<?php
 return [
     "enable" => true,
     "site_name" => "couponforless",
     "app_url" => "https://couponforless.com",
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
     ],

     /**
        SERVICE CONFIGURE SECTION
      */
     "service" => [
         "domain" => "https://service.coupon.megaads.vn",
         "token" => "ajsdf435kjdsjf43t343",
     ],

     "queue" => [
         "enable" => true,
         "host" => "127.0.0.1",
         "port" => 4730,
     ],
 ];