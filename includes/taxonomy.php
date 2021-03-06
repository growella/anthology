<?php
/**
 * Register the 'anthology' taxonomy, which is used to help relate posts in the same series.
 *
 * @package Growella\Anthology
 * @author  Growella
 */

namespace Growella\Anthology\Taxonomy;

use Growella\Anthology\Core as Core;

/**
 * Register the 'anthology' taxonomy.
 */
function register_series_taxonomy() {
	register_taxonomy( 'anthology-series', 'post', array(
		'label'             => _x( 'Series', 'taxonomy label', 'anthology' ),
		'labels'            => array(
			'singular_name' => _x( 'Series', 'taxonomy label', 'anthology' ),
			'all_items'     => _x( 'All Series', 'taxonomy label', 'anthology' ),
			'edit_item'     => _x( 'Edit Series', 'taxonomy label', 'anthology' ),
			'view_item'     => _x( 'View Series', 'taxonomy label', 'anthology' ),
			'update_item'   => _x( 'Update Series', 'taxonomy label', 'anthology' ),
			'add_new_item'  => _x( 'Add New Series', 'taxonomy label', 'anthology' ),
			'new_item_name' => _x( 'New Series Name', 'taxonomy label', 'anthology' ),
			'search_items'  => _x( 'Search Series', 'taxonomy label', 'anthology' ),
			'popular_items' => _x( 'Popular Series', 'taxonomy label', 'anthology' ),
			'not_found'     => _x( 'No series found.', 'taxonomy label', 'anthology' ),
		),
		'public'            => false,
		'show_ui'           => true,
		'show_in_nav_menus' => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'description'       => __( 'A collection of post in a series', 'anthology' ),
		'hierarchical'      => false,
		'sort'              => false,
	) );
}
add_action( 'init', __NAMESPACE__ . '\register_series_taxonomy' );

/**
 * Add a list of posts in this series to the term edit screen.
 *
 * @param WP_Term $term The current term object.
 */
function render_series_ordering( $term ) {
	$posts = get_series_query( $term->slug );
?>

	<table class="form-table">
		<tbody>
			<tr class="form-field anthology-series-order">
				<th scope="row">
					<label for="anthology-series-order"><?php echo esc_html_x( 'Order', 'anthology series order', 'anthology' ); ?></label>
				</th>
				<td>
					<?php if ( ! $posts->have_posts() ) : ?>

						<p class="error"><?php esc_html_e( 'No posts have been assigned to this series, so there\'s nothing to order!', 'anthology' ); ?></p>

					<?php else : ?>

						<table class="wp-list-table widefat striped anthology-series-order-list">
							<thead>
								<tr>
									<th scope="col"><?php echo esc_html_x( 'Title', 'anthology table heading', 'anthology' ); ?></th>
									<th scope="col"><?php echo esc_html_x( 'Date', 'anthology table heading', 'anthology' ); ?></th>
								</tr>
							</thead>
							<tbody>
								<?php while ( $posts->have_posts() ) : $posts->the_post(); ?>

									<tr>
										<td class="column-title column-primary">
											<strong><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></strong>
											<input name="anthology-series-order[]" value="<?php echo esc_attr( get_the_ID() ); ?>" type="hidden" />
										</td>
										<td class="column-date"><?php the_date(); ?></td>
									</tr>

								<?php endwhile; ?>
							</tbody>
						</table>

						<?php wp_nonce_field( 'anthology-series-ordering', 'anthology-nonce' ); ?>

					<?php endif; ?>
				</td>
			</tr>
		</tbody>
	</table>

<?php
	wp_reset_postdata();
}
add_action( 'anthology-series_edit_form_fields', __NAMESPACE__ . '\render_series_ordering' );

/**
 * Save the ordering when a term is saved.
 *
 * @param int $term_id The ID of the term being saved.
 */
function save_series_order( $term_id ) {
	if (
		! isset( $_POST['anthology-nonce'], $_POST['anthology-series-order'] )
		|| ( defined( 'DOING_AJAX' ) && DOING_AJAX )
		|| ! wp_verify_nonce( $_POST['anthology-nonce'], 'anthology-series-ordering' )
	) {
		return;
	}

	$post_ids = array_map( 'absint', (array) $_POST['anthology-series-order'] );
	update_term_meta( $term_id, 'anthology-order', $post_ids );
}
add_action( 'edited_anthology-series', __NAMESPACE__ . '\save_series_order' );

/**
 * Given a series of arguments, construct a WP_Query object for the given series.
 *
 * @param string $series The series to construct a query for.
 * @param array  $args {
 *   Arguments used to construct the WP_Query object.
 *
 *   @type int $limit The maximum number of results to show. Default is 100.
 * }
 * @return WP_Query A WP_Query object containing the ordered posts in the given series.
 */
function get_series_query( $series, $args = array() ) {
	$term  = get_term_by( 'slug', $series, 'anthology-series' );
	$tax   = get_taxonomy( 'anthology-series' );
	$args  = wp_parse_args( $args, array(
		'limit' => 100,
	) );
	$order = $term ? get_term_meta( $term->term_id, 'anthology-order', true ) : array();
	$query = new \WP_Query( array(
		'post_type'              => $tax->object_type,
		'posts_per_page'         => (int) $args['limit'],
		'update_term_meta_cache' => true,
		'update_post_meta_cache' => false,
		'no_found_rows'          => true,
		'tax_query'              => array(
			array(
				'taxonomy' => 'anthology-series',
				'field'    => 'term_id',
				'terms'    => $term ? $term->term_id : 0,
			),
		),
	) );

	return Core\sort_query_by_series_order( $query, (array) $order );
}

/**
 * Determine the default series to use for the given post.
 *
 * @param int $post_id The post ID.
 * @return WP_Term|boolean Either the WP_Term object representing the first series applied to the
 *                         given post or a boolean FALSE if there are no series for the post.
 */
function get_default_series_for_post( $post_id ) {
	$terms = get_the_terms( $post_id, 'anthology-series' );

	if ( is_wp_error( $terms ) ) {
		return false;
	}

	return is_array( $terms ) && $terms ? array_shift( $terms ) : false;
}
