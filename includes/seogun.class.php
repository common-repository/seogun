<?php
/**
 * Plugin Name: SEO Gun
 * Version:     1.2
 * Author:      SEO Gun
 * Plugin URI:  https://seogun.io
 * Text Domain: seogun
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Description: AI based wordpress plugin that help's you to enhance your SEO by linking all your article togther.
 * Domain Path: /languages
 * Compatible with WordPress 5.0.
 */
defined( 'ABSPATH' ) or die();

include_once(dirname(__FILE__)."/html.php");

class SEOGUN
{

  private $classic_editor;
  private $templates;
  private $token;

  public function __construct()
  {

      $this->classic_editor = $this->check_wp_editor();
      $this->templates = new SEOGUN_Templates();

    try
    {
        $this->init();
    }
    catch (Exception $e) {

    }
  }


  private function init()
  {
    add_action( 'add_meta_boxes', function(){
      add_meta_box( 'SEOGUN-Box', 'SEO Gun', function(){
         echo $this->get_meta_box() . $this->get_seogun_dialog();
      }, 'post', 'side', 'high' );
    });


   add_action('admin_enqueue_scripts', function($hook)
   {
     global $post;

     if(in_array($hook,array('post.php','post-new.php')))
     {
       wp_enqueue_script( 'SEOGUN_Defaults', plugins_url('sdk/SEOGUN-WPSDK.js?v='.time(), dirname(__FILE__) ),array( 'wp-i18n' ));

       wp_set_script_translations( 'SEOGUN_Defaults', 'seogun', plugin_dir_path( __FILE__ ) . 'languages');


       $seogun_settings = $this->seogun_settings();

       $wp_lang = explode('_',$seogun_settings['lang']);

       wp_localize_script('SEOGUN_Defaults', 'SEOGUN_Defaults',
       [
           'post_id'        =>  $post->ID,
           'plugin_url'     =>  plugins_url('seogun', dirname(__FILE__) ),
           'icons_url'      =>  plugins_url('sdk/icons/' , dirname(__FILE__) ),
           'seogun_token'   =>  $seogun_settings['token'],
           'editor_type'    =>  $this->get_editor_type(),
           'wp_language'    =>  strtolower($wp_lang[0]),
           'selected_types' =>  array(),
           'templates'      =>  array(
                 'chip'     =>  $this->get_seogun_chip()
           ),
           'settings'       => $seogun_settings
       ]);

       wp_enqueue_script('SEOGUN_Defaults');

       wp_enqueue_script('jquery-ui-dialog' );
       wp_enqueue_style('wp-jquery-ui-dialog' );
       wp_enqueue_script('lottie', plugins_url( 'sdk/lottie.min.js' , dirname(__FILE__) ) );

       wp_register_style('seogun_css', plugins_url( 'seogun/sdk/seogun.css'), false, '1.0.0' );
       wp_enqueue_style('seogun_css');


     }
   });


   //get keywords list
   add_action('wp_ajax_seogun_keywords', function(){
     $tags = $this->get_wp_tags();
     wp_send_json($tags);
     wp_die();
   });

   //set post tags
   add_action('wp_ajax_seogun_settags', function()
   {
     $request_body = file_get_contents('php://input');
     $data = json_decode($request_body);

     $tags = $this->set_post_tags($data->post_id,$data->tags);

     wp_send_json($tags);
     wp_die();
   });



  }


  public function get_editor_type(){
      if($this->classic_editor == true){
          return('classic');
      }
      else{
        return('gutenberg');
      }
  }

  public function get_meta_box()
  {
    return($this->templates->wp_meta_box($this->classic_editor,''));
  }

  public function get_seogun_dialog()
  {
    return($this->templates->wp_meta_box_dialog($this->get_wp_tags_count(),''));
  }

  public function get_seogun_chip()
  {
    return($this->templates->chip_template());
  }


  private function get_wp_tags_count(){
     $all_tags = get_tags();
     return(count($all_tags));
  }



  public function get_wp_tags(){
     $all_tags = get_tags(array('hide_empty' => false));
     if(is_array($all_tags) && count($all_tags) > 0){
          $list = array();
          foreach($all_tags as $k => $v){
            $list[] = $v->name;

            if($k > 15000)
            {
              break;
            }
          }

          return $list;
     }
     else
     {
       return array();
     }
     return($all_tags);
  }


  public function set_post_tags($id,$tags)
  {
     if($id && $tags)
     {

        $keywords = [];
        foreach($tags as $k => $v)
        {
            $keywords[] = $v->keyword;
        }

        $ids = wp_set_post_tags( $id, $keywords, true );


        if($ids)
        {
            $resp = [];
            foreach($ids as $k => $v)
            {

                $resp[] = array
                (
                  'id'      => $v,
                  'keyword' => $keywords[$k],
                  'link'    => get_tag_link($v),
                  'type'    => $tags[$k]->type
                );
            }

            return array('status' => true,'data' => $resp);

        }
        else{
          return array('status' => false);
        }

     }
     else
     {
       return array('status' => false);
     }
  }

  private function check_wp_editor()
  {
    $this->classic_editor = false;

    if($this->is_gutenberg_active() || $this->is_classic_editor_plugin_active()){
        $this->classic_editor = true;
    }


    return($this->classic_editor);
  }


  private function is_gutenberg_active()
  {
      // Gutenberg plugin is installed and activated.
      $gutenberg = ! ( false === has_filter( 'replace_editor', 'gutenberg_init' ) );

      // Block editor since 5.0.
      $block_editor = version_compare( $GLOBALS['wp_version'], '5.0-beta', '>' );

      if ( ! $gutenberg && ! $block_editor ) {
          return false;
      }

      if ( $this->is_classic_editor_plugin_active() )
      {
          $editor_option       = get_option( 'classic-editor-replace' );
          $block_editor_active = array( 'no-replace', 'block' );

          return in_array( $editor_option, $block_editor_active, true );
      }

      return true;
  }

  private function is_classic_editor_plugin_active()
  {
      if ( ! function_exists( 'is_plugin_active' ) )
      {
          include_once ABSPATH . 'wp-admin/includes/plugin.php';
      }

      if ( is_plugin_active( 'classic-editor/classic-editor.php' ) )
      {
          return true;
      }

      return false;
  }

  public function seogun_settings()
  {
      $wp_settings = get_option('seogun_settings');
      if($wp_settings)
      {
        $settings = $wp_settings;

        if(!isset($settings['lang']) || $settings['lang'] == '')
        {
            $settings['lang'] = 'en';
        }

        if(!isset($settings['entities']) || $settings['entities'] == '')
        {
            $settings['entities'] = array();
        }
        else
        {
          $settings['entities'] = array_keys($settings['entities']);
        }

        if(!isset($settings['google_trends_country']) || $settings['google_trends_country'] == '')
        {
            $settings['google_trends_country'] = 'US';
        }

      }
      else
      {
          $settings = array
          (
            'token'                 => null,
            'lang'                  => 'en',
            'entities'              => array('commercial_item','event','location','organization','person','title','other'),
            'google_trends_country' => 'US'
          );
      }

      return($settings);
  }


}


?>
