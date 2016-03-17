/* global tinyMCE, vpEditorView */
(function( $, wp, vpEditorView ){
	wp.mce = wp.mce || {};
	wp.mce.videopress_wp_view_renderer = {
		shortcode_string : 'videopress',
		shortcode_data : {},
		defaults       : {
			w         : '',
			at        : 0,
			permalink : true,
			hd        : false,
			loop      : false,
			freedom   : false,
			autoplay  : false,
			flashonly : false
		},
		coerce         : wp.media.coerce,
		template       : wp.template( 'videopress_iframe_vnext' ),
		getContent     : function() {
			var urlargs = 'for=' + encodeURIComponent( vpEditorView.home_url_host ),
				named = this.shortcode.attrs.named,
				options, key, width;

			for ( key in named ) {
				switch ( key ) {
					case 'at' :
						if ( parseInt( named[ key ], 10 ) ) {
							urlargs += '&' + key + '=' + parseInt( named[ key ], 10 );
						} // Else omit, as it's the default.
						break;
					case 'permalink' :
						if ( 'false' === named[ key ] ) {
							urlargs += '&' + key + '=0';
						} // Else omit, as it's the default.
						break;
					case 'hd' :
					case 'loop' :
					case 'autoplay' :
						if ( 'true' === named[ key ] ) {
							urlargs += '&' + key + '=1';
						} // Else omit, as it's the default.
						break;
					default:
						// Unknown parameters?  Ditch it!
						break;
				}
			}

			options = {
				width   : vpEditorView.content_width,
				height  : ( vpEditorView.content_width * 0.5625 ),
				guid    : this.shortcode.attrs.numeric[0],
				urlargs : urlargs
			};

			if ( typeof named.w !== 'undefined' ) {
				width = parseInt( named.w, 10 );
				if ( width > vpEditorView.min_content_width && width < vpEditorView.content_width ) {
					options.width  = width;
					options.height = parseInt( width * 0.5625, 10 );
				}
			}

			return this.template( options );
		},
		edit: function( data ) {
			var shortcode_data = wp.shortcode.next( this.shortcode_string, data ),
				values         = shortcode_data.shortcode.attrs.named;

			values.guid = shortcode_data.shortcode.attrs.numeric[0];

			wp.mce.videopress_wp_view_renderer.popupwindow( tinyMCE.activeEditor, values );
		},
		popupwindow: function( editor, values, onsubmit_callback ){
			var renderer = this,
				key;

			/**
			 * Set up a fallback onsubmit callback handler.
			 *
			 * A custom one can be provided as the third argument if desired.
			 */
			if ( typeof onsubmit_callback !== 'function' ) {
				onsubmit_callback = function( e ) {
					var s = '[' + renderer.shortcode_string,
						i;
					for ( i in e.data ) {
						switch( i ) {
							case 'guid' :
								s += ' ' + e.data.guid;
								break;
							case 'w' :
							case 'at' :
								if ( parseInt( e.data[ i ], 10 ) ) {
									s += ' ' + i + '="' + parseInt( e.data[ i ], 10 ) + '"';
								} // Else omit, as it's the default.
								break;
							case 'permalink' :
								if ( ! e.data[ i ] ) {
									s += ' ' + i + '="false"';
								} // Else omit, as it's the default.
								break;
							case 'hd' :
							case 'loop' :
							case 'freedom' :
							case 'autoplay' :
							case 'flashonly' :
								if ( e.data[ i ] ) {
									s += ' ' + i + '="true"';
								} // Else omit, as it's the default.
								break;
							default:
								// Unknown parameters?  Ditch it!
								break;
						}
					}
					s += ']';
					editor.insertContent( s );
				};
			}

			/**
			 * Populate the defaults.
			 */
			_.each( this.defaults, function( value, key ) {
				values[ key ] = this.coerce( values, key);
			}, this );
			console.log( JSON.stringify( values ) );

			/**
			 * Declare the fields that will show in the popup when editing the shortcode.
			 */
			editor.windowManager.open( {
				title : vpEditorView.modal_labels.title,
				body  : [
					{
						type  : 'textbox',
						name  : 'guid',
						label : vpEditorView.modal_labels.guid,
						value : values.guid
					}, {
						type  : 'textbox',
						name  : 'w',
						label : vpEditorView.modal_labels.w,
						value : values.w
					}, {
						type  : 'textbox',
						name  : 'at',
						label : vpEditorView.modal_labels.at,
						value : values.at
					}, {
						type    : 'checkbox',
						name    : 'hd',
						label   : vpEditorView.modal_labels.hd,
						checked : values.hd
					}, {
						type    : 'checkbox',
						name    : 'permalink',
						label   : vpEditorView.modal_labels.permalink,
						checked : values.permalink
					}, {
						type    : 'checkbox',
						name    : 'autoplay',
						label   : vpEditorView.modal_labels.autoplay,
						checked : values.autoplay
					}, {
						type    : 'checkbox',
						name    : 'loop',
						label   : vpEditorView.modal_labels.loop,
						checked : values.loop
					}, {
						type    : 'checkbox',
						name    : 'freedom',
						label   : vpEditorView.modal_labels.freedom,
						checked : values.freedom
					}, {
						type    : 'checkbox',
						name    : 'flashonly',
						label   : vpEditorView.modal_labels.flashonly,
						checked : values.flashonly
					}
				],
				onsubmit : onsubmit_callback
			} );
		}
	};
	wp.mce.views.register( 'videopress', wp.mce.videopress_wp_view_renderer );

	// Extend the videopress one to also handle `wpvideo` instances.
	wp.mce.wpvideo_wp_view_renderer = _.extend( {}, wp.mce.videopress_wp_view_renderer, {
		shortcode_string : 'wpvideo'
	});
	wp.mce.views.register( 'wpvideo', wp.mce.wpvideo_wp_view_renderer );
}( jQuery, wp, vpEditorView ));