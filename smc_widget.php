<?php
/*
Plugin Name: Social Media Icons Widget
Plugin URI: http://www.arstropica.com
Description: Plugin/Widget for Social Media Icons 
Author: Akin Williams
Version: 1.0
Tags: social media, icons, facebook, twitter, Digg, email, flickr, googleplus, linkedin, Reddit, rss, social, stumbleupon, vimeo, wordpress, yelp, youtube
Author URI: http://arstropica.com
*/

define('SMC_PLUGIN_FILE', __FILE__);
define('SMC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('SMC_PLUGIN_PATH', trailingslashit(dirname(__FILE__)));
define('SMC_PLUGIN_DIR', trailingslashit(WP_PLUGIN_URL) . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));
if ( !defined( 'WP_PLUGIN_DIR' ) )
    define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

register_deactivation_hook(__FILE__, 'smc_3_unregister');
register_activation_hook(__FILE__, 'smc_3_preregister');
add_action('widgets_init', 'smcWidgetReg');
add_action( 'admin_print_styles-widgets.php', 'smc_3_admin_styles' );
add_action( 'admin_print_scripts-widgets.php',  'smc_3_admin_scripts' );
if( is_active_widget( false, false, 'smcwidget' ) ){
    add_action('wp_print_styles', 'smc_3_front_styles');
}

function smc_3_preregister() {
    add_option('default_iconset', 'Default');
    add_option('first_save', 1);
}

function smc_3_unregister() {
    delete_option( 'default_iconset' );
    delete_option( 'first_save' );
}

function smcWidgetReg() {
    register_widget('SMCWidget');
}

function smc_3_admin_styles(){
    $styles_dir = SMC_PLUGIN_DIR . "styles/";
    wp_enqueue_style('smc-admin-style',$styles_dir . 'smc_admin.css');
}
function smc_3_admin_scripts(){
    $scripts_dir = SMC_PLUGIN_DIR . "js/";
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-tooltip',$scripts_dir . 'tooltip.min.js', array('jquery'));
    wp_enqueue_script('smc-admin-script',$scripts_dir . 'smc_admin.js', array('jquery', 'jquery-tooltip'));
}
function smc_3_front_styles(){
    $styles_dir = SMC_PLUGIN_DIR . "styles/";
    wp_enqueue_style('smc-widget-style',$styles_dir . 'smc_front.css');
}

class SMCWidget
    extends WP_Widget
    {
        public function SMCWidget(){
        $widget_ops=array
            (
            'classname'   => 'SMCWidget',
            'description' => 'Social Media Icons'
            );

            $control_ops=array ( 'width' => 300, 'height' => 300 );

            $this->WP_Widget('SMCWidget', __('Social Media Icons'), $widget_ops, $control_ops); 
            
        }
        
        function update($new_instance, $old_instance) {
            $first_save = get_option('first_save');
            if ($first_save == 1) $first_save = 0;
            update_option('first_save', $first_save);
            
            $instance = $old_instance;
            $default_rss = get_bloginfo_rss('rss2_url');
            $iconsetpath = SMC_PLUGIN_PATH . "images/iconset/" . $new_instance['selected_iconset'];
            $icon_array = array();
            foreach (glob($iconsetpath . "/*.png") as $filename) {
                $icon_name = basename($filename, ".png");
                $icon_array[$icon_name] = $icon_name;
            }

            $value_array = array_merge(array(
                "selected_iconset" => "selected_iconset",
                "title" => "title",
                "plural" => "plural",
                "facebook" => "facebook",
                "twitter" => "twitter",
                "rss" => "rss",
                "youtube" => "youtube",
                "contact" => "contact",
                "flickr" => "flickr",
                "linkedin" => "linkedin",
                "delicious" => "delicious",
                "digg" => "digg",
                "buzz" => "buzz",
                "stumbleupon" => "stumbleupon",
                "reddit" => "reddit",
                "vimeo" => "vimeo",
                "yelp" => "yelp",
                "website" => "website",
                "target" => "target"), $icon_array);
            foreach ( $value_array as $val ) {
                $new_value = isset($new_instance[$val]) ? strip_tags( $new_instance[$val] ) : "";
                $instance[$val] = $new_value;
            }
            return $instance;
        }
        
        function form( $instance ) {
            global $current_user;
            $author_meta = $this->authorinfo();
            get_currentuserinfo();
            $is_authorized = ((current_user_can('manage_options') || ($current_user->ID == $author_meta->ID)) ? true : false);
            $iconsetpath = SMC_PLUGIN_PATH . "images/iconset/";
            $iconsetfolders = $this->get_dirs($iconsetpath);
            $option_default_iconset = get_option('default_iconset', 'Default');
            $first_save = get_option('first_save');
            $default_rss = ($first_save == 1) ? get_bloginfo_rss('rss2_url') : "";
            
            $defaults = array(
                'selected_iconset'  => $option_default_iconset,
                'title'             => 'Connect with me',
                'rss'               => $default_rss,
                'plural'            => false,
                'target'            => '_blank'
              );
            $instance = wp_parse_args( (array) $instance, $defaults );
            extract( $instance, EXTR_OVERWRITE);

            # Output the options
            echo '<p style="text-align:right;"><label for="' . $this->get_field_name('title') . '">' . __('Title:') . ' <input style="width: 250px;" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . $title . '" /></label></p>';
            echo "<div id='widget_icon_setup'>\n";
            echo "<fieldset>\n";
            echo "<legend>Social Media Icons</legend>\n";
            echo "<div class=\"seltheme_container\">\n";
            echo "<label> Select a Theme: \n";
            echo "<select name='" . $this->get_field_name('selected_iconset') . "' id='" . $this->get_field_id('selected_iconset') . "' class='smc_theme_select'>\n";
            foreach ($iconsetfolders as $iconfolder)
                {
                echo "<OPTION value=\"" . $iconfolder . "\"" . ($iconfolder == $selected_iconset ? " selected=\"selected\"" : "") . ">" . ucwords($iconfolder) . " Theme</OPTION>";
                }
            echo "</select>\n";
            echo "</label>\n";
            echo "</div>\n";
            echo wp_nonce_field('smc-update-widget', 'smc_widget_update', true, false); 
            echo "<div class=\"iconset_container\">\n";
            echo "<div class=\"smc_utility nopad\">";
            echo "<p class=\"helper small fl\"><a id=\"more_info\" href=\"#\">&nbsp;</a></p>\n";
            echo "<!-- tooltip element -->\n";
            echo "<div class='tooltip'>\n";
            echo "<table style='margin:0'>\n";
            echo "<tr>\n";
            echo "<td colspan='2' class='label'><h3>Social Media Icon Widget Help</h3></td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td>1.</td>\n";
            echo "<td>Select an icon theme from the drop-down menu.</td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td>2.</td>\n";
            echo "<td>Click an available icon to reveal the URL text box below.</td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td>3.</td>\n";
            echo "<td>Enter a URL in the text field &amp; click save to update the widget.</td>\n";
            echo "</tr>\n";
            echo "<tr>\n";
            echo "<td></td>\n";
            echo "<td><strong>Note: Icons without a set url will not be displayed.</strong>"  . "</td>\n";
            echo "</tr>\n";
            echo "</table>\n";
            echo "</div>\n";
            echo "<p class=\"helper small fl\"><span>Tip: Click icon to set its URL...</span></p>\n";
            echo "</div>\n";
            foreach (glob($iconsetpath . $selected_iconset . "/*.png") as $filename) {
                $icon_name = basename($filename, ".png");
                $instance_value = isset(${$icon_name}) ? ${$icon_name} : "";
                $icon_value = (isset($instance_value)) ? $instance_value : "";
                if ($icon_name == "contact") {
                    echo $this->contact_pages($selected_iconset, $filename, $icon_value);
                } elseif ($icon_name == "googleplus") {
                    $icon_value = (empty($icon_value) ? 0 : $icon_value);
                    echo $this->icon_field($selected_iconset, $filename, $icon_value);
                } else {
                    echo $this->icon_field($selected_iconset, $filename, $icon_value);
                }
            }
            echo "<div class=\"iconset_container_bdr\">\n";
            foreach (glob($iconsetpath . $selected_iconset . "/*.png") as $filename) {
                $icon_name = basename($filename, ".png");
                echo $this->icon_image($selected_iconset, $filename);
            }
            echo "</div>\n";
            echo "<div class=\"smc_utility\">";
            echo "<label style='margin-top: 5px; display: block; clear: both;'>Use \"our\" instead of \"my\" in icon text?\n";
            echo "<input class=\"checkbox\" type=\"checkbox\" " . checked( (bool) $plural, '1', false ) . "id=\"" . $this->get_field_id( 'plural' ) . "\" name=\"" . $this->get_field_name( 'plural' ) . "\" value=\"1\" />";
            echo "</label>\n";
            echo "<label style='margin-top: 5px; display: block; clear: both;'>Open links in a new tab or window?\n";
            echo "<input class=\"checkbox\" type=\"checkbox\" " . checked( $target, '_blank', false ) . "id=\"" . $this->get_field_id( 'target' ) . "\" name=\"" . $this->get_field_name( 'target' ) . "\" value=\"_blank\" />";
            echo "</label>\n";
            echo "</div>\n";
            echo "</div>\n";
            echo "</fieldset>\n";
            echo "</div>\n";
        }
        
        function widget( $args, $instance ) {
            extract( $args );
            $title = apply_filters('widget_title', esc_attr($instance['title']));
            echo $before_widget;
            if(!empty($title)) {
                echo $before_title.$title.$after_title;
            }
            $this->get_smcWidget($instance);    
            echo $after_widget;
        }
        
        function get_SMCWidget($instance){
            $author_meta = $this->authorinfo();
            $default_rss = get_bloginfo_rss('rss2_url');
            $option_default_iconset = get_option('default_iconset', 'Default');
            foreach ($instance as $optionname => $optionvalue) {
                $var_name = "smc_" . $optionname;
                ${$var_name} = $optionvalue;
            }
            $iconsetpath = SMC_PLUGIN_PATH . "images/iconset/";
            if (isset($smc_selected_iconset)) {
                $icon_mask = $iconsetpath . $smc_selected_iconset . "/*.png";
            } else {
                $default_iconset = get_option('default_iconset', 'Default');
                $icon_mask = $iconsetpath . $default_iconset . "/*.png";
            }
            $html="<!-- BEGIN SOCIAL MEDIA CONTACTS -->\n";
            $html.="<div id=\"socialmedia-container\">\n";
            foreach (glob($icon_mask) as $filename) {
                $icon_name = basename($filename, ".png");
                $icon_varname = "smc_" . $icon_name;
                $instance_value = isset(${$icon_varname}) ? ${$icon_varname} : "";
                $icon_url = (empty($instance_value)) ? false : $instance_value;
                if ($icon_url) $html .= $this->build_widget_icon($filename, $icon_url, $smc_plural, $smc_target);
            } 
            $html.="</div>\n";
            $html.="<!-- END SOCIAL MEDIA CONTACTS -->\n";
            echo $html;
        }
        
        function build_widget_icon($icon, $iconlink="", $smc_plural=false, $smc_target='_blank'){
            $icon_info = pathinfo($icon);
            $icon_name = $icon_info['filename'];
            $icon_theme = basename($icon_info['dirname']);
            $output= "";
            $iconsrc = str_replace(array(rtrim(ABSPATH, '/\\'), '\\'), array(rtrim(get_bloginfo('url'), '/\\'), '/'), $icon);
            $src = file_exists($icon) ? $iconsrc : false;
            if ($src){
                if ($icon_name == "contact") {
                    $iconlink = get_permalink($iconlink);
                    $output.="<div id=\"social-" . $icon_name . "\" class=\"smc_icon_container $icon_theme\"><a href=\"" . $iconlink . "\" title=\"Link to " . (empty($smc_plural) ? "my" : "our") . " " . ucwords($icon_name) . " Page\"><img alt=\"Link to " . (empty($smc_plural) ? "my" : "our") . " " . ucwords($icon_name) . "\" width=\"32\" height=\"32\" src=\"" . $src . "\" /></a></div>";
                } elseif ($icon_name == "googleplus"){
                    if (empty($iconlink) || $iconlink === 0){ 
                        return $output;
                    } else {
                        $output .=  "<div id=\"social-" . $icon_name . "\" class='googleplus smc_icon_container " . $icon_theme . "'>\n";
                        $output .=  "<div class='googlehider'>\n";
                        $output .=  "<g:plusone annotation='none'></g:plusone>\n";
                        $output .=  "\n";
                        $output .=  "<!-- Place this render call where appropriate -->\n";
                        $output .=  "<script type='text/javascript'>\n";
                        $output .=  "window.___gcfg = {lang: 'en-GB'};\n";
                        $output .=  "\n";
                        $output .=  "(function() {\n";
                        $output .=  "var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;\n";
                        $output .=  "po.src = 'https://apis.google.com/js/plusone.js';\n";
                        $output .=  "var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);\n";
                        $output .=  "})();\n";
                        $output .=  "</script>\n";
                        $output .=  "</div>\n";
                        $output .=  "<img alt=\"Recommend this page!\" title=\"Recommend this page!\" width=\"32\" height=\"32\" src='" . $src . "' class='mygoogle' />\n";
                        $output .=  "</div>\n";
                    }
                } else {
                    $iconlink = $this->addhttp($iconlink);
                    $output.="<div id=\"social-" . $icon_name . "\" class=\"smc_icon_container $icon_theme\"><a href=\"" . $iconlink . "\" " . (($smc_target == '_blank') ? "target=\"_blank\" " : "") . "title=\"Link to " . (empty($smc_plural) ? "my" : "our") . " " . ucwords($icon_name) . " Page\"><img alt=\"Link to " . (empty($smc_plural) ? "my" : "our") . ucwords($icon_name) . "\" width=\"32\" height=\"32\" src=\"" . $src . "\" /></a></div>";
                }
            }
            return $output;
        }
        
        function authorinfo(){
            $blog_admin_email = (function_exists('get_blog_option') ? (get_blog_option( $this->current_blog_var->blog_id,'admin_email' )) : get_option('admin_email'));
            $blog_admin_info = get_user_by_email($blog_admin_email);
            $blog_owner_id = (function_exists('get_user_id_from_string') ? (get_user_id_from_string( $blog_admin_email )) : ($blog_admin_info->ID));
            $authordata = get_userdata( $blog_owner_id );
            return $authordata;
        }

        function addhttp($url) {
            if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
                $url = "http://" . $url;
            }
            return $url;
        }
        
        function icon_field($directory_name, $icon, $value="", $onclick=""){
            $icon_info = pathinfo($icon);
            $icon_name = $icon_info['filename'];
            $output = "";
            $disabled = (!file_exists($icon)) ? " disabled\" disabled = \"disabled" : "";
            $onclick = (empty($onclick) ? "" : " onclick =\"if(this.value=='" . $onclick . "'){this.value='';}\"");
            $output.= "<p class=\"icon_url_input\" id=\"" . $icon_name . "_icon_url_input\" style=\"display: none; \">\n";
            $output.= "<label>" . ($icon_name == "googleplus" ? "Enable " : "") . ucwords($icon_name) . " Icon:&nbsp;\n";
            if($icon_name == "googleplus")
                $output.= "<input id=\"smc_profile_url_" . $icon_name . "\" name=\"" . $this->get_field_name($icon_name) . "\" value=\"1\" type=\"checkbox\" class=\"smc_widget_url_input" . $disabled . "\" " . checked($value, "1", false) . " style=\"width: auto!important; margin-top: 5px;\">\n";
            else
                $output.= "<input id=\"smc_profile_url_" . $icon_name . "\" name=\"" . $this->get_field_name($icon_name) . "\" value=\"" . $value . "\" type=\"text\" class=\"smc_widget_url_input" . $disabled . "\"" . $onclick . ">\n";
            $output.= "</label>\n";
            $output.= "</p>\n";
            return $output;
        }

        function icon_image($directory_name, $icon){
            $output = "";
            $icon_info = pathinfo($icon);
            $icon_name = $icon_info['filename'];
            $iconurl = str_replace(rtrim(ABSPATH, '/\\'), rtrim(get_bloginfo('url'), '/\\'), $icon);
            
            $src = file_exists($icon) ? $iconurl : SMC_PLUGIN_DIR . "images/" . "unknown.png";
            $output .="<div class='icon_container' id='" . $icon_name . "icon_container'>\n";
            $output .="<img alt=\"" . ucwords($directory_name) . " Theme: " . ucwords($icon_name) . " Icon\" src=\"" . $src . "\" height=\"32\" width=\"32\" />\n";
            $output .="<span class=\"" . (file_exists($icon) ? "" : "icon_disabled") . "\">" . ucwords($icon_name) . "</span>";
            $output .=" </div>\n";
            return $output;
        }
        
        function get_dirs($path = '.') {
            $dirs = array();
            foreach (new DirectoryIterator($path) as $file) {
                if ($file->isDir() && !$file->isDot()) {
                    $dirs[] = $file->getFilename();
                }
            }
            return $dirs;
        }

        function contact_pages($directory_name, $icon, $value=""){
            $icon_info = pathinfo($icon);
            $icon_name = $icon_info['filename'];
            $disabled = (!file_exists($icon)) ? "disabled\" disabled = \"disabled" : "";
            $smc_pages=get_pages();
            $wp_pages=array(0 => "Choose a Page");
            $output = "";
            foreach ($smc_pages as $smc_pages_list) { $wp_pages[ "$smc_pages_list->ID" ]=$smc_pages_list->post_title; }
            $output.= "<p class=\"icon_url_input\" id=\"" . $icon_name . "_icon_url_input\" style=\"display: none; \">\n";
            $output.= "<label>" . ucwords($icon_name) . " Icon:&nbsp;\n";
            $output.= "<select id=\"smc_profile_url_" . $icon_name . "\" name=\"" .  $this->get_field_name('contact') . "\" class=\"smc_widget_url_input " . $disabled . "\">\n";
            foreach ($wp_pages as $pageid => $pagetitle) {
                $output.= "<option value = \"" . (($pageid === 0) ? "" : $pageid) . "\" " . ($value == $pageid ? "selected=\"selected\"" : "") . " class=\"" . $pageid . "\"" . ">" . $pagetitle . "</option>\n";
                #if ($pageid == 'leadgen') $output .= "<option disabled='disabled'>&mdash;</option>\n";
            }        
            $output.= "</select>\n";
            $output.= "</label>\n";
            $output.= "</p>\n";
            return $output;
        }       
        
    }
