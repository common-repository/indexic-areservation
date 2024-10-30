<?php
	
/*
Plugin Name: indexic aReservation
Plugin URI: https://indexic.net/
Description: Easily integrate Indexic's aReservation Tour Booking and Rental Reservation Software (3rd party software) into your WordPress website.
Version: 1.3.1
Author: indexic, inc.
Author URI: http://indexic.net
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

Copyright 2020 indexic, inc.
*/


define('areservation_options_version', '1.3.1');

// BUTTON
add_shortcode( 'ares_button', function( $atts ){
    
    extract( shortcode_atts( array(
		'id' => 'standard',
		'url' => '',
	), $atts ) );
	
	ob_start();
	
	if( ! empty( $id ) ){
	
		$areservation_options = get_option('areservation_options');
		
		if( ! empty( $areservation_options['buttons_v3'][ $id ]['html_output'] ) ){
		
			$html_output = $areservation_options['buttons_v3'][ $id ]['html_output'];
			
			if( ! empty( $url ) )
				$html_output = preg_replace('/ href="([^"]*)"/',' href="'. esc_url( $url ) .'"', $html_output );
			
			echo $html_output;
			
		}
	
	}
	
	return ob_get_clean();
	
});

add_action('wp_footer', function(){

	?>
	<script type="text/javascript">
		(function($){
			
			$('a.ares-button').each(function(index, el) {
				
				$(el).attr('style', $(el).data('default-style') ).hover(function() {
					
					$(el).attr('style', $(el).data('hover-style') );
					
				}, function() {
					
					$(el).attr('style', $(el).data('default-style') );
					
				});
				
			});
			
		})(jQuery);
	</script>
	<?php

}, 1000);

add_filter( 'widget_text', 'do_shortcode' );

add_action( 'admin_enqueue_scripts', function(){
    
    $screen = get_current_screen();
					
	if( ! empty( $screen->id ) && $screen->id == 'settings_page_indexic-areservation' ){
	
	    wp_enqueue_style( 'ares-color-picker-style', plugins_url( 'assets/color-picker.min.css', __FILE__ ), array(), '20200824' );
	    wp_enqueue_style( 'ares-customization-style', plugins_url( 'assets/style.css', __FILE__ ), array(), '20200824' );
	    wp_enqueue_script( 'ares-color-picker-script', plugins_url( 'assets/color-picker.min.js', __FILE__ ), array( 'jquery' ), '20200824', true );
	    //wp_enqueue_script( 'ares-script', plugins_url( 'assets/script.js', __FILE__ ), array( 'jquery' ), '20200824', true );
	    wp_enqueue_script( 'ares-admin-script', plugins_url( 'assets/admin-script.js', __FILE__ ), array( 'jquery' ), '20200824', true );
		
	}
    
}, 100);





function areservation_options_validate( $input ){ return $input; }

function ares_rgba_to_hex( $color = '' ){
	
	$color = explode( ',', preg_replace('/[^0-9,.]+/', '', $color ) );
	
	if( count( $color ) == 3 )
		return sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2] );
	elseif( count( $color ) == 4 )
		return sprintf("#%02x%02x%02x%02x", $color[0], $color[1], $color[2], min( 255, round( $color[3] * 255 ) ) );
	else
		return '#000000';
	
}

function ares_array_merge_recursive_distinct( &$array1, &$array2 ){
  $merged = $array1;

  foreach ( $array2 as $key => &$value )
  {
    if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
    {
      $merged [$key] = ares_array_merge_recursive_distinct ( $merged [$key], $value );
    }
    else
    {
      $merged [$key] = $value;
    }
  }

  return $merged;
}

register_activation_hook( __FILE__, function(){
	
	$data = get_option('areservation_options');
	
	if( empty( $data['version'] ) || $data['version'] != areservation_options_version ){
	
		update_option( 'areservation_options', array(
			'version' => areservation_options_version,
			'scripts' => array( 'https://link.areservation.com/aResLinkPopOver.js' )
		));
	
	}

});

class areservation_Options{
	
	public static $data = array();
	public static $default_buttons_data = array(
		'html_output' => '',
		'title' => '',
		'url' => '',
		'url_format' => '',
		'new_tab' => '0',
		'heading' => '',
		'event_name' => '',
		'group_id' => '',
		'shortcode_additional_atts' => '',
		'default' => array(
			'text_color' => 'rgba(8,30,102,1)',
			'text_size' => '18',
			'text_weight' => '400',
			'bg_color' => 'rgba(250,162,27,1)',
			'horizontal_padding' => '15',
			'vertical_padding' => '8',
			'border_color' => 'rgba(0,0,0,1)',
			'border_size' => '0',
			'border_radius' => '3'
		),
		'hover' => array(
			'text_color' => 'rgba(8,30,102,1)',
			'text_size' => '18',
			'text_weight' => '',
			'bg_color' => 'rgba(250,162,27,1)',
			'horizontal_padding' => '15',
			'vertical_padding' => '8',
			'border_color' => 'rgba(0,0,0,1)',
			'border_size' => '0',
			'border_radius' => '3'
		)
	);
	public static $default_buttons_data_by_type = array(
		'standard' => array(
			'default_title' => 'Book now',
			'heading' => 'Standard Button',
			'url_format' => 'https://link.aReservation.com/event/{UrlFriendlyCompanyName}',
			//'shortcode_additional_atts' => ' url="ENTER_URL_HERE"'
		),
		'event' => array(
			'default_title' => 'Book now',
			'heading' => 'Event Button',
			'url_format' => 'https://link.aReservation.com/event/{UrlFriendlyCompanyName}/{EventName}',
		),
		'group_id' => array(
			'default_title' => 'Book now',
			'heading' => 'Group ID Button',
			'url_format' => 'https://link.aReservation.com/event/{UrlFriendlyCompanyName}?GroupID={GroupID}',
		),
		'event_cal' => array(
			'default_title' => 'Book now',
			'heading' => 'Event Calendar Button',
			'url_format' => 'https://link.aReservation.com/eventCalendar/{UrlFriendlyCompanyName}'
		),
		'shopping_cart' => array(
			'default_title' => 'Cart',
			'heading' => 'Shopping Cart Button',
			'url_format' => 'https://link.aReservation.com/Cart/{UrlFriendlyCompanyName}'
		),
	);
	
	public static function build_data(){
		
		self::$data = get_option('areservation_options');
		
		if( empty( self::$data['url_friendly_company_name'] ) )
			self::$data['url_friendly_company_name'] = '';
		
		if( ! empty( self::$data['buttons_v3'] ) ){
		
			foreach( self::$data['buttons_v3'] as $key => $value ){
				
				self::$data['buttons_v3'][ $key ] = ares_array_merge_recursive_distinct( self::$default_buttons_data, self::$data['buttons_v3'][ $key ] );
				
				if( ! empty( self::$default_buttons_data_by_type[ $key ] ) )
					self::$data['buttons_v3'][ $key ] = ares_array_merge_recursive_distinct( self::$data['buttons_v3'][ $key ], self::$default_buttons_data_by_type[ $key ] );
				
			}
			
		}
		else{
			
			foreach( self::$default_buttons_data_by_type as $key => $value ){
				
				self::$data['buttons_v3'][ $key ] = ares_array_merge_recursive_distinct( self::$default_buttons_data, $value );
				
			}
			
		}
		
		
	}
	
	public static function init(){
		
		//update_option('areservation_options', array());
		
		self::build_data();
		
		add_action('admin_init', function(){
		    register_setting( 'areservation_options_fields', 'areservation_options', 'areservation_options_validate' );
		});
		
		add_action('admin_menu', function(){
		    add_submenu_page( 'options-general.php', 'aReservation', 'aReservation', 'manage_options', 'indexic-areservation', array( get_called_class(), 'render_options_page' ) );
		});
		
		if( ! empty( self::$data['scripts'] ) ){
			
			add_action('wp_enqueue_scripts', function(){
			
				foreach( self::$data['scripts'] as $script_key => $script_url ){
					
					if( ! empty( $script_url ) )
						wp_enqueue_script( 'ares-script-' . $script_key, esc_url( $script_url ), array( 'jquery' ) );
				
				}
			
			}, 100);
			
		}
		
	}
	
	/*public static function get_button_field_val( $field_name = '', $field_key = '' ){
		
		
		
	}*/
	
	public static function render_options_page(){
		
	    ?>
		<div class="wrap">
			<form method="post" action="options.php">
				<?php settings_fields('areservation_options_fields'); ?>
				<input type="hidden" name="areservation_options[version]" value="1.3">
				<img src="<?= plugins_url( 'assets/logo.png', __FILE__ ); ?>" alt="logo" class="indexic-logo">
				<br>
				<br>
				<h1>Add scripts to all pages</h1>
				
				<div id="ares-scripts-container">
					<?php
						
						if( empty( self::$data['scripts'] ) )
							self::$data['scripts']['default'] = '';
						
						echo '<table class="ares-scripts">';
						
						foreach( self::$data['scripts'] as $script_key => $script_url ){
							
							if( empty( $script_url ) && $script_key != 'default' )
								continue;
							
							?>
							<tr class="ares-script-row">
								<td class="ares-row-id"><?= ( $script_key + 1 ); ?>.</td>
								<td class="ares-row-url"><input type="text" class="form-control" name="areservation_options[scripts][]" placeholder="Enter script URL here" value="<?= esc_url( $script_url ); ?>"></td>
								<td class="ares-row-remove"><a href="#"><span class="dashicons dashicons-no"></span></a></td>
							</tr>
							<?php
						
						}
						
						echo '</table>';
					
					?>
					
				</div>
				<input id="ares-add-new-script" type="button" class="button-secondary" value="<?php _e('Add new script') ?>" />
				
				<br><br>
				
				<h1>Buttons</h1>
				
				<div id="ares-buttons-container">
					
					<div class="ares-fields-group">
						<div class="ares-fields-group-label">Enter your URL Friendly Company Name</div>
						<div class="ares-fields-group-data"><input type="text" class="ares-url-friendly-company-name" name="areservation_options[url_friendly_company_name]" value="<?= self::$data['url_friendly_company_name']; ?>"></div>
					</div>
					
					<?php
						
					$buttons_counter = 1;
					
					foreach( self::$data['buttons_v3'] as $button_key => $button ){
						
						//echo sprintf('<pre style="display:%s">%s</pre>', 'block', print_r( $button, true ) );
						
						echo '<br><h4>'. $button['heading'] .'</h4>';
						
						?>
						<div class="ares-button-container">
							<input type="hidden" class="ares-button-url-format" value="<?= str_replace( '"', '\"', $button['url_format'] ); ?>">
							<?php if( $button_key == 'event' ): ?>
							<div class="ares-fields-group">
								<div class="ares-fields-group-label" style="width: 140px;">Enter your Event Name</div>
								<div class="ares-fields-group-data"><input class="ares-button-event-name" type="text" name="areservation_options[buttons_v3][<?= $button_key; ?>][event_name]" value="<?= $button['event_name']; ?>"></div>
							</div>
							<?php elseif( $button_key == 'group_id' ): ?>
							<div class="ares-fields-group">
								<div class="ares-fields-group-label" style="width: 140px;">Enter your Group ID</div>
								<div class="ares-fields-group-data"><input class="ares-button-group-id" type="text" name="areservation_options[buttons_v3][<?= $button_key; ?>][group_id]" value="<?= $button['group_id']; ?>"></div>
							</div>
							<?php endif; ?>
							<table class="ares-buttons" data-row-key="<?= $button_key; ?>">
								<tr class="ares-button-row">
									<!-- <td class="ares-button-row-id" rowspan="3">$buttons_counter;.</td> -->
									<?php foreach( array('default','hover') as $button_state ): ?>
									<td class="ares-button-data ares-button-data-<?= $button_state; ?>">
										<h4>"<?= ucfirst( $button_state ); ?>" style</h4>
										<?php $input_name = 'areservation_options[buttons_v3][' . $button_key . '][' . $button_state . ']'; ?>
										<table class="ares-buttons-data-table">
											<tr>
												<th class="ares-text-color">Text color</th>
												<th class="ares-text-size">Text size</th>
												<th class="ares-text-weight">Text weight</th>
											</tr>
											<tr>
												<td class="ares-text-color ares-td-color <?= empty( $button[ $button_state ]['text_color'] ) ? '' : 'ares-td-has-color' ?>">
													<div class="button-field-color" data-color="<?= ares_rgba_to_hex( $button[ $button_state ]['text_color'] ); ?>"></div>
													<div class="button-field-color-value"></div>
													<span class="dashicons dashicons-no"></span>
													<input type="hidden" name="<?= $input_name; ?>[text_color]" value="<?= $button[ $button_state ]['text_color']; ?>">
												</td>
												<td class="ares-text-size">
													<div class="button-input-field input-px">
														<input type="text" name="<?= $input_name; ?>[text_size]" value="<?= $button[ $button_state ]['text_size']; ?>">
													</div>
												</td>
												<td class="ares-text-weight">
													<div class="button-field">
														<select name="<?= $input_name; ?>[text_weight]">
															<option value="400"<?= $button[ $button_state ]['text_weight'] == '400' ? ' selected="selected"' : ''; ?>>Normal</option>
															<option value="700"<?= $button[ $button_state ]['text_weight'] == '700' ? ' selected="selected"' : ''; ?>>Bold</option>
														</select>
													</div>
												</td>
											</tr>
											<tr>
												<th class="ares-bg-color">Background color</th>
												<th class="ares-horizontal-padding">Horizontal<br>Padding</th>
												<th class="ares-vertical-padding">Vertical<br>Padding</th>
											</tr>
											<tr>
												<td class="ares-bg-color ares-td-color <?= empty( $button[ $button_state ]['bg_color'] ) ? '' : 'ares-td-has-color' ?>">
													<div class="button-field-color" data-color="<?= ares_rgba_to_hex( $button[ $button_state ]['bg_color'] ); ?>"></div>
													<div class="button-field-color-value"></div>
													<span class="dashicons dashicons-no"></span>
													<input type="hidden" name="<?= $input_name; ?>[bg_color]" value="<?= $button[ $button_state ]['bg_color']; ?>">
												</td>
												<td class="ares-horizontal-padding">
													<div class="button-input-field input-px">
														<input type="text" name="<?= $input_name; ?>[horizontal_padding]" value="<?= $button[ $button_state ]['horizontal_padding']; ?>">
													</div>
												</td>
												<td class="ares-vertical-padding">
													<div class="button-input-field input-px">
														<input type="text" name="<?= $input_name; ?>[vertical_padding]" value="<?= $button[ $button_state ]['vertical_padding']; ?>">
													</div>
												</td>
											</tr>
											<tr>
												<th class="ares-border-color">Border color</th>
												<th class="ares-border-size">Border size</th>
												<th class="ares-border-radius">Border radius</th>
											</tr>
											<tr>
												<td class="ares-border-color ares-td-color <?= empty( $button[ $button_state ]['border_color'] ) ? '' : 'ares-td-has-color' ?>">
													<div class="button-field-color" data-color="<?= ares_rgba_to_hex( $button[ $button_state ]['border_color'] ); ?>"></div>
													<div class="button-field-color-value"></div>
													<span class="dashicons dashicons-no"></span>
													<input type="hidden" name="<?= $input_name; ?>[border_color]" value="<?= $button[ $button_state ]['border_color']; ?>">
												</td>
												<td class="ares-border-size">
													<div class="button-input-field input-px">
														<input type="text" name="<?= $input_name; ?>[border_size]" value="<?= $button[ $button_state ]['border_size']; ?>">
													</div>
												</td>
												<td class="ares-border-radius">
													<div class="button-input-field input-px">
														<input type="text" name="<?= $input_name; ?>[border_radius]" value="<?= $button[ $button_state ]['border_radius']; ?>">
													</div>
												</td>
											</tr>
										</table>
									</td>
									<?php endforeach; ?>
									<td class="ares-row-remove" rowspan="3"><a href="#"><span class="dashicons dashicons-no"></span></a></td>
								</tr>
								<tr class="ares-button-row">
									<td class="ares-td-title">
										<input type="text" class="ares-button-title" name="areservation_options[buttons_v3][<?= $button_key; ?>][title]" value="<?= empty( $button['title'] ) ? $button['default_title'] : $button['title']; ?>" placeholder="Button title">
									</td>
									<td class="ares-td-url">
										<input type="text" class="ares-button-url" name="areservation_options[buttons_v3][<?= $button_key; ?>][url]" value="<?= $button['url']; ?>" placeholder="URL">
										<!-- <br> --><label style="display:none;"><input type="checkbox" name="areservation_options[buttons_v3][<?= $button_key; ?>][new_tab]" value="1"<?= empty( $button['new_tab'] ) ? '' : ' checked="checked"'; ?>> Open link in a new tab</label>
									</td>
								</tr>
								<tr class="ares-button-row">
									<td class="ares-td-preview">
										Preview: <span class="ares-button-preview"><a href="#" class="ares-button"></a></span><input type="hidden" class="button-html-output" name="areservation_options[buttons_v3][<?= $button_key; ?>][html_output]" value="<?= esc_attr( $button['html_output'] ); ?>">
									</td>
									<td class="ares-td-usage">
										Usage: <code data-additional_attrs="<?= esc_attr( $button['shortcode_additional_atts'] ); ?>">[ares_button<?= ( /*$button_key == 'standard' ? '' :*/ ' id="'. $button_key .'"' ); ?><?= $button['shortcode_additional_atts']; ?>]</code><br><br>You can customize the button's URL by adding <b>url="YOUR_CUSTOM_URL"</b> to the shortcode.
									</td>
								</tr>
							</table>
						</div>
						<?php
						
						$buttons_counter++;
					
					}
					
					?>
				</div>
				
				<input id="ares-add-new-button" type="button" class="button-secondary" value="<?php _e('Add new button') ?>" />
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
				</p>
			</form>
		</div>
		
		<?php
		
		//echo sprintf('<pre style="display:%s">%s</pre>', 'block', print_r( self::$data, true ) );
		
	}
	
}

areservation_Options::init();