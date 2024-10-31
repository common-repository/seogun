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

class SEOGun_Settings
{

    public function __construct() {
    	// Hook into the admin menu
    	add_action( 'admin_menu', array( $this, 'seogun_settings_page' ) );
    	register_setting( 'seogun_fields','seogun_settings');

    }

    public function seogun_settings_page() {
    	// Add the menu item and page
    	$page_title = __('SEO Gun Settings','seogun');
    	$menu_title = __('SEO Gun','seogun');
    	$capability = 'manage_options';
    	$slug = 'seogun';
    	$callback = array( $this, 'seogun_settings_page_html' );
    	$position = 100;

      add_submenu_page( 'options-general.php', $page_title, $menu_title, $capability, $slug, $callback );
    }

    public function seogun_settings_page_html()
    {
      $seogun_settings = get_option('seogun_settings');
      ?>


              <div class="wrap">

                <h2><?php _e( 'SEO Gun Settings', 'seogun' ); ?></h2>

                <div id="poststuff">


                  <div id="post-body" class="metabox-holder columns-2">
                    <form method="post" action="options.php">

                    <!-- main content -->
                    <div id="post-body-content">

                      <div class="meta-box-sortables ui-sortable">


                        <!-- Token -->
                        <div class="postbox">



                          <h3><span><?php _e( 'Plugin Settings', 'seogun' ); ?></span></h3>
                          <div class="inside">


                              <?php settings_fields('seogun_fields'); ?>

                              <table class="form-table">

                                <tr valign="top">
                                  <th scope="row">
                                    <label for="seogun_api_token"><strong><?php _e( 'Token', 'seogun' ); ?></strong></label>
                                  </th>
                                  <td>
                                    <input id="seogun_api_token" class="all-options code" name="seogun_settings[token]" type="text" value="<?php echo $seogun_settings['token'];?>">
                                    <br /><small class="description">

                                      <?php
                                      printf(__('You don\'t have token yet? <a href="https://seogun.io/actions/?action=signup&domain=%s" target="_blank">Click here to signup.</a>', 'seogun'), $_SERVER['HTTP_HOST']);
                                      ?>
                                      </small>
                                  </td>
                                </tr>

                                <tr valign="top">
                                  <th scope="row">
                                    <label for="seogun_settings_lang"><strong><?php _e( 'Your Content Language', 'seogun' ); ?></strong></label>
                                  </th>
                                  <td>



                                    <?php
                                    wp_dropdown_languages(array(
                                      'id'       => 'seogun_settings_lang',
                                      'name'     => 'seogun_settings[lang]',
                                      'selected' => $seogun_settings['lang'],
                                      'show_option_site_default' => false,
                                      'show_option_en_us' => true
                                    ));
                                     ?>

                                    <br />

                                    <small class="description">

                                      <?php
                                      _e('Please select your article language, We are using this language to analyze your content.','seogun');
                                      ?>
                                      </small>
                                  </td>
                                </tr>


                              </table>
                          </div> <!-- .inside -->
                        </div> <!-- .postbox -->

                        <!-- Entitis settings -->
                        <div class="postbox">



                          <h3><span><?php _e( 'Entities Settings', 'seogun' ); ?></span></h3>


                           <div class="inside">



                              <?php settings_fields('seogun_fields'); ?>

                              <table class="form-table">



                                <tr valign="top">
                                  <th scope="row">
                                    <label><strong><?php _e( 'Entity Types', 'seogun' ); ?></strong></label>
                                  </th>
                                  <td>



          											   <input id="seogun_settings_commercial_item" name="seogun_settings[entities][commercial_item]" <?php echo $this->check_entities_type('commercial_item',$seogun_settings);?> type="checkbox">
                                   <label for="seogun_settings_commercial_item"><?php _e('Products','seogun') ?></label>
                                     <br />
                                     <small class="description" style="padding: 0px 23px;"><?php _e('Detect any product name in your content.','seogun');?> </small>
                                   <br><br>

                                   <input id="seogun_entities_date" name="seogun_settings[entities][date]" <?php echo $this->check_entities_type('date',$seogun_settings);?> type="checkbox">
                                    <label for="seogun_entities_date"><?php _e('Date','seogun') ?></label>
                                      <br />
                                      <small class="description" style="padding: 0px 23px;"><?php _e('Detect any date like: (11/25/2017), day (Tuesday), month (May), or time (8:30 a.m.).','seogun');?> </small>
                                    <br><br>

                                    <input id="seogun_entities_event" name="seogun_settings[entities][event]" <?php echo $this->check_entities_type('event',$seogun_settings);?> type="checkbox">
                                     <label for="seogun_entities_event"><?php _e('Events','seogun') ?></label>
                                       <br />
                                       <small class="description" style="padding: 0px 23px;"><?php _e('Detect events, such as a festival, concert, election, etc.','seogun');?> </small>
                                     <br><br>

                                     <input id="seogun_entities_location" name="seogun_settings[entities][location]" <?php echo $this->check_entities_type('location',$seogun_settings);?> type="checkbox">
                                      <label for="seogun_entities_location"><?php _e('Locations','seogun') ?></label>
                                        <br />
                                        <small class="description" style="padding: 0px 23px;"><?php _e('Detect A specific location, such as a country, city, lake, building, etc.','seogun');?> </small>
                                      <br><br>


                                      <input id="seogun_entities_organization" name="seogun_settings[entities][organization]" <?php echo $this->check_entities_type('organization',$seogun_settings);?> type="checkbox">
                                       <label for="seogun_entities_organization"><?php _e('Organizations','seogun') ?></label>
                                         <br />
                                         <small class="description" style="padding: 0px 23px;"><?php _e('Detect large organizations, such as a government, company, religion, sports team, etc.','seogun');?> </small>
                                       <br><br>


                                       <input id="seogun_entitiesـperson" name="seogun_settings[entities][person]" <?php echo $this->check_entities_type('person',$seogun_settings);?> type="checkbox">
                                        <label for="seogun_entitiesـperson"><?php _e('Persons','seogun') ?></label>
                                          <br />
                                          <small class="description" style="padding: 0px 23px;"><?php _e('Detect Individuals, groups of people, nicknames, fictional characters.','seogun');?> </small>
                                        <br><br>


                                        <input id="seogun_entitiesـtitle" name="seogun_settings[entities][title]" <?php echo $this->check_entities_type('title',$seogun_settings);?> type="checkbox">
                                         <label for="seogun_entitiesـtitle"><?php _e('Titles','seogun') ?></label>
                                           <br />
                                           <small class="description" style="padding: 0px 23px;"><?php _e('Detect an official name given to any creation or creative work, such as movies, books, songs, etc.','seogun');?> </small>
                                         <br><br>


                                         <input id="seogun_entities_other" name="seogun_settings[entities][other]" <?php echo $this->check_entities_type('other',$seogun_settings);?> type="checkbox">
                                          <label for="seogun_entities_other"><?php _e('Other','seogun') ?></label>
                                            <br />
                                            <small class="description" style="padding: 0px 23px;"><?php _e('Entities that don\'t fit into any of the other entity categories','seogun');?> </small>
                                          <br><br>

                                  </td>
                                </tr>

                              </table>
                                  <?php //submit_button();?>

                          </div> <!-- .inside -->
                        </div> <!-- .postbox -->



                        <!-- Google Trends -->
                        <div class="postbox">


                          <h3><span><?php _e( 'Google Trends Settings', 'seogun' ); ?></span></h3>
                          <div class="inside">



                              <?php settings_fields('seogun_fields'); ?>

                              <table class="form-table">



                                <tr valign="top">
                                  <th scope="row">
                                    <label for="seogun_settings_google_trends_country"><strong><?php _e( 'Google Country', 'seogun' ); ?></strong></label>
                                  </th>
                                  <td>

                                    <?php
                                        echo $this->get_google_trends_select($seogun_settings);
                                     ?>

                                    <br />

                                    <small class="description">

                                      <?php
                                      _e('Please select your article language, We are using this language to analyze your content.','seogun');
                                      ?>
                                      </small>
                                  </td>
                                </tr>


                              </table>

                          </div> <!-- .inside -->
                        </div> <!-- .postbox -->


                        <?php submit_button();?>
                        </form>

                      </div> <!-- .meta-box-sortables .ui-sortable -->
                    </div> <!-- post-body-content -->

                    <!-- sidebar -->
                    <div id="postbox-container-1" class="postbox-container">

                      <div class="meta-box-sortables">

                        <div class="postbox">
                          <h3><span><?php _e( 'Ratings & Reviews', 'seogun' ); ?></span></h3>
                          <div class="inside">
                            <p><?php _e( 'If you like <strong>SEOGun</strong> please consider leaving a', 'seogun' ); ?> <a href="https://wordpress.org/support/view/plugin-reviews/seogun?filter=5#postform" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> <?php _e( 'rating.', 'seogun' ); ?><br><?php _e( 'A huge thanks in advance!', 'seogun' ); ?></p>
                            <p><a href="https://wordpress.org/support/view/plugin-reviews/seogun?filter=5#postform" target="_blank" class="button-primary"><?php _e('Leave a rating','seogun'); ?></a></p>
                          </div> <!-- .inside -->
                        </div> <!-- .postbox -->

                        <div class="postbox">
                          <h3><span><?php _e( 'Upgrade Now', 'seogun' ); ?></span></h3>
                          <div class="inside">
                            <p><b><?php _e( 'Enjoy the premium features.', 'seogun' ); ?></b>
                              <br><?php _e( 'Upgrade your account now and benefit out of our amazing premium features.', 'seogun' ); ?></p>
                            <p><a href="https://seogun.io/actions/?action=upgrade&place=settings" target="_blank" class="button-primary"><?php _e('Upgrade Now','seogun'); ?></a></p>
                          </div> <!-- .inside -->
                        </div> <!-- .postbox -->

                      </div> <!-- .meta-box-sortables -->

                    </div> <!-- #postbox-container-1 .postbox-container -->

                  </div> <!-- #post-body .metabox-holder .columns-2 -->

                  <br class="clear">
                </div> <!-- #poststuff -->

              </div> <!-- .wrap -->



       <?php
    }

    private function check_entities_type($type,$seogun_settings)
    {
      if(isset($seogun_settings['entities']))
      {
        $entities = array_keys($seogun_settings['entities']);


          if(in_array($type,$entities))
          {
              return('checked="checked"');
          }
          else{
              return '';
          }
      }
      else{
        return '';
      }

    }

    private function get_google_trends_select($seogun_settings)
    {


      $country_list = array
      (
        "AR"      => "Argentina",
        "AU"      => "Australia",
        "AT"      => "Austria",
        "BE"      => "Belgium",
        "BR"      => "Brazil",
        "CA"      => "Canada",
        "CL"      => "Chile",
        "CO"      => "Colombia",
        "CZ"      => "Czechia",
        "DK"      => "Denmark",
        "EG"      => "Egypt",
        "FI"      => "Finland",
        "FR"      => "France",
        "DE"      => "Germany",
        "GR"      => "Greece",
        "HK"      => "Hong Kong",
        "HU"      => "Hungary",
        "IN"      => "India",
        "ID"      => "Indonesia",
        "IE"      => "Ireland",
        "IT"      => "Italy",
        "JP"      => "Japan",
        "KE"      => "Kenya",
        "MY"      => "Malaysia",
        "MX"      => "Mexico",
        "NL"      => "Netherlands",
        "NZ"      => "New Zealand",
        "NG"      => "Nigeria",
        "NO"      => "Norway",
        "PH"      => "Philippines",
        "PL"      => "Poland",
        "PT"      => "Portugal",
        "RO"      => "Romania",
        "RU"      => "Russia",
        "SA"      => "Saudi Arabia",
        "SG"      => "Singapore",
        "ZA"      => "South Africa",
        "KR"      => "South Korea",
        "SE"      => "Sweden",
        "CH"      => "Switzerland",
        "TW"      => "Taiwan",
        "TH"      => "Thailand",
        "TR"      => "Turkey",
        "UA"      => "Ukraine",
        "UK"      => "United Kingdom",
        "US"      => "United States",
        "VN"      => "Vietnam"
      );


      $selected = '';
      if(isset($seogun_settings['google_trends_country']))
      {
          $selected = $seogun_settings['google_trends_country'];
      }


      $html = '<select name="seogun_settings[google_trends_country]" id="seogun_settings_google_trends_country">';

      foreach($country_list as $k => $v)
      {
          $html_selected = '';
          if($selected == $k)
          {
              $html_selected = ' selected="selected" ';
          }

          $html .= '<option value="'.$k.'" '.$html_selected.'>'.$v.'</option>';
      }

      $html .= '</select>';

      return $html;

    }


}

?>
