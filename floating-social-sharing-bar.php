<?php
/*
Plugin Name: Floating Social Sharing Bar
Plugin URI: http://www.way2blogging.org/
Description: Shows a horizontal sharing bar with selected social counter. It is fixed upon page scroll through out the Post.
Version: 1.1
Author: Harish Dasari
Author URI: http://www.way2blogging.org/
License: GPL2
*/

/*  
	Copyright 2012  Harish Dasari  (email : way2blogging@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Exit on direct load 
if ( ! defined( 'ABSPATH' ) ) exit();

/**
 * Main Class
 * 
 * @since 1.0
 */
class W2B_Floating_Social_Sharing_Bar{

	/**
	 * Title of the Admin Page
	 * 
	 * @var string
	 */
	var $title;

	/**
	 * Slug of the Admin Page
	 * 
	 * @var string
	 */
	var $slug;

	/**
	 * Setting name of the Plugin
	 * 
	 * @var string
	 */
	var $setting;

	/**
	 * Social service buttons
	 * 
	 * @var array
	 */
	var $social_services = array();

	/**
	 * Constructor
	 */
	function __construct(){

		$this->title           = __( 'Floating Social Sharing Bar', 'w2b' );
		$this->slug            = 'w2b_fssb_settings';
		$this->setting         = 'w2b_floating_social_sharing_bar';
		$this->social_services = array( 
			'Twitter',
			'Facebook',
			'Pinterest',
			'GooglePlus',
			'Digg',
			'LinkedIn',
			'StumbleUpon',
			'Buffer',
		);

		/* Actions */
		add_action( 'admin_menu', array( $this, 'w2b_floating_menu' ) );
		add_action( 'admin_init', array( $this, 'w2b_register_settings' ) );
		add_action( 'template_redirect', array( $this, 'w2b_add_social_sharing_buttons' ) );

		/* Actication Hooks */
		register_activation_hook( __FILE__, array( $this, 'w2b_fssb_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'w2b_fssb_uninstall' ) );

	}

	/**
	 * Register Setting Page
	 * 
	 * @since 1.0
	 * 
	 * @return null
	 */
	function w2b_floating_menu(){

		$hook = add_options_page(
			$this->title,
			__( 'Floating Social Bar', 'w2b' ),
			'activate_plugins',
			$this->slug,
			array( $this, 'w2b_fshb_options_page' )
		);

	}

	/**
	 * Load default Options upon activation.
	 * 
	 * @since 1.0
	 * 
	 * @return null
	 */
	function w2b_fssb_install(){

		$w2b_options['w2b_enable_floating_social'] = 0; 
		$w2b_options['w2b_social_services']        = array_map( 'strtolower', $this->social_services );
		$w2b_options['w2b_topoffset']              = '0';
		$w2b_options['w2b_post_classname']         = 'hentry';
		$w2b_options['w2bBgColor']                 = 'FFFFFF';

		update_option( $this->setting, $w2b_options );

	}

	/**
	 * Remove options upon deavtivation.
	 * 
	 * @since 1.0
	 * 
	 * @return null
	 */
	function w2b_fssb_uninstall(){

		delete_option( $this->setting );

	}

	/**
	 * Registe the Setting
	 * 
	 * @since 1.1
	 * 
	 * @return null
	 */
	function w2b_register_settings(){

		register_setting( $this->setting, $this->setting, array( $this, 'w2b_sanitize_options' ) );

	}

	/**
	 * Sanitize Options upon save.
	 * 
	 * @since 1.1
	 * 
	 * @param  mixed $options submitted value
	 * @return mixed          sanitized value
	 */
	function w2b_sanitize_options( $options ){

		$new_options                               = array();
		$new_options['w2b_enable_floating_social'] = (int) (bool) $options['w2b_enable_floating_social'];
		$new_options['w2b_social_services']        = array();
		if ( ! empty( $options['w2b_social_services'] ) && is_array( $options['w2b_social_services'] ) ) {
			$all_socials = array_map( 'strtolower', $this->social_services );
			foreach ( $options['w2b_social_services'] as $service ) {
				if ( in_array( $service, $all_socials ) )
					$new_options['w2b_social_services'][] = $service;
			}
		}
		$new_options['w2b_topoffset']      = intval( $options['w2b_topoffset'] );
		$new_options['w2b_post_classname'] = sanitize_html_class( $options['w2b_post_classname'] );
		$new_options['w2bBgColor']         = preg_match( '/^[a-f0-9]{3,6}$/i', $options['w2bBgColor'] ) ? esc_attr( $options['w2bBgColor'] ) : 'FFFFFF';

		return $new_options;

	}

	/**
	 * Print the Setting Page.
	 * 
	 * @since 1.0
	 * 
	 * @return null
	 */
	function w2b_fshb_options_page(){

		$default = array(
			'w2b_enable_floating_social' => '',
			'w2b_social_services'        => array(),
			'w2b_topoffset'              => '',
			'w2b_post_classname'         => '',
			'w2bBgColor'                 => '',
		);

		extract( wp_parse_args( get_option( $this->setting, array() ), $default ) );

		?>
		<div class="wrap">
			<?php screen_icon( 'plugins' ); ?>
			<h2><?php echo esc_html( $this->title ); ?></h2>
			<form action="options.php" method="POST" id="w2bForm">
				<h3><?php _e( 'Settings', 'w2b' );?></h3>
				<table class="form-table">
					<tr valing="top">
						<th scope="row"><label for="w2b_enable_floating_social"><?php _e( 'Enable the Plugin?', 'w2b' );?></label></th>
						<td>
							<select name="<?php echo esc_attr( $this->setting ); ?>[w2b_enable_floating_social]" id="w2b_enable_floating_social">
								<option value="1" <?php selected( $w2b_enable_floating_social, '1' );?>><?php _e( 'Yes', 'w2b' );?></option>
								<option value="0" <?php selected( $w2b_enable_floating_social, '0' );?>><?php _e( 'No', 'w2b' );?></option>
							</select>
							<p class="description"><?php _e( 'Enable or Disable the Plugin', 'w2b' ) ?></p>
						</td>
					</tr>
					<tr valing="top">
						<th scope="row"><label for=""><?php _e( 'Choose Social Buttons', 'w2b' );?></label></th>
						<td>
							<?php 
								foreach ( (array) $this->social_services as $social ) {
									$social_l = strtolower( $social );
									$id = 'w2b-service-'. esc_attr( $social_l );
									echo '<p>' . 
										 	'<input type="checkbox" name="' . esc_attr( $this->setting ) . '[w2b_social_services][]" value="' . esc_attr( $social_l ) . '" id="' . $id . '" ' . checked( in_array( $social_l, (array) $w2b_social_services ), true, false ) . '/>' .
										 	'<label for="'. $id .'"> '. $social .'</label>' .
										 '</p>';
								}
							?>
							<p class="description"><?php _e( 'Select the Social sharing buttons you want to show on the Horizontal bar.', 'w2b' ) ?></p>
						</td>
					</tr>
					<tr valing="top">
						<th scope="row"><label for="w2b_topoffset"><?php _e( 'Top Offset', 'w2b' );?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $this->setting ); ?>[w2b_topoffset]" id="w2b_topoffset" size="4" value="<?php echo esc_attr( $w2b_topoffset ) ?>"/> <em>px</em>
							<p class="description"><?php _e( 'Top offset when Fixed position. normatlly it is <code>0</code> <br/> while scrolling the window, you may have your own fixed floating navigation menus. To avoid the conflicts, this option is helps you to set the top offset.', 'w2b' ) ?></p>
						</td>
					</tr>
					<tr valing="top">
						<th scope="row"><label for="w2b_post_classname"><?php _e( 'Post Class Name (Optional)' ); ?></label></th>
						<td>
							<input type="text" name="<?php echo esc_attr( $this->setting ); ?>[w2b_post_classname]" id="w2b_post_classname" size="10" value="<?php echo esc_attr( $w2b_post_classname ); ?>"/>
							<p class="description"><?php _e( 'Common Class name of your Post. This is <code>hentry</code> in most cases. However, if you have different class name, add the class name here without <code>.</code> in front of class name.', 'w2b' ); ?></p>
						</td>
					</tr>
					<tr valing="top">
						<th scope="row"><label for="w2bBgColor"><?php _e( 'Background Color' ); ?></label></th>
						<td>
							# <input type="text" name="<?php echo esc_attr( $this->setting ); ?>[w2bBgColor]" id="w2bBgColor" size="10" value="<?php echo esc_attr( $w2bBgColor ); ?>"/>
							<p class="description"><?php _e( 'Background color of the Horizontal bar.', 'w2b' ); ?></p>
						</td>
					</tr>
					<tr valing="top">
						<td colspan="2">
							<?php settings_fields( $this->setting ); ?>
							<?php submit_button(); ?>
						</td>
					</tr>
				</table>
			</form>
			<p>Coded by <a href="http://www.way2blogging.org/" target="_blank">Harish Dasari</a>. Please share this Plugin.</p>
		</div>
		<?php
	}

	/**
	 * Add Scripts, CSS and HTML to Single Post pages
	 * 
	 * @since 1.0
	 * 
	 * @return null if not single post
	 */
	function w2b_add_social_sharing_buttons(){

		if ( ! is_singular( 'post' ) )
			return;

		wp_enqueue_script( 'jquery' );

		add_action( 'wp_head', array( $this, 'w2b_insert_socialcss' ) );
		add_action( 'wp_footer', array( $this, 'w2b_insert_socialscripts' ) );

		add_filter( 'the_content', array( $this,'w2b_insert_socialhtml' ) );

	}

	/**
	 * Add Social Sharing HTML to Top of Content.
	 * 
	 * @since 1.0
	 * 
	 * @param  string $content Post Content.
	 * @return string          Social sharing html + Post Content.
	 */
	function w2b_insert_socialhtml( $content ){

		$social_html = $this->w2b_get_socialsharinghtml();
		return $social_html . $content;

	}

	/**
	 * Print Social Scripts on Footer
	 * 
	 * @since 1.0
	 * 
	 * @return null 
	 */
	function w2b_insert_socialscripts(){

		$w2b_option      = get_option( $this->setting );
		$w2b_post_classs = empty( $w2b_option['w2b_post_classname'] ) ? 'hentry' : stripslashes( $w2b_option['w2b_post_classname'] );
		$w2b_offset      = empty( $w2b_option['w2b_topoffset'] ) ? 0 : intval( $w2b_option['w2b_topoffset'] );

		if ( count( $w2b_option['w2b_social_services'] ) != 0 && $w2b_option['w2b_enable_floating_social'] ) {

			$scripts     = '';
			$social_scripts = array(
				'twitter'     => '(function(a,b,c){var d=a.getElementsByTagName(b)[0];if(!a.getElementById(c)){a=a.createElement(b);a.id=c;a.src="//platform.twitter.com/widgets.js";d.parentNode.insertBefore(a,d)}})(document,"script","twitter-wjs");',
				'googleplus'  => '(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src="https://apis.google.com/js/plusone.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
				'stumbleupon' => '(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src="https://platform.stumbleupon.com/1/widgets.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
				'digg'        => '(function(){var a=document.createElement("script"),b=document.getElementsByTagName("script")[0];a.type="text/javascript";a.async=true;a.src="http://widgets.digg.com/buttons.js";b.parentNode.insertBefore(a,b)})();',
				'pinterest'   => '(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src="//assets.pinterest.com/js/pinit.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
				'linkedin'    => '(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src="//platform.linkedin.com/in.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
				'buffer'      => '(function(){var a=document.createElement("script");a.type="text/javascript";a.async=true;a.src="http://static.bufferapp.com/js/button.js";var b=document.getElementsByTagName("script")[0];b.parentNode.insertBefore(a,b)})();',
			);

			foreach ( (array) $w2b_option['w2b_social_services'] as $service ) {
				if ( array_key_exists( strtolower( $service ), $social_scripts ) )
					$scripts .= $social_scripts[ strtolower($service) ] . "\n";
			}

			echo <<<W2BSCRIPTS
<script type='text/javascript'>
/*<![CDATA[*/
{$scripts}/*]]>*/
</script>
<script type='text/javascript'>
/*<![CDATA[*/
var w2b_offset = {$w2b_offset};
jQuery(document).ready(function (b) {
	var a = b("#w2bSocialFloat");
	a.wrap('<div id="w2bSocialPlaceholder"></div>').closest("#w2bSocialPlaceholder").height(a.outerHeight());
	a.width(a.outerWidth());
	e = a.offset().top - w2b_offset;
	b(window).scroll(function() {
		d = b(this).scrollTop();
		d >= e ? a.addClass("w2bFloatSocial") : a.removeClass("w2bFloatSocial");
		f = b(".{$w2b_post_classs}");
		if(f.length != 0) {
			c = f.outerHeight() + f.offset().top;
			d >= c ? a.stop().animate({
				top: "-150px"
			}) : a.stop().animate({
				top: w2b_offset + "px"
			});
		} else d >= e ? a.css("top", w2b_offset + "px") : a.css("top", "0");
	});
});
/*]]>*/
</script>
W2BSCRIPTS;

		}
	}

	/**
	 * Build Social Sharing HTML and returns it.
	 * 
	 * @since 1.0
	 * 
	 * @return string Social Sharing HTML.
	 */
	function w2b_get_socialsharinghtml(){

		$w2b_option = get_option( $this->setting );

		if ( count( $w2b_option['w2b_social_services'] ) != 0 && $w2b_option['w2b_enable_floating_social'] ) {

			global $post;
			$post_title    = $post->post_title;
			$post_url      = get_permalink( $post->ID );
			$post_image    = $this->get_post_image();
			
			$social_html = array(
				'facebook'    => sprintf( '<td><iframe src="//www.facebook.com/plugins/like.php?href=%s&send=false&layout=button_count&width=80&show_faces=false&action=like&colorscheme=light&font&height=21" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:80px; height:21px;" allowTransparency="true"></iframe></td>', urlencode( $post_url ) ),
				'twitter'     => sprintf( '<td><a href="https://twitter.com/share" class="twitter-share-button" data-url="%s" data-text="%s">Tweet</a></td>', esc_attr( $post_url ), esc_attr( $post_title ) ),
				'googleplus'  => sprintf( '<td class="w2b-gplusone"><div class="g-plusone" data-size="medium" data-href="%s"></div></td>', esc_attr( $post_url ) ),
				'stumbleupon' => sprintf( '<td><su:badge layout="1" location="%s"></su:badge></td>', esc_attr( $post_url ) ),
				'digg'        => '<td><a class="DiggThisButton DiggCompact"></a></td>',
				'pinterest'   => sprintf( '<td class="w2b-pinit"><a href="http://pinterest.com/pin/create/button/?url=%s&media=%s&description=%s" class="pin-it-button" count-layout="horizontal"><img border="0" src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a></td>', urlencode( $post_url ), urlencode( $post_image ), urlencode( $this->get_post_excerpt( 500 ) ) ),
				'linkedin'    => sprintf( '<td><script type="IN/Share" data-url="%s" data-counter="right"></script></td>', esc_attr( $post_url ) ),
				'buffer'      => sprintf( '<td><a href="http://bufferapp.com/add" class="buffer-add-button" data-text="%s" data-url="%s" data-count="horizontal" data-picture="%s">Buffer</a></td>', esc_attr( $post_title ), esc_attr( $post_title ), esc_attr( $post_image ) ),
			);
			
			$html          = "<div id=\"w2bSocialFloat\" class=\"w2bSocialFloat\">\n<table  width=\"100%\" class=\"w2bSocialFloat\">\n<tr>\n";

			foreach ( (array) $w2b_option['w2b_social_services'] as $service ) {
				if ( array_key_exists( strtolower( $service ), $social_html ) )
					$html .= $social_html[ strtolower( $service ) ] . "\n";
			}

			$html .= "</tr>\n</table>\n</div>";

			return $html;

		}

	}

	/**
	 * Insert CSS on wp_head.
	 * 
	 * @since 1.0
	 * 
	 * @return null 
	 */
	function w2b_insert_socialcss(){

		$w2b_option = get_option( $this->setting );
		$color      = empty( $w2b_option['w2bBgColor'] ) ? 'FFFFFF' : $w2b_option['w2bBgColor'];

		if ( count( $w2b_option['w2b_social_services'] ) != 0 && $w2b_option['w2b_enable_floating_social'] ) {

			echo <<<W2BCSS
<style type='text/css'>
/*<![CDATA[*/
#w2bSocialFloat {
	clear:both;
	padding: 6px 0;
	display:block;
	background:#{$color};
}
.w2bSocialFloat{
	margin:0 !important;
}
#w2bSocialFloat td{
	padding:4px;
	margin:0;
	border:none;
	vertical-align:top;
}
#w2bSocialFloat td iframe{
	max-width:82px;
	width:82px !important;
}
#w2bSocialFloat td.w2b-gplusone *{
	max-width:80px !important;
}
#w2bSocialFloat td.w2b-pinit > a {
	vertical-align:top !important;
}
#w2bSocialFloat.w2bFloatSocial{
	position: fixed;
	top:0;
	z-index:999;
	border-bottom:1px solid #ccc;
	-webkit-box-shadow:0 1px 1px rgba(0,0,0,0.15);
	-moz-box-shadow:0 1px 1px rgba(0,0,0,0.15);
	box-shadow:0 1px 1px rgba(0,0,0,0.15);
}
/*]]>*/
</style>
W2BCSS;

		}

	}

	/**
	 * Get the Featured Image OR First Image in the Post.
	 * 
	 * @since 1.1
	 * 
	 * @return string Featured Image OR First Image in the Post.
	 */
	function get_post_image() {

		global $post;
		$post_thumbnail_url = '';
		if( has_post_thumbnail( $post->ID ) ) { 
			$post_thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ),  'single-post-thumbnail' );  
			$post_thumbnail_url = $post_thumbnail[0];
		} else {
			preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
			if ( isset( $matches[1] ) && isset( $matches[1][0] ) )
				$post_thumbnail_url = $matches[1][0];
		}

		return $post_thumbnail_url;

	}

	/**
	 * Get the Post excerpts of specifed length.
	 * 
	 * @since 1.1
	 * 
	 * @param  integer $length Length of the post content.
	 * @return string          Post excerpts.
	 */
	function get_post_excerpt( $length = 100 ) {
		
		$content = wp_strip_all_tags( get_the_content(), true );

		return substr( $content, 0, $length );

	}

}

/* New Instance */
new W2B_Floating_Social_Sharing_Bar;