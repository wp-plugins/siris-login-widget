<?php
/*
Plugin Name: Siris Login Widget
Plugin URI: http://sirisgraphics.com/development/siris-login-widget
Description: A widget to add login form to your sidebar with a custom menu.
Version: 1.2
Author: Vamsi Pulavarthi
Author URI: http://sirisgraphics.com/author/vamsi
License: GPLv2
*/

$plugin_tag = "sg-slw";

//Siris Login Widget Class
class SirisLoginWidget extends WP_Widget {
    
    function SirisLoginWidget() {
		$widget_ops = array( 'classname' => 'slw', 
                             'description' => __( ' A widget to add login form to your sidebar with a custom menu.', $plugin_tag ) );
		$this->WP_Widget( false, __( 'Siris Login Widget', $plugin_tag ), $widget_ops );

        wp_register_style( 'my-plugin', plugins_url( 'siris-login-widget/css/slwstyle.css' ) );
		wp_enqueue_style( 'my-plugin' );
	}

    function form($instance) {

        $slw_menu = isset( $instance['slw_menu'] ) ? $instance['slw_menu'] : '';
        
        // Get menus
		$slwmenus = wp_get_nav_menus( array( 'orderby' => 'name' ) );
        
        // If no menus exists, direct the user to go and create some.
		if ( !$slwmenus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('slw_menu'); ?>"><?php _e('Select Menu:'); ?></label>
            <select id="<?php echo $this->get_field_id('slw_menu'); ?>" name="<?php echo $this->get_field_name('slw_menu'); ?>">
				<option value="0"><?php _e( '&mdash; Select &mdash;' ) ?></option>
		<?php
			foreach ( $slwmenus as $slwmenu ) {
				echo '<option value="' . $slwmenu->term_id . '"'
					. selected( $slw_menu, $slwmenu->term_id, false )
					. '>'. esc_html( $slwmenu->name ) . '</option>';
			}
		?>
			</select>
        </p>
        <?php    
    }

    function update($new_instance, $old_instance) {
        //$instance = $old_instance;
        $instance['slw_menu'] = (int) $new_instance['slw_menu'];
        return $instance;
    }

    function widget($args, $instance) {
		global $user_ID, $user_identity, $user_level, $user_email, $user_login;
		extract($args);
		echo $before_widget;
        //$slw_menu = empty( $instance['slw_menu'] ) ? '' : $instance['slw_menu'];

        if ( is_user_logged_in() ) 
        {
		    $slw_menu = ! empty( $instance['slw_menu'] ) ? wp_get_nav_menu_object( $instance['slw_menu'] ) : false;
		    if ( !$slw_menu )
			    return;
        
            echo $before_title;
            echo "Welcome ";
            echo $user_identity;
            echo $after_title;    
            echo "<div class='clearfix' style='padding: 5px;'>";
		    wp_nav_menu( 
                array( 'theme_location' => 'special',
                    'container' => 'div', 
                    'container_class' => '',
                    'menu' => $slw_menu, 
                    'menu_class' => 'svmenu',
                    'items_wrap' => '<ul class="%2$s">%3$s</ul>',
                    'fallback_cb' => 'false' ) );
            echo "</div>";
        ?>

        <?php
        }
        else
        {
            echo $before_title;
            echo "Login Form";
            echo $after_title;  
            echo "<div class='clearfix' style='padding: 5px;'>";
            $args = array(
                'echo' => true,
                'redirect' => site_url( $_SERVER['REQUEST_URI'] ),
                'form_id' => 'sirisloginform',
                'label_username' => __( 'User Name' ),
                'label_password' => __( 'Password' ),
                'label_remember' => __( 'Remember Me' ),
                'label_log_in' => __( 'Log In' ),
                'id_username' => 'user_login',
                'id_password' => 'user_pass',
                'id_remember' => 'rememberme',
                'id_submit' => 'wp-submit',
                'remember' => true,
                'value_username' => NULL,
                'value_remember' => false );            
            wp_login_form( $args );

            echo "</div>";
        }
        echo $after_widget;
    }
}

// init widgets
add_action('widgets_init', 'sirisWidgetsInit');
function sirisWidgetsInit() {
    $plugin_dir = basename(dirname(__FILE__));
    load_plugin_textdomain($plugin_tag, false, $plugin_dir . '/languages' );
	register_widget('SirisLoginWidget');
}
/*
//Add jQuery UI Classes to Menu Items
add_filter('nav_menu_css_class' , 'special_nav_class' , 10 , 2);
function special_nav_class($classes, $item){
    $classes[] = "ui-menu-item";
    return $classes;
}
*/
//Add Logout list to Menu's added by Siris Login Widget
add_filter('wp_nav_menu_items', 'add_login_logout_link', 10, 2);
function add_login_logout_link($items, $args) {
    ob_start();
    wp_loginout('index.php');
    $loginoutlink = ob_get_contents();
    ob_end_clean();
    if($args->theme_location == 'special') {
        $items .= '<li class="menu-item ui-menu-item">'. $loginoutlink .'</li>';        
    }
    return $items;
}
?>