<?php
/*
Plugin Name: biz-things
Version: 0.0.1
Description: Adds a widget that displays your Google Places data.
Author: Gary Kovar
Author URI: http://binarygary.com
*/

require_once( dirname( __FILE__ ) . "/includes/googlePlaces.php" );

function biz_things_menu() {
	add_menu_page( 'Biz Things', 'Biz Things', 'manage_options', 'bizthings-plugin', 'biz_things_settings' ,'dashicons-store');
	
}
add_action( 'admin_init', 'bizthings_add_settings_field' );
add_action('admin_menu','biz_things_menu');

//Get the users google places key
function bizthings_add_settings_field() {
		
	register_setting('biz_things_settings-group', 'biz_things_google_places_id', 'esc_attr');
	register_setting('biz_things_settings-group', 'biz_things_google_places_search', 'esc_attr');
  register_setting('biz_things_settings-group', 'biz_things_google_places_placeid', 'esc_attr');
}

function biz_things_settings() {
	?>
	<div class="wrap">
	<h2>Biz Things</h2>

	<form method="post" action="options.php">
	    <?php settings_fields( 'biz_things_settings-group' ); ?>
	    <?php do_settings_sections( 'biz_things_settings-group' ); ?>
	    <table class="form-table">
	      <tr valign="top">
	        <th scope="row">Google Place API Web Service Key:</th>
	        <td><input type="text" class="regular-text" name="biz_things_google_places_id" value="<?php echo esc_attr( get_option('biz_things_google_places_id') ); ?>" /></td>
        </tr>        
        <tr valign="top">
          <th scope="row">Business Name and Location (search):</th>
          <td><input type="text" class="regular-text" name="biz_things_google_places_search" value="<?php echo esc_attr( get_option('biz_things_google_places_search') ); ?>" />
          <?php
            if (get_option('biz_things_google_places_search')!=NULL) {
              $result=bizthings_search(get_option('biz_things_google_places_search'));
		          update_option( 'biz_things_google_places_placeid', $result);
              $googleResponse=bizthings_placeDetails($result);
              echo "<h4>Here's the place Biz Things thinks you're talking about:</h4>";
              echo "<h5>".$googleResponse['name']."<BR>";
              echo $googleResponse['formattedAddress']."</h5>";
              //echo "<h6>".$result."</h6>";
            } else {
              echo "<h4>Unfortunately the infortmation you have provided was not specific enough.</h4>";
            }
          ?>
          </td>
        </tr>
	    </table>
    
	    <?php submit_button(); ?>

	</form>
</div>
<?php
}


class bizthings_widget extends WP_Widget {
 
  function __construct() {
    parent::__construct(false, 'Biz Things' );
  }

  function form($instance) {
    if ($instance) {
      $title = esc_attr($instance['title']);
    } else {
      $title='';
    }
    ?>
    <p>
      <label for="<?php echo $this->get_field_id('title'); ?>">Widget Title</label>
      <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
    </p>
    <?php
  }
  
  function update($new_instance,$old_instance) {
    $instance=$old_instance;
    $instance['title']=strip_tags($new_instance['title']);
    return $instance;
  }

  function widget($args, $instance) {
    extract( $args );
    $title = apply_filters('widget_title', $instance['title']);
    echo $before_widget;
    echo '<div class="widget-text bizthings_widget_plugin_box">';
    echo $before_title.$title.$after_title;
    $placeId=get_option('biz_things_google_places_placeid');
    $placeArray=bizthings_placeDetails($placeId);
    
    if ($placeArray['openNow']==1) {
			echo "<h5>Open Now!</h5>";
		}
    
    $hoursList="<UL>";
			if (count($placeArray['hours'])>1) {
				foreach ($placeArray['hours'] as $hoursOfOperation) {
					$hoursList.="<LI>$hoursOfOperation";
				}
			}
		$hoursList.="</UL>";
    echo $hoursList;
    
    //echo '<p class="wp_widget_plugin_textarea">Biz Things TextArea</p>';
    echo "</div>";
    echo $after_widget;
  }
 
}
add_action('widgets_init', create_function('', 'return register_widget("bizthings_widget");'));