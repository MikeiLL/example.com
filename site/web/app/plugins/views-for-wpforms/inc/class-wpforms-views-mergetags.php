<?php if ( ! defined( 'ABSPATH' ) ) exit;

class WPForms_Views_MergeTags {
	private static $instance;

	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WPForms_Views_MergeTags ) ) {
			self::$instance = new WPForms_Views_MergeTags();
		}
		return self::$instance;
	}

	/**
	 * Custom replace() method for custom post meta or user meta.
	 *
	 * @param string|array $subject
	 * @return string
	 */
	public function replace( $subject ) {
		// Recursively replace merge tags.

		if ( is_array( $subject ) ) {
			foreach ( $subject as $i => $s ) {
				$subject[ $i ] = $this->replace( $s );
			}
			return $subject;
		}

		/**
		 * Replace Custom Post Meta
		 * {post_meta:foo} --> meta key is 'foo'
		 */
		preg_match_all( "/{post_meta:(.*?)}/", $subject, $post_meta_matches );
		if ( ! empty( $post_meta_matches[0] ) ) {
			/**
			 * $matches[0][$i]  merge tag match     {post_meta:foo}
			 * $matches[1][$i]  captured meta key   foo
			 */
			foreach ( $post_meta_matches[0] as $i => $search ) {
				$meta_key   = $post_meta_matches[1][$i];
				$meta_value = get_post_meta( $this->post_id(), $meta_key, true  );

				if ( '' != $meta_value ) {
					$subject = str_replace( $search, $meta_value, $subject );
				} else {
					$subject = str_replace( $search, '', $subject );
				}
			}
		}

		/**
		 * Replace Date Fields
		 * {post_meta:foo} --> meta key is 'foo'
		 */
		preg_match_all( "/{date:(.*?)}/", $subject, $date_matches );
		if ( ! empty( $date_matches[0] ) ) {
			/**
			 * $matches[0][$i]  merge tag match     {post_meta:foo}
			 * $matches[1][$i]  captured meta key   foo
			 */
			foreach ( $date_matches[0] as $i => $search ) {
				$date_string   = $date_matches[1][$i];

				$date_value = date( 'Y-m-d',strtotime( $date_string ) );
				if ( '' != $date_value ) {
					$subject = str_replace( $search, $date_value, $subject );
				} else {
					$subject = str_replace( $search, '', $subject );
				}
			}
		}

		/**
		 * Replace Custom User Meta
		 * {user_meta:foo} --> meta key is 'foo'
		 */
		$user_id = get_current_user_id();
		preg_match_all( "/{user_meta:(.*?)}/", $subject, $user_meta_matches );
		// if user is logged in and we have user_meta merge tags
		if ( ! empty( $user_meta_matches[0] ) && $user_id != 0  ) {
			/**
			 * $matches[0][$i]  merge tag match     {user_meta:foo}
			 * $matches[1][$i]  captured meta key   foo
			 */
			foreach ( $user_meta_matches[0] as $i => $search ) {
				$meta_key = $user_meta_matches[1][$i];
				$meta_value = get_user_meta( $user_id, $meta_key, /* $single */ true );
				$subject = str_replace( $search, $meta_value, $subject );
			}
			// if a user is not logged in, but there are user_meta merge tags
		} elseif ( ! empty( $user_meta_matches[0] ) && $user_id == 0 ) {
			$subject = '';
		}
		return $this->get_merge_tag_data( $subject );
	}

	public function get_merge_tag_data( $subject ) {
		if ( is_array( $subject ) ) {
			foreach ( $subject as $i => $s ) {
				$subject[ $i ] = $this->get_merge_tag_data( $s );
			}
			return $subject;
		}


		preg_match_all( "/{([^}]*)}/", $subject, $matches );

		if ( empty( $matches[0] ) ) return $subject;

		foreach ( $this->get_merge_tags() as $merge_tag ) {
			if ( ! isset( $merge_tag[ 'tag' ] ) || ! in_array( $merge_tag[ 'tag' ], $matches[0] ) ) continue;

			if ( ! isset( $merge_tag[ 'callback' ] ) ) continue;

			if ( is_callable( array( $this, $merge_tag[ 'callback' ] ) ) ) {
				$replace = $this->{$merge_tag[ 'callback' ]}();
			} elseif ( is_callable( $merge_tag[ 'callback' ] ) ) {
				$replace = $merge_tag[ 'callback' ]();
			} else {
				$replace = '';
			}

			$subject = str_replace( $merge_tag[ 'tag' ], $replace, $subject );
		}

		return $subject;
	}


	protected function post_id() {
		global $post;

		if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			// If we are doing AJAX, use the referer to get the Post ID.
			$post_id = url_to_postid( wp_get_referer() );
		} elseif ( $post ) {
			$post_id = $post->ID;
		} else {
			return false; // No Post ID found.
		}

		return $post_id;
	}

	protected function post_title() {
		$post_id = $this->post_id();
		if ( ! $post_id ) return;
		$post = get_post( $post_id );
		return ( $post ) ? $post->post_title : '';
	}

	protected function post_url() {
		$post_id = $this->post_id();
		if ( ! $post_id ) return;
		$post = get_post( $post_id );
		return ( $post ) ? get_permalink( $post->ID ) : '';
	}

	protected function post_author() {
		$post_id = $this->post_id();
		if ( ! $post_id ) return;
		$post = get_post( $post_id );
		if ( ! $post ) return '';
		$author = get_user_by( 'id', $post->post_author );
		return $author->display_name;
	}

	protected function post_author_email() {
		$post_id = $this->post_id();
		if ( ! $post_id ) return;
		$post = get_post( $post_id );
		if ( ! $post ) return '';
		$author = get_user_by( 'id', $post->post_author );
		return $author->user_email;
	}

	protected function user_id() {
		$current_user = wp_get_current_user();

		return ( $current_user ) ? $current_user->ID : '';
	}

	protected function user_first_name() {
		$current_user = wp_get_current_user();

		return ( $current_user ) ? $current_user->user_firstname : '';
	}

	protected function user_last_name() {
		$current_user = wp_get_current_user();

		return ( $current_user ) ? $current_user->user_lastname : '';
	}

	protected function user_display_name() {
		$current_user = wp_get_current_user();

		return ( $current_user ) ? $current_user->display_name : '';
	}

	protected function user_username() {
		$current_user = wp_get_current_user();

		return ( $current_user ) ? $current_user->user_nicename : '';
	}

	protected function user_email() {
		$current_user = wp_get_current_user();

		return ( $current_user ) ? $current_user->user_email : '';
	}

	protected function user_url() {
		$current_user = wp_get_current_user();

		return ( $current_user ) ? $current_user->user_url : '';
	}

	protected function admin_email() {
		return get_option( 'admin_email' );
	}

	protected function site_title() {
		return get_bloginfo( 'name' );
	}

	protected function site_url() {
		return get_bloginfo( 'url' );
	}

	protected function get_merge_tags() {
		return array(

			/*
		|--------------------------------------------------------------------------
		| Post ID
		|--------------------------------------------------------------------------
		*/

			'id' => array(
				'id' => 'id',
				'tag' => '{wp:post_id}',
				'label' => esc_html__( 'Post ID', 'views-for-wpforms' ),
				'callback' => 'post_id'
			),

			/*
		|--------------------------------------------------------------------------
		| Post Title
		|--------------------------------------------------------------------------
		*/

			'title' => array(
				'id' => 'title',
				'tag' => '{wp:post_title}',
				'label' => esc_html__( 'Post Title', 'views-for-wpforms' ),
				'callback' => 'post_title'
			),

			/*
		|--------------------------------------------------------------------------
		| Post URL
		|--------------------------------------------------------------------------
		*/

			'url' => array(
				'id' => 'url',
				'tag' => '{wp:post_url}',
				'label' => esc_html__( 'Post URL', 'views-for-wpforms' ),
				'callback' => 'post_url'
			),

			/*
		|--------------------------------------------------------------------------
		| Post Author
		|--------------------------------------------------------------------------
		*/

			'author' => array(
				'id' => 'author',
				'tag' => '{wp:post_author}',
				'label' => esc_html__( 'Post Author', 'views-for-wpforms' ),
				'callback' => 'post_author'
			),

			/*
		|--------------------------------------------------------------------------
		| Post Author Email
		|--------------------------------------------------------------------------
		*/

			'author_email' => array(
				'id' => 'author_email',
				'tag' => '{wp:post_author_email}',
				'label' => esc_html__( 'Post Author Email', 'views-for-wpforms' ),
				'callback' => 'post_author_email'
			),

			/*
		|--------------------------------------------------------------------------
		| Post Meta
		|--------------------------------------------------------------------------
		*/

			'post_meta' => array(
				'id' => 'post_meta',
				'tag' => '{post_meta:YOUR_META_KEY}',
				'label' => esc_html__( 'Post Meta', 'views-for-wpforms' ),
				'callback' => null
			),

			/*
		|--------------------------------------------------------------------------
		| User ID
		|--------------------------------------------------------------------------
		*/

			'user_id' => array(
				'id' => 'user_id',
				'tag' => '{wp:user_id}',
				'label' => esc_html__( 'User ID', 'views-for-wpforms' ),
				'callback' => 'user_id'
			),

			/*
		|--------------------------------------------------------------------------
		| User First Name
		|--------------------------------------------------------------------------
		*/

			'first_name' => array(
				'id' => 'first_name',
				'tag' => '{wp:user_first_name}',
				'label' => esc_html__( 'User First Name', 'views-for-wpforms' ),
				'callback' => 'user_first_name'
			),

			/*
		|--------------------------------------------------------------------------
		| User Last Name
		|--------------------------------------------------------------------------
		*/

			'last_name' => array(
				'id' => 'last_name',
				'tag' => '{wp:user_last_name}',
				'label' => esc_html__( 'User Last Name', 'views-for-wpforms' ),
				'callback' => 'user_last_name'
			),

			/*
		|--------------------------------------------------------------------------
		| User Disply Name
		|--------------------------------------------------------------------------
		*/

			'display_name' => array(
				'id' => 'display_name',
				'tag' => '{wp:user_display_name}',
				'label' => esc_html__( 'User Display Name', 'views-for-wpforms' ),
				'callback' => 'user_display_name'
			),

			/*
		|--------------------------------------------------------------------------
		| User Username
		|--------------------------------------------------------------------------
		*/

			'username' => array(
				'id' => 'username',
				'tag' => '{wp:user_username}',
				'label' => esc_html__( 'User Username', 'views-for-wpforms' ),
				'callback' => 'user_username'
			),

			/*
		|--------------------------------------------------------------------------
		| User Email Address
		|--------------------------------------------------------------------------
		*/

			'user_email' => array(
				'id' => 'user_email',
				'tag' => '{wp:user_email}',
				'label' => esc_html__( 'User Email', 'views-for-wpforms' ),
				'callback' => 'user_email'
			),

			/*
		|--------------------------------------------------------------------------
		| User Website Address
		|--------------------------------------------------------------------------
		*/

			'user_url' => array(
				'id' => 'user_url',
				'tag' => '{wp:user_url}',
				'label' => esc_html__( 'User URL', 'views-for-wpforms' ),
				'callback' => 'user_url'
			),

			/*
		 |--------------------------------------------------------------------------
		 | Post Meta
		 |--------------------------------------------------------------------------
		 */

			'user_meta' => array(
				'id' => 'user_meta',
				'tag' => '{user_meta:YOUR_META_KEY}',
				'label' => esc_html__( 'User Meta', 'views-for-wpforms' ),
				'callback' => null
			),

			/*
		|--------------------------------------------------------------------------
		| Site Title
		|--------------------------------------------------------------------------
		*/

			'site_title' => array(
				'id' => 'site_title',
				'tag' => '{wp:site_title}',
				'label' => esc_html__( 'Site Title', 'views-for-wpforms' ),
				'callback' => 'site_title'
			),

			/*
		|--------------------------------------------------------------------------
		| Site URL
		|--------------------------------------------------------------------------
		*/

			'site_url' => array(
				'id' => 'site_url',
				'tag' => '{wp:site_url}',
				'label' => esc_html__( 'Site URL', 'views-for-wpforms' ),
				'callback' => 'site_url'
			),

			/*
		|--------------------------------------------------------------------------
		| Admin Email Address
		|--------------------------------------------------------------------------
		*/

			'admin_email' => array(
				'id' => 'admin_email',
				'tag' => '{wp:admin_email}',
				'label' => esc_html__( 'Admin Email', 'views-for-wpforms' ),
				'callback' => 'admin_email'
			),

		);
	}

}

function WPForms_Views_MergeTags() {
	return WPForms_Views_MergeTags::instance();
}

WPForms_Views_MergeTags();
