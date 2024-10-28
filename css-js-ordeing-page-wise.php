<?php
/*
Plugin Name: Add Css And Js Option Page Wise
Plugin URI: 
Version: 1.0
Description: Add Css And Js Option Page Wise
Author: 
Author URI: 
License: GPL2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit;

define('OCSSJS_PLUGIN_URL', plugin_dir_url(__FILE__));
define( 'OCSSJS_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
if(!class_exists('OCSSJS')) {
    class OCSSJS{
            public function __construct(){
                add_action( 'admin_enqueue_scripts', array($this, 'OCSSJS_scripts') );
                add_action( 'pre_get_posts', array($this, 'OCSSJS_insert_script_data') );
                add_action( 'add_meta_boxes', array($this, 'OCSSJS_add_oredring_metabox') );
            }
            public function OCSSJS_scripts()
            {
                wp_enqueue_script('custom-ajax', OCSSJS_PLUGIN_URL . 'js/custom.js', array(), '1.0.0', true);
                wp_enqueue_style('reorder-admin', OCSSJS_PLUGIN_URL . 'css/reorder-admin.css', array(), '0.1.0', 'all');
                wp_enqueue_script('jquery-ui-sortable' );
                wp_enqueue_script( 'custom-ajax' );
                wp_localize_script( 'custom-ajax', 'ajax_object', array('ajax_url' => admin_url( 'admin-ajax.php' )) );
                
            }
            public function OCSSJS_insert_script_data($query)
            {
                if (!is_admin()) {
                    // Not a query for an admin page.
                    // It's the main query for a front end page of your site.
                    global $post, $wp_styles,$wp_scripts;
                   
                    if(isset($post->ID)){
                        $post_id = $post->ID;
                    }
                    $s_count = count($wp_styles->queue);
                    $j_count = count($wp_scripts->queue);
                    if ($s_count > 0) {
                        $css = get_post_meta($post->ID, 'css_style_data', true);
                        if(!$css){
                        update_post_meta($post_id, 'css_style_data', $wp_styles->queue);
                        }
                    }
                    if ($j_count> 0){
                        $js = get_post_meta($post->ID, 'js_script_data', true);
                        if(!$js){
                        update_post_meta($post_id, 'js_script_data', $wp_scripts->queue);
                        }
                    }
                }
            }
            public function OCSSJS_add_oredring_metabox()
            {
                add_meta_box('re_order', // ID attribute of metabox
                'Re-Order JS & Css', // Title of metabox visible to user
                'OCSSJS_re_order_inner_metabox', // Function that prints box in wp-admin
                'page', // Show box for posts, pages, custom, etc.
                'advanced', // Where on the page to show the box
                'high'); // Priority of box in display order
            }
           
                
        }
        function OCSSJS_re_order_inner_metabox($post)
        {
            $data = get_post_meta($post->ID, 'css_style_data', true);
            $js = get_post_meta($post->ID, 'js_script_data', true);
            //print_r($data);
            $data_new= '';
           
            $data_new.= '<p style="color:red;">For CSS & JS Reordering Once You Need To Reload Your Page From Front End Side.';
            $data_new.= '<div class="OCSSJS-reorder-cont">';
            $data_new.= '<div class="OCSSJS-reorder-css-cont">';
            $data_new.= '<h3>CSS Ordering</h3>';
            $data_new.= '<input type="hidden" name="post_id" id="post_id" value="'.$post->ID.'"/>';
            $data_new.= '<ul class="reorder-css-list ui-sortable" id="sortable">';
            if($data){
            foreach($data as $d)
                {
                        $data_new.= '<li id="' . $d . '" class="ui-state-default" >' . $d . '</li>';
                }
            }
            $data_new.= '</ul>';
          
            $data_new.= '</div>';
            
            $data_new.= '<div class="OCSSJS-reorder-js-cont">';
            $data_new.= '<h3>JavaScript Ordering</h3>';
            $data_new.= '<input type="hidden" name="post_id" id="post_id" value="'.$post->ID.'"/>';
            $data_new.= '<ul class="reorder-js-list ui-sortable" id="sortable">';
            $jquery_array = array(
                'jquery-core',
                'jquery-migrate',
                'jquery'
            );
            foreach($jquery_array as $jq){
                $data_new.= '<li id="' . $jq . '" style="display:none;" class="ui-state-default" >' . $jq . '</li>';
            }
            if($js){
            $result=array_diff($js,$jquery_array);
            foreach($result as $j)
                {
                 
                        $data_new.= '<li id="' . $j . '" class="ui-state-default" >' . $j . '</li>';
                  
                }
            }
            $data_new.= '</ul>';
            $data_new.= '</div>';
            $data_new.= '</div>';
            $data_new.= '<button class="button button-primary show_field" id="reorder-save">Save New Order</button>';
            echo $data_new;
        }
    function OCSSJS_load_re_ordered_css_js() {
        global $post,$wp_scripts,$wp_styles;
       

            remove_action('wp_head', 'wp_print_scripts');
            remove_action('wp_head', 'wp_print_head_scripts', 9);
            remove_action('wp_head', 'wp_enqueue_scripts', 1);
            add_action('wp_footer', 'wp_print_scripts', 5);
            add_action('wp_footer', 'wp_enqueue_scripts', 5);
            add_action('wp_footer', 'wp_print_head_scripts', 5);

            $post_id=$post->ID;
            $css_keys = get_post_meta($post_id,'css_style_data', true);
            if($css_keys){
                foreach($css_keys as $currentKey) {
                    $keyToSplice = array_search($currentKey,$wp_styles->queue);
                    if ($keyToSplice!==false && !is_null($keyToSplice)) {
                        $elementToMove = array_splice($wp_styles->queue,$keyToSplice,1);
                        $wp_styles->queue[] = $elementToMove[0];
                    }
        
                }
            }
            
            $js_keys = get_post_meta($post_id,'js_script_data', true);
            if($js_keys){
                foreach($js_keys as $currentKey) {
                    $keyToSplice = array_search($currentKey,$wp_scripts->queue);
                    if ($keyToSplice!==false && !is_null($keyToSplice)) {
                        $elementToMove = array_splice($wp_scripts->queue,$keyToSplice,1);
                        $wp_scripts->queue[] = $elementToMove[0];
                    }
                }
            }

        
    }
    add_action( 'wp_enqueue_scripts', 'OCSSJS_load_re_ordered_css_js', 100 );
    
        add_action( 'wp_ajax_OCSSJS_update_reorder', 'OCSSJS_update_reorder' );
    
        function OCSSJS_update_reorder(){
            // $new_css_order = array();
            // $new_js_order = array();
            $ajaxResponse = array('jQueryScripts' => array());
            if (isset($_POST['css_order']) && isset($_POST['post_id']) || isset($_POST['jsorder']))
            {
                    $post_id=$_POST['post_id'];
                    $css_order=$_POST['css_order'];
                     // update_post_meta( $post_id,'css_style_data',$css_order);
                      $jsorder=$_POST['jsorder'];
                      //update_post_meta( $post_id,'js_script_data',$jsorder);
                    //   $js_store_order = $js_order;
                    //   foreach($js_store_order as $key => $value) {
                    //     array_push($new_js_order, stripslashes(sanitize_text_field($value)));
                    //  }
                    //   foreach($css_order as $key => $value) {
                    //     array_push($new_css_order, stripslashes(sanitize_text_field($value)));
                    //     }
                        $updateTasks = array('css_style_data' => $css_order,'js_script_data' => $jsorder);
					
					 //If any of these are true, return success ,if all are false, return failure
					 foreach($updateTasks as $key =>$value) {
					     $result = update_post_meta($post_id,$key, $value);
						  if($result) $success = true;
					 }
                      $response = ($success)? 1: 0;

                      $class = ($response) ? 'OCSSJS-feedback updated' : 'OCSSJS-feedback error';
                      $message = ($response) ? 'Settings updated successfully' : 'Settings could not be updated';
                      $feedback = "<div class='{$class}'><p>". $message. "</p></div>";
                      //Send the feedback and the new JS script order to the JavaScript
                      $ajaxResponse['feedback'] = $feedback;
                      $ajaxResponse['newJSOrder'] = $js_order;
                      echo json_encode($ajaxResponse, true);
                      
                      die();

            }
        }
        function OCSSJS_css_js_ordering_admin_notice(){
            if ( is_admin() ) {
                 echo '<div class="notice notice-warning is-dismissible">
                     <p>For CSS & JS Reordering Once You Need To Reload Your Page From Front End Side.</p>
                 </div>';
            }
        }
        add_action('admin_notices', 'OCSSJS_css_js_ordering_admin_notice');
       
        

    }
if(class_exists('OCSSJS')) {
    register_deactivation_hook( __FILE__, array('OCSSJS' , 'deactivate'));
   
    $OCSSJS = new OCSSJS();
}