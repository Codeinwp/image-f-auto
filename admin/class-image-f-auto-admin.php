<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       themeisle.com
 * @since      1.0.0
 *
 * @package    Image_F_Auto
 * @subpackage Image_F_Auto/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Image_F_Auto
 * @subpackage Image_F_Auto/admin
 * @author     Themeisle <friends@themeisle.com>
 */
class Image_F_Auto_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Image_F_Auto_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Image_F_Auto_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/image-f-auto-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Image_F_Auto_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Image_F_Auto_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/image-f-auto-admin.js', array( 'jquery' ), $this->version, false );


		$screen = get_current_screen();
		if( $screen->base == "cloudinary-image-management-and-manipulation-in-the-cloud-cdn/options" ){
			wp_enqueue_script( $this->plugin_name."_options", plugin_dir_url( __FILE__ ) . 'js/image-f-auto-control.js', array( 'jquery' ), $this->version, false );
		}


	}


	public function ifa_image_downsize($dummy, $post_id, $size) {

	    $url = wp_get_attachment_url($post_id);
	    $metadata = wp_get_attachment_metadata($post_id);

			if (isset($metadata["cloudinary"])) {
					return $this->build_resize_url_2($url, $metadata, $size);
			} else {
					return FALSE;
			}
	}


	function get_wp_sizes() {
    if (isset($this->sizes)) return $this->sizes;
    // make thumbnails and other intermediate sizes
    global $_wp_additional_image_sizes;

    foreach ( get_intermediate_image_sizes() as $s ) {
      $sizes[$s] = array( 'width' => '', 'height' => '', 'crop' => false );
      if ( isset( $_wp_additional_image_sizes[$s]['width'] ) )
        $sizes[$s]['width'] = intval( $_wp_additional_image_sizes[$s]['width'] ); // For theme-added sizes
      else
        $sizes[$s]['width'] = get_option( "{$s}_size_w" ); // For default sizes set in options
      if ( isset( $_wp_additional_image_sizes[$s]['height'] ) )
        $sizes[$s]['height'] = intval( $_wp_additional_image_sizes[$s]['height'] ); // For theme-added sizes
      else
        $sizes[$s]['height'] = get_option( "{$s}_size_h" ); // For default sizes set in options
      if ( isset( $_wp_additional_image_sizes[$s]['crop'] ) )
        $sizes[$s]['crop'] = intval( $_wp_additional_image_sizes[$s]['crop'] ); // For theme-added sizes
      else
        $sizes[$s]['crop'] = get_option( "{$s}_crop" ); // For default sizes set in options
    }

    $this->sizes = apply_filters( 'intermediate_image_sizes_advanced', $sizes );
    return $this->sizes;
  }




	function build_resize_url_2($url, $metadata, $size) {
    if (preg_match('#(.*?)/(v[0-9]+/.*)$#', $url, $matches)) {
      if (!$size) {
        return array($url, $metadata["width"], $metadata["height"], true);
      }
      if (is_array($size)) {
        $wanted = array("width" => $size[0], "height" => $size[1]);
        $crop = false;
      } else {
        $sizes = $this->get_wp_sizes();
        $wanted = $sizes[$size];
        $crop = $wanted["crop"];
      }
      $transformation = "";
      $src_w = $dst_w = $metadata["width"];
      $src_h = $dst_h = $metadata["height"];
      if ($crop) {
        $resized = image_resize_dimensions($metadata['width'], $metadata['height'], $wanted['width'], $wanted['height'], true);
        if ($resized) {
          list ($dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) = $resized;
          $transformation = "c_crop,h_$src_h,w_$src_w,x_$src_x,y_$src_y/";
        }
      }

      list($width, $height) = image_constrain_size_for_editor($dst_w, $dst_h, $size);
      if ($width != $src_w || $height != $src_h) {
        $transformation = $transformation . "h_$height,w_$width/";
      }

      $f_auto = get_option( 'enable_fauto' );
      if( $f_auto == "true" ){
        if(!empty($transformation)){
          $transformation = "f_auto,".$transformation;
        }else{
          $transformation= "f_auto/";
        }
      }
      $url = "$matches[1]/$transformation$matches[2]";

      return array($url, $width, $height, true);
    } else {
      return false;
    }
  }
  function remote_resize($dummy, $post_id, $size) {
    $url = wp_get_attachment_url($post_id);
    $metadata = wp_get_attachment_metadata($post_id);

    if (Cloudinary::option_get($metadata, "cloudinary")) {

      return $this->build_resize_url($url, $metadata, $size);
    } else {
      return FALSE;
    }
  }

	function fauto_save_callback(){
		$fauto = $_POST['fauto'];
		$opt = array("true", "false");
		if( in_array( $fauto, $opt)  ){
      update_option( 'enable_fauto', $fauto );
		}
		else return false;
	}

	function fauto_state(){
		$screen = get_current_screen();
		if( $screen->base == "cloudinary-image-management-and-manipulation-in-the-cloud-cdn/options" ){
			wp_enqueue_script( $this->plugin_name."_maintain_state", plugin_dir_url( __FILE__ ) . 'js/image-f-auto-maintain.js', array( 'jquery' ), $this->version, false );
			$fauto = get_option('enable_fauto');
			wp_localize_script( $this->plugin_name."_maintain_state", 'state', $fauto );
		}

	}

}
