jQuery(document).ready(function($){
	
	function ares_generate_button_styles( $button_container ){
		
		var common_styles = 'display: inline-block; text-decoration: none; outline: none;';
		
		var styles = { 
			'default' : [ common_styles ], 
			'hover' : [ common_styles ] 
		};
		
		var $button = $button_container.find('a.ares-button');
		
		var title = $button_container.find('input.ares-button-title' ).val();
		var url = $button_container.find('input.ares-button-url' ).val() || '';
		var url_target = $button_container.find('.ares-td-url input[type="checkbox"]' ).is(':checked') ? '_blank' : '_self';
		
		var url_format = $button_container.find('input.ares-button-url-format' ).val() || '';
		var url_friendly_company_name = $('input.ares-url-friendly-company-name').val() || '';
		var event_name = $button_container.find('input.ares-button-event-name' ).val() || '';
		var group_id = $button_container.find('input.ares-button-group-id' ).val() || '';
		
		if( url_format )
			url = url_format;
		
		url = url.replace( '{UrlFriendlyCompanyName}', url_friendly_company_name );
		url = url.replace( '{EventName}', event_name );
		url = url.replace( '{GroupID}', group_id );
		url = url.replace( /\/\//g, '/' ).replace( ':/', '://' );
		
		//console.log( 'url: ' + url );
		
		$button.attr( 'href', ( url || 'javascript:void(0)' ) ).attr( 'target', url_target ).text( title );
		
		$.each( [ 'default', 'hover' ], function(index, val){
			
			//console.log( $button_container.find('.ares-button-data-' + val + ' .ares-text-color input' ) );
			
			var text_color = $button_container.find('.ares-button-data-' + val + ' .ares-text-color input' ).val();
			var text_size = $button_container.find('.ares-button-data-' + val + ' .ares-text-size input' ).val() + 'px';
			var text_weight = $button_container.find('.ares-button-data-' + val + ' .ares-text-weight select' ).val();
			
			var bg_color = $button_container.find('.ares-button-data-' + val + ' .ares-bg-color input' ).val();
			var hor_padding = $button_container.find('.ares-button-data-' + val + ' .ares-horizontal-padding input' ).val() + 'px';
			var ver_padding = $button_container.find('.ares-button-data-' + val + ' .ares-vertical-padding input' ).val() + 'px';
			
			var border_color = $button_container.find('.ares-button-data-' + val + ' .ares-border-color input' ).val();
			var border_size = $button_container.find('.ares-button-data-' + val + ' .ares-border-size input' ).val() + 'px';
			var border_radius = $button_container.find('.ares-button-data-' + val + ' .ares-border-radius input' ).val() + 'px';
			
			styles[val].push( 'color:' + ( text_color == '' ? 'transparent' : text_color ) + ';' );
			styles[val].push( 'font-size:' + ( text_size == 'px' ? '0' : text_size ) + ';' );
			styles[val].push( 'font-weight:' + ( text_weight == '' ? '400' : text_weight ) + ';' );
			
			styles[val].push( 'background:' + ( bg_color == '' ? 'none' : bg_color ) + ';' );
			styles[val].push( 'padding:' + ( ver_padding == 'px' ? '0' : ver_padding ) + ' ' + ( hor_padding == 'px' ? '0' : hor_padding ) + ';' );
			
			styles[val].push( 'border:' + ( border_size == '' ? '0px' : border_size ) + ' solid ' + ( border_color == '' ? 'transparent' : border_color ) + ';' );
			styles[val].push( 'border-radius:' + ( border_radius == 'px' ? '0' : border_radius ) + ';' );
			
		});
		
		//console.log( styles );
		
		$button.attr('data-default-style', styles['default'].join('') );
		$button.attr('style', styles['default'].join('') );
		$button.attr('data-hover-style', styles['hover'].join('') );
		
		$button_container.find('input.button-html-output').val( $button_container.find('.ares-button-preview').html() );
		
	}
	
	function ares_update_buttons_ids(){
		
		$('#ares-buttons-container table.ares-buttons').each(function( button_row_index, button_row_el){
			
			var row_key = $(button_row_el).data('row-key');
			
			var $code = $(button_row_el).find('.ares-td-usage code');
			
			/*if( row_key == 'standard' )
				var id_attr = '';
			else*/
				var id_attr = ' id="' + row_key + '"';
			
			//$(button_row_el).find('.ares-button-row-id').html( ( button_row_index + 1 ) + '.' );
			//$code.html( '[ares_button id="' + ( button_row_index + 1 ) + '"' + ( $code.data('additional_attrs') || '' ) +']' );
			$code.html( '[ares_button' + id_attr + ( $code.data('additional_attrs') || '' ) +']' );
			//$(button_row_el).data('row-key', button_row_index);
			
			/*$(button_row_el).find('[name]').each(function(name_field_index, name_field_el){
				
				$(name_field_el).attr( 'name', $(name_field_el).attr('name').replace( '['+row_key+']', '['+button_row_index+']' ) );
				
			});*/
				
		});
		
	}
	
	function init_button_row( $button_row ){
		
		$button_row.closest('.ares-button-container').find('input,select').add('input.ares-url-friendly-company-name').on('change input keyup paste', function() {
			
			ares_generate_button_styles( $button_row.closest('.ares-button-container') );
			
		});
	
		$button_row.find('a.ares-button').hover(function() {
			
			$(this).attr('style', $(this).attr('data-hover-style') );
			
		}, function() {
			
			$(this).attr('style', $(this).attr('data-default-style') );
			
		});
		
		$button_row.find('td.ares-text-color, td.ares-bg-color, td.ares-border-color').each(function(index, color_td_el) {
			
			//console.log( 'xx: ', ++xx );
			
			var $picker_el = $(color_td_el).children('.button-field-color');
			var $picker_value_el = $(color_td_el).children('.button-field-color-value');
			var $input = $(color_td_el).children('input:hidden');
			
			if( $input.val() == 'transparent' )
				$input.val('');
			
			$picker_value_el.text( $input.val() );
			
			if( $input.val() != '' )
				$picker_el.css('background', $input.val() );
			
			var picker = new CP( $picker_el[0] );
			
			picker.on('change', function(r, g, b, a) {
				
				var new_color = 'rgba(' + r + ', ' + g + ', ' + b + ', ' + a + ')';
				
				$picker_value_el.html( new_color );
		    	$input.val( new_color );
		    	$picker_el.css('background', new_color ).data('color', this.color(r, g, b, a) ).attr('data-color', this.color(r, g, b, a) );
		    	
		    	$(color_td_el).addClass('ares-td-has-color');
		        
		        ares_generate_button_styles( $button_row.closest('.ares-button-container') );
		        
		    });
			
			$(color_td_el).find('.dashicons').on('click', function(event) {
				
				event.preventDefault();
				
				$input.val('');
				$picker_el.css('background', 'transparent' );
				$(color_td_el).removeClass('ares-td-has-color');
				$picker_value_el.html('');
				
				ares_generate_button_styles( $button_row.closest('.ares-button-container') );
				
			});
			
			$(color_td_el).find('.button-field-color-value').on('click', function(event) {
				
				event.preventDefault();
				
				picker.enter();
				
			});
			
		});
		
		$button_row.find('td.ares-row-remove a').on('click', function(event) {
			
			event.preventDefault();
			
			//console.log( '$button_row: ', $button_row );
			
			if( $button_row.siblings('table').length > 0 ){
				
				$button_row.remove();
				
				ares_update_buttons_ids();
				
			}
			
		});
		
		ares_generate_button_styles( $button_row.closest('.ares-button-container') );
		
		ares_update_buttons_ids();
		
	}
	
	var $scripts_table = $('table.ares-scripts');

	$('#ares-add-new-script').on('click', function(event) {
		
		event.preventDefault();
		
		var $trs = $scripts_table.find('tr');
		
		var $new_row = $trs.last().clone();
		
		$new_row.find('td.ares-row-id').text( ( $trs.length + 1 ) + '.' );
		$new_row.find('td.ares-row-url input').val('');
		
		$new_row.appendTo( $scripts_table );
		
	});
	
	$scripts_table.on('click', 'td.ares-row-remove a', function(event) {
		
		event.preventDefault();
		
		if( $scripts_table.find('tr').length > 1 ){
			
			$(this).closest('tr').remove();
			
			$scripts_table.find('tr').each(function(index, el) {
				
				$(el).find('td.ares-row-id').text( ( index + 1 ) + '.' );
				
			});
			
		}
		else{
			
			$(this).closest('tr').find('td.ares-row-url input').val('');
			
		}
		
	});
	
	var xx = 0;
	
	$('#ares-buttons-container table.ares-buttons').each(function( button_row_index, button_row_el){
		
		init_button_row( $(button_row_el) );
			
	});	

	$('#ares-add-new-button').on('click', function(event) {
		
		event.preventDefault();
		
		var $buttons_rows = $('#ares-buttons-container table.ares-buttons');
		
		//console.log( '$buttons_rows: ', $buttons_rows );
		
		var $new_row = $buttons_rows.last().clone();
		
		$new_row.insertAfter( $buttons_rows.last() );
		
		init_button_row( $new_row );
		
	});
	
});