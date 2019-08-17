<?php if ( ! defined( 'ABSPATH' ) ) {
	die( 'No direct access' );
}

// Register Widget
add_action( 'widgets_init', 'coauthors_register_widget' );
function coauthors_register_widget() {
	register_widget( 'coauthorsOtherPosts' );
}

class coauthorsOtherPosts extends WP_Widget {

	// Constructor
	function coauthorsOtherPosts() {
		parent::__construct(
			'coauthors_other_posts',
			'Co Authors Other Posts',
			array( 'description' => __( 'Widget to display other posts by co-authors of a post on single post page.', 'co-authors-plus' ) )
		);
	}

	// Widget backend options.
	function form( $instance ) {
		
		// Check if values present, set default otherwise.
		$title				= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number_of_posts	= isset( $instance['number_of_posts'] ) ? absint( $instance['number_of_posts'] ) : 5;
		$show_thumbnail		= isset( $instance['show_thumbnail'] ) ? (bool) $instance['show_thumbnail'] : false;
		$show_date			= isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
		?>
		
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title', 'co-authors-plus' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'number_of_posts' ); ?>"><?php _e( 'Number of posts to show:', 'co-authors-plus' ); ?></label>
            <input id="<?php echo $this->get_field_id( 'number_of_posts' ); ?>" name="<?php echo $this->get_field_name( 'number_of_posts' ); ?>" type="text" size="2" value="<?php echo $number_of_posts; ?>"/>
        </p>
        <p>
            <input class="checkbox show_thumbnail" id="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnail' ); ?>" type="checkbox" <?php checked( $show_thumbnail ); ?> />
            <label for="<?php echo $this->get_field_id( 'show_thumbnail' ); ?>"><?php _e( 'Show Thumbnail', 'co-authors-plus' ); ?></label>
        </p>
        <p>
            <input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>"/>
            <label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date', 'co-authors-plus' ); ?></label>
        </p>
		
	<?php
	}

	// Update widget options
	function update( $new_instance, $old_instance ) {

		$old_instance['title']				= isset( $new_instance['title'] ) ? $new_instance['title'] : '';
		$old_instance['number_of_posts']	= isset( $new_instance['number_of_posts'] ) ? absint( $new_instance['number_of_posts'] ) : '';
		$old_instance['show_thumbnail']		= isset( $new_instance['show_thumbnail'] ) ? (bool) $new_instance['show_thumbnail'] : false;
		$old_instance['show_date']			= isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;

		return $old_instance;

	}

	// Frontend
	function widget( $args, $instance ) {

		if ( is_single() ) {
			
			global $post;
			extract($args, EXTR_SKIP);
			
			// Get saved widget options from database.
			$widget_id			= str_replace( 'coauthors_other_posts-', '', $args['widget_id'] );
			$widget_options		= get_option( $this->option_name );
			$instance			= $widget_options[ $widget_id ];
			$title				= ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
			$number_of_posts	= ( ! empty( $instance['number_of_posts'] ) ) ? absint( $instance['number_of_posts'] ) : 5;
			$show_thumbnail		= isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : false;
			$show_date			= isset( $instance['show_date'] ) ? $instance['show_date'] : false;

			$coauthors = get_coauthors( $post->ID );

			foreach( $coauthors as $coauthor ) {
				$author_ids[] = $coauthor->ID;
			}

			$author_ids = implode(",", $author_ids);

			$coauthors_posts = get_posts( array(
				'author'         => $author_ids,
				'post__not_in'   => array( $post->ID ),
				'posts_per_page' => $number_of_posts
			) );
			
			
			echo $before_widget;
				
			echo $before_title . $title . $after_title;
			
				
			if ( count( $coauthors_posts ) > 0 ) {
			?>
				
                <ul class="coauthor-posts">
					<?php
					foreach ( $coauthors_posts as $coauthors_post ) {
						$permalink = esc_url( get_permalink( $coauthors_post->ID ) );
						?>
                        <li style="list-style:none;width:100%;float:left;margin-top:10px;">
							<?php if ( $show_thumbnail && has_post_thumbnail( $coauthors_post->ID ) ) { ?>
                                <div class="coauthor-post-thumbnail" style="width:50px;height:50px;float:left;margin-right:10px;">
									<a href="<?php echo $permalink; ?>">
										<?php
										echo get_the_post_thumbnail( $coauthors_post->ID, array( 50,50 ) ); ?>
									</a>
                                </div>
							<?php
							} ?>
                            <div class="coauthor-post-details">
                                <a href="<?php echo $permalink; ?>">
									<?php echo $coauthors_post->post_title; ?>
                                </a>
								<?php if ( $show_date ) { ?>
                                    <br/><small><?php echo date( get_option( 'date_format' ), strtotime( $coauthors_post->post_date ) ); ?></small>
								<?php
								} ?>
                            </div>
                        </li>
					<?php
					} ?>
                </ul>
				
			<?php
			}
			
			else {
				_e( 'No other posts from the same author(s)', 'co-authors-plus' );
			}
			
			echo $after_widget;
		}
	} // End of function widget()
} // End of the class coauthorsOtherPosts
?>