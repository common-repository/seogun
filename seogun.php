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


include_once(dirname(__FILE__)."/includes/seogun.class.php");
include_once(dirname(__FILE__)."/includes/settings.class.php");

add_action( 'plugins_loaded', function()
{
  load_plugin_textdomain( 'seogun', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}, 0 );


register_activation_hook( __FILE__, function()
{

    if(!get_option('seogun_settings'))
    {
      $def_settings = array
      (
        'token' => '',
        'lang'  => get_locale(),
        'entities' => array
        (
            'commercial_item' => 'on',
            'event'           => 'on',
            'location'        => 'on',
            'organization'    => 'on',
            'person'          => 'on',
            'title'           => 'on',
            'other'           => 'on'
        ),
        'google_trends_country' => 'US'
      );

      update_option('seogun_settings', $def_settings);
    }

} );


//ininiate SEOGun Setting class
new SEOGun_Settings();

//ininiate SEOGun Main class
new SEOGUN();
