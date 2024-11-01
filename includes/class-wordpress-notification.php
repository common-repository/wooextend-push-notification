<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
Class WPN_Push_Notification {

	public function __construct() {

		// Register push notificaction
		add_action( 'init', array($this, 'wpn_register_push_notification') );
		add_action( 'add_meta_boxes_push-notification', array($this,'wpn_add_user_to_notify'));

		// Save notification user mapping
		add_action("save_post", array($this, "wpn_save_push_notification_users"), 10, 3);

		// Show the progressbar on front end
		add_action("wp_head", array($this, "wpn_add_notification_front"));
		
		add_action( 'admin_enqueue_scripts', array($this, 'wpn_wp_admin_style') );

		add_filter( 'admin_footer_text', array( $this, 'wpn_admin_footer_text'), 1 );
	}
	public function wpn_wp_admin_style() {
		wp_enqueue_script('select2', plugins_url( '/assets/js/jquery-select2.js', dirname(__FILE__)), array('jquery'));
		wp_enqueue_style('select2', plugins_url('/assets/css/select2.min.css', dirname(__FILE__)));
	}
	public function wpn_register_push_notification() {

		// Set UI labels for Custom Post Type
	    $labels = array(
	        'name'                => _x( 'Push Notification', 'Post Type General Name', 'wpn_push_notification' ),
	        'singular_name'       => _x( 'Push Notification', 'Post Type Singular Name', 'wpn_push_notification' ),
	        'menu_name'           => __( 'Push Notifications', 'wpn_push_notification' ),
	        'parent_item_colon'   => __( 'Parent Push Notification', 'wpn_push_notification' ),
	        'all_items'           => __( 'All Push Notifications', 'wpn_push_notification' ),
	        'view_item'           => __( 'View Push Notification', 'wpn_push_notification' ),
	        'add_new_item'        => __( 'Add New Push Notification', 'wpn_push_notification' ),
	        'add_new'             => __( 'Add New', 'wpn_push_notification' ),
	        'edit_item'           => __( 'Edit Push Notification', 'wpn_push_notification' ),
	        'update_item'         => __( 'Update Push Notification', 'wpn_push_notification' ),
	        'search_items'        => __( 'Search Push Notification', 'wpn_push_notification' ),
	        'not_found'           => __( 'Not Found', 'wpn_push_notification' ),
	        'not_found_in_trash'  => __( 'Not found in Trash', 'wpn_push_notification' ),
	    );
	     
	// Set other options for Custom Post Type
	     
	    $args = array(
	        'label'               => __( 'Push Notification', 'wpn_push_notification' ),
	        'description'         => __( 'Show push notification to site users.', 'wpn_push_notification' ),
	        'labels'              => $labels,
	        'supports'            => array( 'title', 'editor' ),
	        'hierarchical'        => false,
	        'public'              => false,
	        'show_ui'             => true,
	        'show_in_menu'        => true,
	        'show_in_nav_menus'   => true,
	        'show_in_admin_bar'   => true,
	        'menu_position'       => 5,
	        'can_export'          => true,
	        'has_archive'         => true,
	        'exclude_from_search' => false,
	        'publicly_queryable'  => false,
	        'capability_type'     => 'page',
	    );
	     
	    // Registering your Custom Post Type
	    register_post_type( 'push-notification', $args );
	}

	//Add field
	public function wpn_add_user_to_notify( $meta_id ) {
	 
	    add_meta_box("we-users-meta-box", "Select users", array($this, "wpn_notification_meta_box_markup"), "push-notification", "side", "high", null);
	    add_meta_box("we-appearance-meta-box", "Appearance", array($this, "wpn_notification_appearance"), "push-notification", "side", "high", null);
	}

	public function wpn_notification_appearance($object) {

		?><div>
        	<div class="misc-pub-section">
	            <label for="txtColor">Font Color</label><br/>
	            <input type="color" id="txtColor" name="txtColor" value="#<?php $color = get_post_meta($object->ID, "we-notification-color", true);echo !empty($color)?$color:'FAFAFA'; ?>">
            </div>
            <div class="misc-pub-section">
	            <label for="txtBGColor">Background Color</label><br/>
	            <input type="color" name="txtBGColor" id="txtBGColor" value="#<?php $color = get_post_meta($object->ID, "we-notification-bg-color", true);echo !empty($color)?$color:'000000'; ?>">
            </div>
        </div><?php
	}

	public function wpn_notification_meta_box_markup($object) {
	    wp_nonce_field(basename(__FILE__), "meta-box-nonce");

	    // Get users
	    $arrUsers = get_users();
	    
	    ?>
	    <style>
	    	.select2-container--default .select2-selection--multiple .select2-selection__rendered li {
	    		line-height: 25px !important;
	    	}
	    	.notification-help-tip {
			    color: #666;
			    display: inline-block;
			    font-size: 1.1em;
			    font-style: normal;
			    height: 16px;
			    line-height: 16px;
			    position: relative;
			    vertical-align: middle;
			    width: 16px;
			}
			.notification-help-tip::after {
			    font-family: Dashicons;
			    speak: none;
			    font-weight: 400;
			    font-variant: normal;
			    text-transform: none;
			    line-height: 1;
			    -webkit-font-smoothing: antialiased;
			    margin: 0;
			    text-indent: 0;
			    position: absolute;
			    top: 0;
			    left: 0;
			    width: 100%;
			    height: 100%;
			    text-align: center;
			    content: "";
			    cursor: help;
			}
			.tooltip span {
				display: none;
			}
			.tooltip:hover span, .tooltip:focus span {
			    display:block;
			    position:absolute;
			    top:1em;
			    font-size: 10px;
			    left:1.5em;
			    padding: 0.2em 0.6em;
			    background-color:black;
			    border-radius: 3px;
			    color:#FFFFFF;
			    width: 150px;
			    z-index: 999999;
			}
	    </style>
        <div>
        	<div class="misc-pub-section">
	            <label for="we-notification-users">Select user to show push notification</label><br/>
	            <select name="we-notification-users[]" multiple="multiple" id="we-notification-users" disabled="disabled"></select>
            </div>
            <div class="misc-pub-section">
		        <label for="we-show-all-users">Show to all users</label><span class="notification-help-tip tooltip">&nbsp;<span>This is a PRO version feature.</span></span>
		            <?php
		                $checkbox_value = get_post_meta($object->ID, "we-show-all-users", true);

		                if($checkbox_value == "true") {
		                    ?><input name="we-show-all-users" type="checkbox" value="true" checked disabled="disabled"><?php
		                } else if($checkbox_value == "") {
		                    ?><input name="we-show-all-users" type="checkbox" value="true" checked disabled="disabled"><?php
		                }
		            ?>
	        </div>
        </div>
        <script type="text/javascript">
        	$ = jQuery;
        	$(document).ready(function() {
        		$('#we-notification-users').select2();
        	});
        </script><?php  
	}

	// Save user options for push notification
	public function wpn_save_push_notification_users($post_id, $post, $update) {

		$nonce = sanitize_title($_POST["meta-box-nonce"]);
	    if (!isset($nonce) || !wp_verify_nonce($nonce, basename(__FILE__)))
	        return $post_id;

	    if(!current_user_can("edit_post", $post_id))
	        return $post_id;

	    if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE)
	        return $post_id;

	    $slug = "push-notification";
	    if($slug != $post->post_type)
	        return $post_id;

	    $meta_box_text_value = "";
	    $meta_box_dropdown_value = "";
	    $meta_box_checkbox_value = "";
	    
	    $notification_user = array_map( 'esc_attr', $_POST["we-notification-users"] );
	    if(isset($notification_user)) {
	        update_post_meta($post_id, "we-notification-users", $notification_user);
	    }  	    

	    $notify_all_users = sanitize_title($_POST["we-show-all-users"]);
	    if(isset($notify_all_users)) {
	        update_post_meta($post_id, "we-show-all-users", $notify_all_users);
	    }   	    

	    $notification_color = sanitize_title($_POST["txtColor"]);
	    if(isset($notification_color)) {
	        update_post_meta($post_id, "we-notification-color", $notification_color);
	    }
	    
	    $bg_notification_color = sanitize_title($_POST["txtBGColor"]);
	    if(isset($_POST["txtBGColor"])) {
	        update_post_meta($post_id, "we-notification-bg-color", $bg_notification_color);
	    }	    
	}

	public function wpn_add_notification_front() {

		$args = array(
			'post_status'	=>	'publish',
			'post_type'		=>	'push-notification'
		);
		$arrNotify = new WP_Query( $args );
		
		$user_id = get_current_user_id();
		// Loop for all notifications
		
		foreach ($arrNotify->posts as $key => $value) {
			
			// Check if we need to show notification to current user or to all
			?><div class="we-notification"><?php echo $value->post_content;?></div><?php
			
			?><style>
				.we-notification {
					line-height: 35px;
					width: 100%;
					background-color: #<?php echo get_post_meta($value->ID, "we-notification-bg-color", true);?>;
					color: #<?php echo get_post_meta($value->ID, "we-notification-color", true);?>;
					text-align: center;
					position: fixed;
					z-index: 99999;
				}
			</style><?php
		}		
	}

	public function wpn_admin_footer_text( $footer_text ) {
	    
	    $current_screen = get_current_screen();

	    // Check to make sure we're on a discount admin page.
	    if ( isset( $current_screen->id ) && ( $current_screen->id == 'push-notification' || $current_screen->id == 'edit-push-notification') ) {
	        
	        /* translators: %s: five stars */
	        $footer_text = sprintf( __( 'For support, write us at : info@wooextend.com and if you like <strong>WooExtend Push Notification</strong> please leave us a %s rating. A huge thanks in advance!', 'woocommerce' ), '<a href="https://wordpress.org/support/plugin/wooextend-push-notification/reviews?rate=5#new-post" target="_blank" class="wc-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'woocommerce' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>' );
	        
	    }

	    return $footer_text;
	}
}
new WPN_Push_Notification();