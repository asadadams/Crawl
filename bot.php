<?php
   require_once('flashy_bot_engine.php');

   $url = "http://localhost/softvision";
   $flashy = new FlashyBot($url);
   $flashy->crawl();
   $flashy->show_crawled_links();
   echo $flashy->get_crawled_json();