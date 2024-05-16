<?php
/*
Plugin Name:  Automation Core Functionality
Plugin URI:   https://laurasaner.com
Description:  Powers all user management and automations.
Version:      1.0
Author:       Laura Saner
Author URI:   https://laurasaner.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  automaticdust
Domain Path:  /languages
*/


//Populate ACF company checkbox with available companies.
function automaticdust_scripts() {
    wp_enqueue_style( 'base-style',  plugin_dir_url( __FILE__ ) . 'style.css' );
    wp_enqueue_style( 'bootstrap-style',  'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css' );
	wp_enqueue_script( 'scripts-js', plugin_dir_url( __FILE__ ) . 'scripts.js', array('jquery'), '1.0', false  );
	wp_enqueue_script( 'bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js', array('jquery'), '1.0', false  );
}
add_action( 'wp_enqueue_scripts', 'automaticdust_scripts' );


function usercompany( $field ) {
	$args = array(  
        'post_type' => 'client',
        'post_status' => 'publish',
        'posts_per_page' => -1, 
        'orderby' => 'title', 
        'order' => 'ASC', 
    );

    $companies = new WP_Query( $args );
        
    while ( $companies->have_posts() ) : $companies->the_post();
		$title = get_the_title();
		$field['choices'][ $title ] = $title ;
    endwhile;
	
    return $field;
    wp_reset_postdata(); 
}
add_filter('acf/load_field/name=user_company', 'usercompany');



//Redirect login page for clients.
function redirect_login_page()
{
    $login_page  = home_url('/');
    $page_viewed = basename($_SERVER['REQUEST_URI']);

    if ($page_viewed == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET') {
        wp_redirect($login_page);
        exit;
    }
}
add_action('init', 'redirect_login_page');


//Display client posts on their dashboard and allow for client approval
function client_dashboard() { 
	if ( is_user_logged_in() ) :
		$user = wp_get_current_user();
		$userid = "user_" . $user->data->ID;
		if ( in_array( 'contributor', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles ) ) :
			//If page has been saved, update ACF values
			if ( isset( $_GET['saved'] ) || isset($_GET['declined'] ) ) :
				$saved = $_GET['saved'];
				$declined = $_GET['saved'];
				$saved = explode(",", $saved);
				$declined = explode(",", $declined);
				foreach ( $saved as $s ) {
					$s = (int)$s;
					update_field('field_65bad705e03ee', 'Approve', $s);
				}
				foreach ( $declined as $d ) {
					$d = (int)$d;
					update_field('field_65bad705e03ee', 'Decline', $d);
				}
			else :

				$usercompany = get_field( 'user_company', $userid );
				$companyid = get_cat_ID( $usercompany );

				$args = array( 
					'cat' => $companyid,  
					'posts_per_page' => -1,
					'post_type' => 'post', 
				);
				$companyposts = new WP_Query( $args );
				if( $companyposts->have_posts() ) :
					$message = "<div class='container d-flex flex-column h-100'>";
					$message .= "<div class='row gy-5'>";
					$message .= "<div class='col-12 text-end'><button id='post-saves'>Save</button></div>";
					while( $companyposts->have_posts() ) : $companyposts->the_post();
						$message .= "<div class='col-12 post-box' id='" . get_the_ID() . "'> <h2> " . get_the_title() . "</h2>";
						$message .= "<div class='post-content'>" . get_the_content() . "</div>";
						$message .= "<div class='post-answer row'><div class='post-approve col-6'>Approve</div><div class='post-decline col-6'>Decline</div></div></div>";
					endwhile;
					$message .= "<div class='col-12 text-end'><button id='post-saves'>Save</button></div>";
					$message .= "</div></div>";
				else:
					$message = "There are currently no posts to approve. Please check back later or contact us for a status!";
				endif;

				return $message;
			endif;
		endif;
	else :
		echo "Please <a href='/'>log in</a> to view your dashboard.";
	endif;
}

add_shortcode('clientdashboard', 'client_dashboard');


