<?php
namespace WPRBSubRedditRSS\Hooks;

use WPRBSUBREDDITRSS;

if (!defined('ABSPATH')) {
    exit;
}
require_once(WPRBSUBREDDITRSS_DIR . 'includes/Validators/UrlValidator.php');

class FeedCPT{
    public $urlValidator;
    public function __construct(){
        $this->urlValidator = new WPRBSUBREDDITRSS\Validators\UrlValidator();
    }
    public function boot(){
        add_action('init', array($this, 'register_feed_post_type'));
        add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ));
        add_action( 'save_post',      array( $this, 'save'));
    }

    public function register_feed_post_type() {
        register_post_type('subreddit_feed',
            array(
                'labels'      => array(
                    'name'          => __( 'Subreddit Feeds', 'wprb-subreddit-rss' ),
                    'singular_name' => __( 'Subreddit Feed', 'wprb-subreddit-rss' ),
                ),
                'public'      => true,
                'has_archive' => true,
                'rewrite'     => array( 'slug' => 'subreddit-feed' ), // my custom slug
                'supports' => array('title','author')
            )
        );
    }

    public function add_meta_box( $post_type ) {
		// Limit meta box to certain post types.
		$post_types = array( 'subreddit_feed');

		if ( in_array( $post_type, $post_types ) ) {
			add_meta_box(
				'subreddit_url',
				__( 'Enter your subreddit URL', 'WPRBSubredditRSS' ),
				array( $this, 'render_meta_box_content' ),
				$post_type,
				'advanced',
				'high'
			);
		}
	}

    public function render_meta_box_content( $post ) {

		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'wprb_subreddit_feed_url', 'wprb_subreddit_feed_url_nonce' );

		// Use get_post_meta to retrieve an existing value from the database.
		$value = get_post_meta( $post->ID, '_wprb_subreddit_url', true );

		// Display the form, using the current value.
		?>
		<label for="myplugin_new_field">
			<?php _e( 'Subreddit URL', 'WPRBSubredditRSS' ); ?>
		</label>
        <br/>
		<input type="text" id="wprb_subreddit_url" name="wprb_subreddit_url" value="<?php echo esc_attr( $value ); ?>" />
		<?php
	}

    	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['wprb_subreddit_feed_url_nonce'] ) ) {
			return $post_id;
		}

		$nonce = $_POST['wprb_subreddit_feed_url_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'wprb_subreddit_feed_url' ) ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return $post_id;
			}
		} else {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return $post_id;
			}
		}

		/* OK, it's safe for us to save the data now. */

		// Sanitize the user input.
		$mydata = sanitize_text_field( $_POST['wprb_subreddit_url'] );
        if($this->customValidation($mydata)) {
            // Update the meta field.
		    update_post_meta( $post_id, '_wprb_subreddit_url', $mydata );
        }
        
		
	}

    public function customValidation($url){
        return $this->urlValidator->validate($url);
    }
}

(new FeedCPT())->boot();
