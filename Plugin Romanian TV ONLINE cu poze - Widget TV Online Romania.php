<?php
 /*
Plugin Name: Plugin Romanian TV ONLINE cu poze - Widget TV Online Romania
Plugin URI: http://www.tvlive.im
Description: Cu ajutorul acestui plugin se pot afisa gratuit pe site-ul dumneavoastra, intr-un widget, posturile TV preferate.
Author: Alexandrescu Claudiu
Version: 3.0
Author URI: http://www.tvlive.im
*/

  include_once(ABSPATH . WPINC . '/feed.php');          
   define('TV_API_PROVIDER', 'http://www.tvlive.im/');
   define('TV_API_URL', TV_API_PROVIDER.'plugin/index.php');
   define('TV_API_PIC', TV_API_PROVIDER.'plugin/logos/');

   define('TV_API_WIDTH', 500);
   define('TV_API_HEIGHT', 500);      
         
  
   function getCategAPI(){
    
    $data = array('method' => 'getCateg', 'time' => time());
    $query = http_build_query($data);
    
    $rss = fetch_feed(TV_API_URL.'?'.$query); 
    $items = $rss->get_items();
    
    foreach($items as $item){
      $categ = $item->data['data'];
      
      if(get_option('tw_categ_bifate') == TRUE){
        $categBifate = unserialize(get_option('tw_categ_bifate'));
        $checked = (in_array($categ, $categBifate)) ? 'checked' : '';
      } else {
        $checked = '';
      }
      echo '&nbsp;&nbsp;	&mdash;  &nbsp;&nbsp;<input type="checkbox" name="categorii[]" value="'.$categ.'" '.$checked.'/> '.$categ.' ';
    }
   }
  
   function getTvsCategAPI(){
    
    $categBifate = unserialize(get_option('tw_categ_bifate')); 
    $data = array('method' => 'getTvsCateg', 'cats' => $categBifate, 'time' => time());
    $query = http_build_query($data);
    
    $rss = fetch_feed(TV_API_URL.'?'.$query);
    $items = $rss->get_items();
    
    foreach($items as $item){
      $post = $item->data['data'];
      $idpost = $item->data['attribs']['']['idpost'];

      if(get_option('tw_post_bifate') == TRUE) {
        $postBifate = unserialize(get_option('tw_post_bifate'));
        $checked = (in_array($idpost, $postBifate)) ? 'checked' : '';
      } else  {
        $checked = '';
      }
      echo '      
<style type="text/css">
.tab_content li {
width:150px;
float:left;
display:inline;
padding-left: 15px;
}
</style>
<div  class="tab_content">
<ul><li><input type="checkbox" name="posturi[]" value="'.$idpost.'" '.$checked.'/> '.$post.'</li></ul>         
</div> ';
    }
   } 
   
   function getTvsAPI(){ // este setat automat la maxim 5 posturi tv per categorie
    
    // Afisam widget doar daca exista posturi bifate
    if(get_option('tw_post_bifate') == TRUE){
      $max = get_option('tw_widget_max');
            
      $postBifate = unserialize(get_option('tw_post_bifate'));
      $data = array('method' => 'getTvs', 'ids' => $postBifate);
      $query = http_build_query($data);
      $rss = fetch_feed(TV_API_URL.'?'.$query);
      $items = $rss->get_items();

      echo '<ul>';
      foreach($items as $item){
        $numecat = $item->data['attribs']['']['numecat'];
      
       echo '<li><strong style="font-size: 14px;">'.$numecat.'</strong></li>';
        $i=1;
        foreach($item->data['child']['']['post'] as $post){
          $numepost = $post['data'];
          $idpost = $post['attribs']['']['idpost'];
	

	
          
          if($i<=$max) echo '
<a href="'.TV_API_PROVIDER.'post-'.$idpost.'.html" onclick="javascript: window.open(\''.TV_API_PROVIDER.'post'.$idpost.'.html\', \'\', \'width='.TV_API_WIDTH.', height='.TV_API_HEIGHT.'\'); return false;" target="_blank" title="TvOnline '.$numepost.'">         
<img src="'.TV_API_PIC.''.$numepost.'.jpg"  alt="TvOnline '.$numepost.'"></a>';  
          $i++;
        }
      }
      
      
      
      echo '<ul>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a  href="'.TV_API_PROVIDER.'" title="Tv Online" rel="nofollow" target="_blank">preia acest widget</a>';
      echo '</ul>'; 
    } else {
      echo '<ul><li>Nu ati selectat nici un post tv!</li></ul>';
    }
   }
   
  
  function register_tw_widget($args) {
    extract($args);

    $title = get_option('tw_widget_title');
    echo $args['before_widget'];
    echo $args['before_title'].' '.$title.' '.$args['after_title'];
    getTvsAPI();
    echo $args['after_widget']; 
  }
  	  
  function register_tw_control(){
    $max = get_option('tw_widget_max');
    $title = get_option('tw_widget_title');
    
    echo '<p><label>Title: <br><input name="title" type="text" value="'.$title.'" /></label></p>';
    echo '<p><label>Posturi Tv / Categorie: <input name="max" type="text" value="'.$max.'" /></label></p>';
      
    if(isset($_POST['max'])){
      update_option('tw_widget_max', attribute_escape($_POST['max']));
      update_option('tw_widget_title', attribute_escape($_POST['title']));
    }
  }    
  
  function tw_widget() {
  	 register_widget_control('Tv ONline Widget', 'register_tw_control'); 
  	 register_sidebar_widget('Tv Online Widget', 'register_tw_widget');
  }          
   
  
   function tw_admin(){
    echo '<div class="wrap">';
    echo '<h2>Setari plugin TV Widget</h2>';
    if(isset($_POST['scategorii']) && isset($_POST['categorii'])){ 
        $categorii = serialize($_POST['categorii']);
        if(get_option('tw_categ_bifate') === FALSE){
          add_option('tw_categ_bifate', $categorii);
        } else {
          delete_option('tw_categ_bifate');
          add_option('tw_categ_bifate', $categorii);
        }
    }
    echo '<div class="widefat" style="padding: 5px">Pasul 1. Alegeti una sau mai multe categorii:<br /><br />';
    echo '<form method="post" name="categorii" target="_self">';
    getCategAPI();
    echo '<input name="scategorii" type="hidden" value="yes" />';
    echo '<br /><br /><input type="submit" name="Submit" value="Listeaza posturile Tv &raquo;" />';    
    echo '</form>';
    echo '</div>';
    echo '<br />';
    if(isset($_POST['scategorii']) && isset($_POST['categorii'])){
      echo '<div class="widefat fade" style="padding: 5px">Pasul 2. Alegeti posturile Tv pe care vreti sa le afisati: <br /><br />';
      echo '<form method="post" name="posturi" target="_self">';      
      getTvsCategAPI();
      echo '<input name="sposturi" type="hidden" value="yes" />';
      echo '<br>';
      echo '<input type="submit" name="Submitt" value="Salveaza selectia &raquo;" />';    
      echo '</form>';
      echo '</div>';   
    }
    if(isset($_POST['sposturi']) && isset($_POST['posturi'])){
        $posturi = serialize($_POST['posturi']);
        if(get_option('tw_post_bifate') === FALSE){
          add_option('tw_post_bifate', $posturi);
        } else {
          delete_option('tw_post_bifate');
          add_option('tw_post_bifate', $posturi);
        }
        echo '<div id="message" class="updated fade"><p><b>Selectia dumneavoastra a fost salvata !</b></p></div>';        
      }    
    echo '</div>';
   }

  
  function tw_addpage() {
    add_menu_page('Plugin TV Online', 'Plugin TV Online', 10, __FILE__, 'tw_admin');
  }
  
  
  function tw_install(){
    add_option('tw_widget_max', '5');
    add_option('tw_widget_title', 'Tv Online');
  }
  
  function tw_uninstall(){
    delete_option('tw_widget_max');
    delete_option('tw_widget_title');
    delete_option('tw_post_bifate');
    delete_option('tw_categ_bifate');
  }     
  
  
  add_action('admin_menu', 'tw_addpage');
  add_action("plugins_loaded", 'tw_widget');
  register_activation_hook(__FILE__, 'tw_install');
  register_deactivation_hook(__FILE__, 'tw_uninstall');    
?>
