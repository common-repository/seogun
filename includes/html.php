<?php
/*
* SEOGun.io AI based WordPress plugin.
* Created by Ahmed Elsayed.
* Date: 3/7/20
* Time: 10:46 PM
*/

class SEOGUN_Templates
{

    public function wp_meta_box($classic_editor,$html)
    {

        $html .= '<label class="components-base-control__label" for="inspector-textarea-control-1">'.__('Select link types:','seogun').'</label>';


        $html .= ($classic_editor == false) ? '<br /><br />':'';

        $html .= '<div id="seogun_checkbox"><p>
                      <input type="checkbox" id="seogun_links_keywords" value="keywords" name="seogun_links_keywords" checked />
                      <label for="seogun_links_keywords">'.__('Tags','seogun').'</label>
                  </p>
                  <p>
                      <input type="checkbox" id="seogun_links_entities" value="entities" name="seogun_links_entities" checked />
                      <label for="seogun_links_entities">'.__('Entities','seogun').'</label>
                  </p>
                  <p>
                      <input type="checkbox" id="seogun_links_trends" value="trends" name="seogun_links_trends" checked />
                      <label for="seogun_links_trends">'.__('Google Trends','seogun').'</label>
                  </p></div>';

        if($classic_editor == false)
        {
            $html .= '<div>
                        <button type="button" class="components-button editor-post-publish-panel__toggle is-button is-primary open-my-dialog" style="margin: 0px;"> '.__('Generate Tags','seogun').'</button>
                      </div>';
        }
        else
        {
            $html .= '<div class="major-publishing-actions">
                          <div class="publishing-action">
                          		<input type="button" name="seogun" class="button button-primary button-large open-seogun-dialog" value="'.__('Generate Tags','seogun').'"></div>
                          <div class="clear"></div>
                      </div>';
        }


        return $html;

    }


    public function wp_meta_box_dialog($tags_count,$html)
    {
        $html = '<div id="seogun-dialog" class="hidden">
          <div class="SEOGUN-box" id="SEOGUN-box">
              <div class="seogun_error_box" id="seogun_error_box">
              </div>
              <div id="seogun-ws">
                  <h3 class="seogun-title"> '.__('We are porcceing your article content now:','seogun').'</h3>

                  <ul>
                      <li>
                          <div class="seogun-icon" id="seogun-init" ></div>
                          <p>'.__('Validate your plugin token.','seogun').'</p>
                      </li>

                      <li class="seogun-item seogun-item-keywords" id="seogun-item-keywords">
                        <div class="seogun-icon" id="seogun-tags" ></div>
                        <p>'. sprintf(__('Compare your article content to all your tags <small>( %s tag)</small>.', 'seogun'), number_format($tags_count)) .'</small></p>
                      </li>

                      <li class="seogun-item seogun-item-entities" id="seogun-item-entities">
                        <div class="seogun-icon" id="seogun-entities" ></div>
                        <p>'.__('Entities generating process.','seogun').'</p>
                      </li>

                      <li class="seogun-item seogun-item-trends" id="seogun-item-trends">
                        <div class="seogun-icon" id="seogun-trends" ></div>
                        <p>'.__('Check if your article matched with Goolge Trends keywords.','seogun').'</p>
                      </li>
                  </ul>
              </div>
              <div id="seogun-tags-select">
                  <h3 class="seogun-title">'.__('Remove unwanted tags:','seogun').'</h3>
                  <div id="seogun-chips">

                  </div>
              </div>
              <div class="publishing-action" data-step="1" id="seogun_nextstep"> <input type="button" id="seogun_nextstep_btn" name="seogun_next" class="button" value="'.__('Next Step','seogun').'" style=" width: 100px; "></div>

              <div class="seogun_network_error" id="seogun_network_error"> <div id="seogun_network_error_animated"> </div> <h4> '.__('Network error. Please try again later.','seogun').'</h4> </div>

              <div class="seogun_final_step" id="seogun_final_step"> <div id="seogun_final_step_animated"> </div> <h4 id="seogun_final_step_text"></h4> </div>
          </div>
       </div>';

       return $html;
    }


    public function chip_template(){

      $html = '<div class="seogun-chip" id="seogun-chip-id-{2}">
        <div class="seogun-chip-head seogun-chip-{0}"></div>
        <div class="seogun-chip-content">{1}</div>
        <div class="seogun-chip-close" data-id="{2}">
            <svg class="seogun-chip-svg" focusable="false" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2C6.47 2 2 6.47 2 12s4.47 10 10 10 10-4.47 10-10S17.53 2 12 2zm5 13.59L15.59 17 12 13.41 8.41 17 7 15.59 10.59 12 7 8.41 8.41 7 12 10.59 15.59 7 17 8.41 13.41 12 17 15.59z"></path></svg>
        </div>
      </div>';

      $html = trim(preg_replace('/\s\s+/', ' ', $html));

      return($html);

    }


}


?>
