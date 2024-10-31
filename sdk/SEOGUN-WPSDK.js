const {__} = wp.i18n;

jQuery(document).ready(function()
{

  if (!String.format)
  {
    String.format = function(format) {
      var args = Array.prototype.slice.call(arguments, 1);
      return format.replace(/{(\d+)}/g, function(match, number) {
        return typeof args[number] != 'undefined'
          ? args[number]
          : match
        ;
      });
    };
  }


  jQuery('#seogun-dialog').dialog
  ({
    title: 'SEO Gun',
    dialogClass: 'wp-dialog',
    autoOpen: false,
    draggable: false,
    width: (screen.width > 640) ? 640 : '100%',
    maxWidth: '100%',
    modal: true,
    resizable: false,
    closeOnEscape: false,
    dialogClass:"seogun_dialog",
    create: function ()
    {
      jQuery('.ui-dialog-titlebar-close').addClass('ui-button');
    }

  });

  // bind a button or a link to open the dialog
  jQuery('.open-seogun-dialog').click(function(e)
  {
    e.preventDefault();

    var selected_types = jQuery('#seogun_checkbox input:checked').map(function()
    {
      return jQuery(this).val();
    });

    if(selected_types.get().length != 0)
    {

      SEOGUN_Defaults.selected_types = selected_types.get();

      //hode error titles
      jQuery('.seogun-item').attr('title','');

      //hide unslected type
      jQuery('.seogun-item').hide();
      jQuery.each(SEOGUN_Defaults.selected_types, function( index, value ) {
        jQuery('.seogun-item-'+value).show();
      });

      //open SEOGun dialog
      jQuery('#seogun-dialog').dialog('open');

      //start SEOGun Object
      const seogun = new SEOGun(SEOGUN_Defaults);
      seogun.init();

      jQuery( "#seogun-chips" ).on( "click", ".seogun-chip-close", function() {
          seogun.remove_tag(jQuery(this).data('id'));
      });

      jQuery("#seogun-dialog").bind("dialogclose",function(e)
      {
          seogun.close_callback();
      });
    }
    else
    {
        alert(__('Please select at less one option','seogun'));
    }


  });

});

class SEOGun
{


  constructor(SEOGUN_Defaults)
  {
    this.http_url           = 'https://ai.seogun.io/';
    this.ws_hostname        = 'ai.seogun.io';

    this.token              = SEOGUN_Defaults.seogun_token;
    this.completed          = false;
    this.seogunkeywords     = [];
    this.ws_status          = [];
    this.session_id         = false;
    this.icons_url          = SEOGUN_Defaults.icons_url;
    this.icons_obj_init     = null;
    this.icons_obj_tags     = null;
    this.icons_obj_trends   = null;
    this.icons_obj_entities = null;
    this.finalstep_loading  = null;
    this.wp_language        = SEOGUN_Defaults.wp_language;
    this.editor_type        = SEOGUN_Defaults.editor_type;
    this.selected_types     = SEOGUN_Defaults.selected_types;
    this.chip_template      = SEOGUN_Defaults.templates.chip;
    this.post_id            = SEOGUN_Defaults.post_id;
    this.selected_entities  = SEOGUN_Defaults.settings.entities;
    this.google_country     = SEOGUN_Defaults.settings.google_trends_country;

    if(wp.data === undefined)
    {
      //classic editor
      this.editor_type = 'classic';
    }
    else
    {
       this.editor_type = 'gutenberg';
    }

    this.NextEventListener   = this.click_function.bind(this);
  }

  async init()
  {

    //set loading SVG for all elemnts
    this.icons_obj_init     = this.loading_element('seogun-init');
    this.icons_obj_tags     = this.loading_element('seogun-tags');
    this.icons_obj_trends   = this.loading_element('seogun-trends');
    this.icons_obj_entities = this.loading_element('seogun-entities');



    const session_request = await this.get_session_id();


    if(session_request != 'unknown_error')
    {
        if(session_request.status == true)
        {
            this.session_id = session_request.session_id;

            //setup next button action
            const seogun_next_btn = document.getElementById('seogun_nextstep');

            seogun_next_btn.addEventListener("click", this.NextEventListener,true);



            this.success_element(this.icons_obj_init,'seogun-init');

            //start WebSocket connection
            const socketProtocol  = (window.location.protocol === 'https:' ? 'wss:' : 'ws:')
            const SEOGunSocketUrl = socketProtocol + '//' + this.ws_hostname + '/ai/ws'
            const seogun_socket   = new WebSocket(SEOGunSocketUrl);

            seogun_socket.onopen = () =>
            {
              console.log('SEOGun: socket is now open ');
            }


            this.generate_ws_object().then(function(res)
            {
                seogun_socket.send(JSON.stringify(res));
            });

            seogun_socket.onmessage = e =>
            {

              var res = JSON.parse(event.data);

              if((res.status == true) || (res.status == false && res.type !== undefined))
              {
                if(res.type == 'keywords')
                {
                    this.ws_status.push('keywords');
                    if(res.status == true)
                    {
                        this.seogunkeywords.push.apply(this.seogunkeywords, res.list);
                        this.success_element(this.icons_obj_tags,'seogun-tags');
                    }
                    else
                    {
                        document.getElementById("seogun-item-keywords").title = res.message;

                        this.fail_element(this.icons_obj_tags,'seogun-tags');
                    }
                }

                if(res.type == 'entities')
                {
                    this.ws_status.push('entities');

                    if(res.status == true)
                    {
                        this.seogunkeywords.push.apply(this.seogunkeywords, res.list);
                        this.success_element(this.icons_obj_entities,'seogun-entities');
                    }
                    else
                    {
                        document.getElementById("seogun-item-entities").title = res.message;
                        this.fail_element(this.icons_obj_entities,'seogun-entities');
                    }
                }

                if(res.type == 'trends')
                {
                    this.ws_status.push('trends');

                    if(res.status == true)
                    {
                        this.seogunkeywords.push.apply(this.seogunkeywords, res.list);
                        this.success_element(this.icons_obj_trends,'seogun-trends');
                    }
                    else
                    {
                        document.getElementById("seogun-item-trends").title = res.message;
                        this.fail_element(this.icons_obj_trends,'seogun-trends');
                    }
                }

                if(this.ws_status.length == this.selected_types.length)
                {
                    //close websocket connection
                    seogun_socket.close();

                    //check if there's no keywrods found
                    if(this.seogunkeywords.length == 0)
                    {
                      //seogun_error_box
                      const error_elm = document.getElementById('seogun_error_box');
                      error_elm.classList.add("seogun_error_box_nokeywords");

                      error_elm.textContent   = __('We are sorry, There are no keywords found in your article.','seogun');
                      error_elm.style.display = 'block';
                    }
                    else
                    {

                        seogun_next_btn.style.display = 'block';
                    }

                }
              }
              else
              {
                this.unknown_error();
              }

            }

            seogun_socket.onclose = function(event)
            {
              console.log("WebSocket is closed now.");
              //TODO check if it close with error
            };

        }
        else
        {
            //chenage loading SVG to error SVG
            this.error_element(this.icons_obj_init,'seogun-init');
            this.error_element(this.icons_obj_tags,'seogun-tags');
            this.error_element(this.icons_obj_trends,'seogun-trends');
            this.error_element(this.icons_obj_entities,'seogun-entities');

            //set error message
            const error_elm = document.getElementById('seogun_error_box');
            error_elm.innerHTML   = __(session_request.message,'seogun');
            error_elm.style.display = 'block';


        }
    }
    else{
      this.unknown_error();
    }


  }


  remove_duplicate_keywords(allkeywords)
  {
     return new Promise(function(resolve, reject)
     {

       allkeywords = allkeywords.filter((thing, index, self) =>
        index === self.findIndex((t) =>
         {
           return(t.keyword === thing.keyword);
         })
      )

      resolve(allkeywords);

     });
  }


  click_function()
  {
      //fix light box scrolling issue
      window.scroll(0,0);

      const next_btn_obj = document.getElementById('seogun_nextstep');
      if(next_btn_obj.dataset.step == 1)
      {
        document.getElementById('seogun_nextstep_btn').value = __('Finish','seogun');

        next_btn_obj.dataset.step = 2;

        var html_chips = [];

        document.getElementById('seogun-ws').style.display          = 'none';
        document.getElementById('seogun-tags-select').style.display = 'block';

        const chip_tpl   = this.chip_template;

        var keyword_reverse = this.seogunkeywords.reverse();

        this.remove_duplicate_keywords(keyword_reverse).then(function(arr)
        {
          this.seogunkeywords = arr;

          this.seogunkeywords.forEach(function (item, key)
          {
            html_chips.push(String.format(chip_tpl,item.type, item.keyword,key));

            if((key+1) == this.seogunkeywords.length)
            {
                document.getElementById('seogun-chips').innerHTML = html_chips.join("\n");
            }

          }.bind(this));

        }.bind(this));


      }
      else
      {
        this.set_article_content();
      }

  }

  async set_article_content()
  {
      //fix light box scrolling issue
      window.scroll(0,0);

      var content = this.get_article_content();
      var tags    = this.seogunkeywords.filter(function(el) { return el; });

      if(tags.length > 0)
      {
           var tags_links = await this.SetKeywords(tags);


          if(tags_links.data !== undefined)
          {

              this.success_element(this.finalstep_loading ,'seogun_final_step_animated');
              document.getElementById('seogun_final_step_text').innerHTML = '<span style="color:green;">'+ __('Selected keywords has been added successfully.','seogun') +'</span>';

              //fix light box scrolling issue
              window.scroll(0,0);

              var editor_type = this.editor_type;

              var tags_ids = [];
              tags_links.data.forEach(function (tag, key)
              {
                  if(editor_type == 'classic')
                  {
                      tags_ids.push(tag.keyword);
                  }
                  else
                  {
                      tags_ids.push(tag.id);
                  }


                  content = content.replace(' '+tag.keyword+' ',' <a href="'+tag.link+'" class="seogun-tag seogun-tag-'+tag.type+'">'+ tag.keyword +'</a> ');

                  if((key+1) == tags.length)
                  {
                      //change article content
                      if(editor_type == 'classic')
                      {
                        //classic editor

                        var activeEditor = tinyMCE.get('content');

                        if(activeEditor !== null)
                        {
                            activeEditor.setContent(content);
                        }
                        else
                        {
                            jQuery('#content').val(content);
                        }

                        //set post tags
                        jQuery('#new-tag-post_tag').val(tags_ids.join(', ')).next('.button').click();

                      }
                      else
                      {

                        //edit post content
                        var editedContent = wp.data.select( "core/editor" ).getEditedPostContent();
                        wp.data.dispatch( 'core/editor' ).editPost( { content: editedContent } );
                        var seogun_blockes_content = wp.blocks.parse(content);
                        wp.data.dispatch( 'core/block-editor' ).resetBlocks([]);
                        wp.data.dispatch( 'core/block-editor' ).insertBlocks(seogun_blockes_content);

                        //set post tags
                        var current_tags = wp.data.select("core/editor").getEditedPostAttribute("tags");
                        var final_tags   = tags_ids.concat(current_tags);

                        wp.data.dispatch('core/editor').editPost({tags: final_tags});

                        //save post to draft this to fix bug on WP that tags doesn't appear after edit post
                        wp.data.dispatch('core/editor').savePost();
                      }
                  }
              });
          }
          else
          {

          }


      }
  }

  get_article_content()
  {

    var content = '';

    if(wp.data === undefined)
    {
      //classic editor
      if(tinymce.editors.content === undefined)
      {
          //text mode
          content = jQuery('#content').val();
      }
      else
      {
         content = tinymce.editors.content.getContent();
      }

    }
    else
    {
      content = wp.data.select( "core/editor" ).getEditedPostContent();
    }

    return content;

  }

  async generate_ws_object()
  {
      var keywords      = await this.GetKeywords();
      var session_id    = this.session_id;
      var content       = this.get_article_content();


      var promise =  new Promise((resolve, reject) =>
      {
            var object =
            {
              "session_id"      : session_id,
              "content"         : content,
              "lang"            : this.wp_language,
              "keywords"        : keywords,
              "options"         : this.selected_types,
              "entities"        : this.selected_entities,
              "google_country"  : this.google_country
            };


            resolve(object);
      });

      return promise;
  }

  async GetKeywords()
  {
      return new Promise(function (resolve, reject)
      {
          var params = 'action=seogun_keywords';

          var xhr = new XMLHttpRequest();
          xhr.open('POST', ajaxurl, true);
          xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
          xhr.responseType = 'json';
          xhr.onload = function ()
          {
              var status = xhr.status;
              if (status == 200)
              {
                  resolve(xhr.response);
              }else
              {
                  reject(status);
              }
          };

          xhr.send(params);
      });
  }

  async SetKeywords(tags)
  {
      var post_id = this.post_id;

      document.getElementById('seogun-tags-select').style.display = 'none';
      document.getElementById('seogun_nextstep').style.display = 'none';
      document.getElementById('seogun_final_step').style.display = 'block';

      this.finalstep_loading = bodymovin.loadAnimation
      ({
        container: document.getElementById('seogun_final_step_animated'),
        path: this.icons_url+'loader.json',
        renderer: 'svg',
        loop: true,
        autoplay: true,
      });

      document.getElementById('seogun_final_step_text').innerText = __('Loading please wait ..','seogun');

      return new Promise(function (resolve, reject)
      {
          //var params = 'action=seogun_settags&post_id='+post_id+'&tags='+tags.toString();
          var params = {action:"seogun_settags",post_id:post_id,tags:tags};

          var xhr = new XMLHttpRequest();
          xhr.open('POST', ajaxurl+'?action=seogun_settags', true);
          xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');
          xhr.responseType = 'json';
          xhr.onload = function ()
          {
              var status = xhr.status;
              if (status == 200)
              {
                  resolve(xhr.response);
              }else
              {
                  reject(status);
              }
          };

          xhr.send(JSON.stringify(params));
      });
  }

  get_session_id()
  {
    const request = new Request(this.http_url+'wp-api/request_init', {method: 'POST', body: 'api_key='+this.token+'&lang='+this.wp_language+'&domain='+window.location.host});

    return new Promise(function (resolve, reject)
    {
      fetch(request,{
        headers:
        {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
      })
        .then(response => {
          if (response.status !== 200)
          {
            //request error
            try
            {
              //check if it json so it's normal error from the API
              response.json().then(function(data) {
                resolve(data);

              });
            }
            catch(err)
            {
                resolve('unknown_error');
            }

          }
          else
          {
            response.json().then(function(data) {
              resolve(data);
            });
          }
        })
        .catch(error =>
        {
          resolve('unknown_error');
        });
    });



  }

  unknown_error()
  {

    document.getElementById('seogun-ws').style.display = 'none';
    document.getElementById('seogun_network_error').style.display = 'block';

    bodymovin.loadAnimation
    ({
      container: document.getElementById('seogun_network_error_animated'),
      path: this.icons_url+'network_error.json',
      renderer: 'svg',
      loop: false,
      autoplay: true,
    });

  }

  success_element(ele,id)
  {
    ele.destroy();

    var icon_path = this.icons_url;
    setTimeout(function()
    {
        bodymovin.loadAnimation
        ({
          container: document.getElementById(id),
          path: icon_path+'check-circle.json',
          renderer: 'svg',
          loop: false,
          autoplay: true,
          initialSegment:[0,17]
        });
   }, 100);
 }

  fail_element(ele,id)
  {

     ele.destroy();
     var icon_path = this.icons_url;
     setTimeout(function()
     {
         bodymovin.loadAnimation
         ({
           container: document.getElementById(id),
           path: icon_path+'error.json',
           renderer: 'svg',
           loop: true,
           autoplay: true
         });
    }, 100);
  }

  error_element(ele,id)
  {

     ele.destroy();
     var icon_path = this.icons_url;
     setTimeout(function()
     {
         bodymovin.loadAnimation
         ({
           container: document.getElementById(id),
           path: icon_path+'error_3.json',
           renderer: 'svg',
           loop: false,
           autoplay: true
         });
    }, 100);
  }

  loading_element(id)
  {
    var loading = bodymovin.loadAnimation
    ({
      container: document.getElementById(id),
      path: this.icons_url+'check-loader.json',
      renderer: 'svg',
      loop: true,
      autoplay: true,
    });

    return loading;
  }

  remove_tag(id)
  {
      jQuery('#seogun-chip-id-'+id).fadeOut();

      delete this.seogunkeywords[id];


      //this.seogunkeywords.splice(id, 1);
      SEOGUN_Defaults.seogunkeywords = this.seogunkeywords.filter(function(el) { return el; });

      console.log(SEOGUN_Defaults.seogunkeywords);
      if(SEOGUN_Defaults.seogunkeywords.length == 0)
      {
        jQuery('#seogun-tags-select h3').text('Sorry no more tags.');

        document.getElementById('seogun_nextstep').style.display = 'none';
      }

  }

  close_callback()
  {



    jQuery('#seogun-dialog svg').remove();



    //reset all var
    this.token              = SEOGUN_Defaults.seogun_token;
    this.completed          = false;
    this.seogunkeywords     = [];
    this.ws_status          = [];
    this.session_id         = false;
    this.icons_url          = SEOGUN_Defaults.icons_url;
    this.icons_obj_init     = null;
    this.icons_obj_tags     = null;
    this.icons_obj_trends   = null;
    this.icons_obj_entities = null;
    this.wp_language        = SEOGUN_Defaults.wp_language;
    this.editor_type        = SEOGUN_Defaults.editor_type;
    this.selected_types     = SEOGUN_Defaults.selected_types;
    this.chip_template      = SEOGUN_Defaults.templates.chip;
    this.post_id            = SEOGUN_Defaults.post_id;


    //hide unwanted divs, This to fix the porblem if user clien twice on generate button
    document.getElementById('seogun-ws').style.display = 'block';
    document.getElementById('seogun_network_error').style.display = 'none';
    document.getElementById('seogun_error_box').style.display = 'none';

    //hide tag select step
    document.getElementById('seogun-tags-select').style.display = 'none';

    //remove all chips
    document.getElementById('seogun-chips').innerHTML = '';

    //clear final step content
    document.getElementById('seogun_final_step').style.display = 'none';
    document.getElementById('seogun_final_step_text').innerText = '';

    //reset next button step number and Button Value
    const seogun_nextstep_elm = document.getElementById("seogun_nextstep");
    seogun_nextstep_elm.dataset.step = 1;
    seogun_nextstep_elm.style.display = 'none';
    document.getElementById('seogun_nextstep_btn').value = __('Next Step','seogun');

    //remove click EventListener
    seogun_nextstep_elm.removeEventListener("click", this.NextEventListener,true);
  }

}
