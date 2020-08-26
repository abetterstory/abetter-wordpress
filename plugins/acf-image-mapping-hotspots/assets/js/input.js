(function( $ ) {

	// make a "class" so we can have multiple of these fields in one form
	var image_mapping = (function( $field ) {

		var

		x,
		y,
		tempX,
		tempY,
		imgWidth,
		imgHeight,
		coordinates,


		// this field's `img`
		$fieldImg = $field.find('.image_mapping-image img'),

		// this field's marker
		$fieldMarker = $field.find('.image_mapping-image span'),

		// this field's `input`
		$fieldInput = $field.find('input.image_mapping-input'),

		// the label for the img to link to
		imgSelector = '[data-name="' + $fieldImg.attr('data-label') + '"]',

		// option for percent based coordinates
		percentageBased = parseInt( $fieldImg.attr('data-percent-based') ),


		// the image from which to catch the coordinates
		$img,

		// the source of the linked image
		imgSrc,

		// the ACF container of the linked image
		$imgCon,

		// repeater parent
		$repeaterParent = $field.parents('.acf-field-repeater'),


		// set up the `image_mapping` object
		init = function() {

			// set the linked image, and kill the function if we couldn't find it
			if ( ! setLinkedImg() ) return;

			// set up the field with for the current image
			loadImage();

			// set up a listener for when the user changes the image
			$img.on( 'load', loadImage );

			// set up the click handler to catch the coordinates
			$fieldImg.on( 'click', handleClick );

			$fieldInput.on( 'change input', handleInputChange );

		},

		setImgDimensions = function() {

			// set the image width & height
			imgWidth  = $fieldImg.width();
			imgHeight = $fieldImg.height();

		},

		handleInputChange = function() {

			// get the coordinates
			coordinates = $fieldInput.val().split(',');

			// we need 2 coordinates to work with
			if ( 2 !== coordinates.length ) {
				return;
			}

			tempX = coordinates[0];
			tempY = coordinates[1];

			// make sure we have numbers & units
			if (
				NaN === parseInt( tempX )
				|| ( -1 === tempX.indexOf('%') && -1 === tempX.indexOf('px') )
				|| NaN === parseInt( tempY )
				|| ( -1 === tempY.indexOf('%') && -1 === tempY.indexOf('px') )
			) {
				return;
			}

			x = tempX;
			y = tempY;

			// handle the change
			moveMarker();

		},

		moveMarker = function() {

			// move the marker
			$fieldMarker.css( 'left', x ).css( 'top', y );

		},

		handleClick = function( e ) {

			// make sure we have the right dimensions, having it on a 'load' listener doesn't work for page refreshes
			setImgDimensions();

			// grab the coordinates
			x = e.offsetX + 'px';
			y = e.offsetY + 'px';

			// transform to percentage base if specified, 4 decimal precision
			if ( percentageBased ) {
				x = ( parseInt( x ) / imgWidth * 100 ).toFixed( 4 ) + '%';
				y = ( parseInt( y ) / imgHeight * 100 ).toFixed( 4 ) + '%';
			}

			// move the marker
			moveMarker( x, y );

			// update the value
			$fieldInput.val( x + ',' + y );

		},

		setLinkedImg = function() {

			$imgCon = $field.siblings( imgSelector );

			// check all the repeater parents
			while ( ! $imgCon.length ) {

				// if there are no more, give up the search
				if ( ! $repeaterParent.length ) {
					console.log('Couldn\'t find a match for the linked image');
					return false;
				}

				// search for the img container
				$imgCon = $repeaterParent.siblings( imgSelector );

				// get the next repeater parent
				$repeaterParent = $repeaterParent.parents('.acf-field-repeater');

			}

			$img = $imgCon.find('img[data-name="image"]');

			return true;

		},

		loadImage = function() {

			// get the img src
			imgSrc = $img.attr('src');

			// we don't want to work with thumbnails so lets get the full size image
			var regex  = /([\s\S]*)-[0-9]+x[0-9]+(.[a-zA-Z]+)$/.exec( imgSrc );
			if ( regex ) {
				imgSrc = regex[1] + regex[2];
			}

			// add the source to the designated `img` tag
			$fieldImg.attr( 'src', imgSrc );

		};

		return init;

	});

	if( typeof acf.add_action !== 'undefined' ) {
	
		/*
		*  ready append (ACF5)
		*
		*  These are 2 events which are fired during the page load
		*  ready = on page load similar to $(document).ready()
		*  append = on new DOM elements appended via repeater field
		*
		*  @type	event
		*  @date	20/07/13
		*
		*  @param	$el (jQuery selection) the jQuery element which contains the ACF fields
		*  @return	n/a
		*/
		
		acf.add_action('ready append', function( $el ){
			
			// search $el for fields of type 'image_mapping'
			acf.get_fields({ type : 'image_mapping'}, $el).each(function(){

				image_mapping( $(this) )();
				
			});
			
		});
		
		
	} else {
		
		
		/*
		*  acf/setup_fields (ACF4)
		*
		*  This event is triggered when ACF adds any new elements to the DOM. 
		*
		*  @type	function
		*  @since	1.0.0
		*  @date	01/01/12
		*
		*  @param	event		e: an event object. This can be ignored
		*  @param	Element		postbox: An element which contains the new HTML
		*
		*  @return	n/a
		*/
		
		// $(document).on('acf/setup_fields', function(e, postbox){
			
		// 	$(postbox).find('.field[data-field_type="image_mapping"]').each(function(){
				
		// 		initialize_field( $(this) );
				
		// 	});
		
		// });
	
	
	}


})(jQuery);
