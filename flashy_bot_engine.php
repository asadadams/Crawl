<?php
   require_once('simple_html_dom.php'); //Simple HTML dom library
   
   class FlashyBot{

      private $host_url;
      private $url; // URL to crawl
      private $crawled_links = array(); //array for storing  crawled links
      
      private $page_info = array(); //page info array


      public function __construct($url){ 
         $this->url = $url; //initializing url var
         $this->host_url = parse_url($url,PHP_URL_HOST); //initializing host url var
      }

      /**
       * [clean_link function to clean all links with # and .(at the begining) so that it doesn't crawl index the same url more than once and getting absolute links,with their respective protocols]
       * @param  {string} $link [link to clean]
       * @return {string}       [Returns the link]
       */
      private function clean_link($link_var){
         $link = $link_var;
         if(strpos($link, '#')){
            $link = substr($link, 0 ,strpos($link, '#'));
         }

         if(substr($link, 0,1) == '.'){
            $link = substr($link, 1);
         }

         if(substr($link, 0, 7) == 'http://')
            $link = $link;
         else if(substr($link, 0 , 8) == 'https://')
            $link = $link;
         else if (substr($link, 0 , 2 ) == '//')
            $link = substr($link,2);
         else if (substr($link, 0 , 1 ) == '#')
            $link = $this->url;
         else if (substr($link, 0 , 7 ) == 'mailto:')
            $link  = $link."[mail]";
         else if($link == 'javascript')
            $link = '';
         else{
            if(substr($link, 0 , 1) != '/'){
               $link = $this->host_url.'/'.$link;
            }else
               $link = $this->host_url.$link;
         }
         return $link;
      }

      /**
       * [final_check_url making a final check to make sure links which don't have their protocols attached to them are attached]
       * @param  {string} $link_var [link to get the absolurte link of]
       * @return {string}           [Returns the link]
       */
      private function final_check_url($link_var){
         $link = $link_var;
         if(substr($link, 0 , 7) != 'http://' && substr($link, 0, 8) != 'https://' && substr($link, 0 , 7) != 'mailto:' ){
            if(substr($this->url,0,7) == 'http://')
               $link = 'http://'.$link;
            else
               $link = 'https://'.$link;
         }
         return $link;
      }

      /**
       * [get_page_info getting info of page crawling]
       * @return [null]
       */
      private function get_page_info($link){
         $page_url = $link;
         $page_title = '';
         $page_description = '';
         $page_keywords = '';
         

         $html = new simple_html_dom(); //instance of Simple HTML dom library
         $html->load_file($link);
         if(is_object($html->find("title",0)))
            $page_title = $html->find('title',0)->innertext;

         if(is_object($html->find('meta[name="description"]',0)))
            $page_description = $html->find('meta[name="description"]',0)->content;
            
         if(is_object($html->find('meta[name="keywords"]',0)))
            $page_keywords = $html->find('meta[name="keywords"]',0)->content;


         $detail = array('url'=>$page_url, 'title'=>$page_title , 'description'=>$page_description , 'keywords'=>$page_keywords);
         if(!in_array($detail, $this->page_info))
            array_push($this->page_info, $detail);
      }

      /**
       * [crawl public function called when you want to start crawling]
       * @return {null}
       */
      public function crawl(){
         $html = new simple_html_dom(); //instance of Simple HTML dom library
         $html->load_file($this->url);
         foreach($html->find("a") as $a_tag){
           $unclean_link = $a_tag->href; //link from the page
           $clean_link = $this->clean_link($unclean_link); //Cleaned link
           $link = $this->final_check_url($clean_link); //final link

          if(substr($link, 0, 7) != 'mailto:'){ //getting page info if is not a mail link
               $this->get_page_info($link);
           }
            
           if(!in_array($link, $this->crawled_links)){
               array_push($this->crawled_links, $link);
           }
         }
         
      } 

      /**
       * [show_crawled_links for showing all crawled links]
       * @return {string} [All the links crawled]
       */
      public function show_crawled_links(){
         foreach ($this->crawled_links as $link) {
            echo $link."</br></br>";
         }
      }

      /**
       * [get_crawled_json Get crawled links in detail in json format]
       * @return [json] [returns links in detail]
       */
      public function get_crawled_json(){
         return json_encode($this->page_info,JSON_PRETTY_PRINT);
      }

   }