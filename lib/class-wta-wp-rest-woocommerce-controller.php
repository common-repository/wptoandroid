<?php
if ( ! defined( 'ABSPATH' ) ) exit; 
class WTA_REST_WooCommerce_Aux_WTA_Controller extends WTA_REST_Aux_WTA_Controller {

	public function __construct() {
		$this->namespace = 'wp-android';
		$this->rest_base = 'woocommerce';
		$this->post_type = 'product';
	}

	/**
	 * Register the routes for the objects of the controller.
	 */
	public function wta_register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'args'            => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/productsbycategory', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_items_by_category' ),
			'args'            => array(
				'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
			),
		));

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/product/categories', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_categories' ),
			'args'            => array(
				'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
			),
		));

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/product/add', array(
			array(
		        'methods'         => WP_REST_Server::READABLE,
		        'callback'        => array( $this, 'add_order' ),
		        'args'            => $this->get_collection_params(),
	        ),
			'schema' => array( $this, 'get_public_item_schema' ),
        ) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/product', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_item' ),
			'args'            => array(
				'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
			),
		));

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/product/search', array(
			'methods'         => WP_REST_Server::READABLE,
			'callback'        => array( $this, 'get_items_search' ),
			'args'            => array(
				'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
			),
		));
	}

	public function get_categories( $request ) {

		$args = array();

		if (isset($request['search']) && $request['search'] != '') {
			$args['search'] = $request['search'];
		}

		$args['hide_empty'] = false;
		$args['fields'] = 'ids';

		$product_categories = array();
		$terms = get_terms( 'product_cat', $args );

		foreach ( $terms as $term_id ) {
			$product_categories[] =  $this->get_product_category( $term_id, $fields );
		}

		return array('product_categories' => $product_categories); 
	}
	
	private function get_product_category($term_id, $fields = null ) {
		$term = get_term( $term_id, 'product_cat' );
		$term_id = intval( $term_id );

		// Get category display type
		$display_type = get_woocommerce_term_meta( $term_id, 'display_type' );

		// Get category image
		$image = '';
		if ( $image_id = get_woocommerce_term_meta( $term_id, 'thumbnail_id' ) ) {
			$image = wp_get_attachment_url( $image_id );
		}

		$product_category = array(
			'id'          => $term_id,
			'name'        => $term->name,
			'slug'        => $term->slug,
			'parent'      => $term->parent,
			'description' => $term->description,
			'display'     => $display_type ? $display_type : 'default',
			'image'       => $image ? esc_url( $image ) : '',
			'count'       => intval( $term->count )
		);

		return $product_category;

		//return array( 'product_category' => $product_category);
	}


	/**
	 * Get one options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_item( $request ) {

		$id = (int) $request['id'];
		$post = get_post( $id );

		$product = new WC_Product( $post->ID );
		$post->related_products = $product->get_related();
		$post->rating = $product->get_average_rating();
		$post->price = $product->price;
		$post->regular_price = $product->regular_price;
		$post->stock = $product->stock;
		$post->is_on_sale = $product->is_on_sale();
		$post->rating_count = $product->get_rating_count();
		$categories = explode(",", $product->get_categories());
		$category_string = "";
		foreach($categories as $category) {
			$category_string .= " ".trim($this->everything_in_tags($category, "a"));
		}
		$post->categories = trim($category_string);

		$images = array();
		$attachment_ids = $product->get_gallery_attachment_ids();
		foreach( $attachment_ids as $attachment_id )
		{
			$image_link = wp_get_attachment_url( $attachment_id );
			$images[] = $image_link;
		}
		$post->images = $images;
		if ( empty( $id ) || empty( $post->ID ) || $this->post_type !== $post->post_type ) {
			return new WP_Error( 'rest_post_invalid_id', __( 'Invalid post id.' ), array( 'status' => 404 ) );
		}

		$data = $this->prepare_item_for_response( $post, $request );
		$response = rest_ensure_response( $data );

		$response->link_header( 'alternate',  get_permalink( $id ), array( 'type' => 'text/html' ) );

		return $response;
	}

	function cmp_price_high($a, $b) {
		return gmp_cmp($b['price'], $a['price']);
	}
	function cmp_price_low($a, $b) {
	    return gmp_cmp($a['price'], $b['price']);
	}
	function cmp_date($a, $b) {
	    return strcmp($a['date'], $b['date']);
	}
	function cmp_rating($a, $b) {
	    return !gmp_cmp($a['rating'], $b['rating']);
	}


	public function add_order( $request ) {
		// $order_id = wc_create_order();
		// wc_add_order_item_meta(meta_key, meta_value)

		// $product = wc_get_product($product_id);
		// wc_get_order($order_id)->add_product($product, $quantity);

		$args                       = array();
		$args['products']         = $request['products'];

		global $wpdb;
		$order_data = array(
				'post_author'	=> $request['user_id'],
	    		'post_name'     => 'order-' . get_the_date('Y-m-d'),
	    		'post_type'     => 'shop_order',
	    		'post_title'    => 'Order &ndash; ' . get_the_date('Y-m-d'),
	    		'post_status'   => 'publish',
	    		'ping_status'   => 'closed',
	    		'post_excerpt'  => 'Order place by Mr.'.$request['firstname'].' '.$request['lastname'].'',
	    		'post_date'     => get_the_date('Y-m-d'),
		);
		$order_id = wp_insert_post( $order_data, true );

		update_post_meta($order_id,'_billing_first_name',$request['firstname']);
		update_post_meta($order_id,'_billing_last_name',$request['firstname']);
		update_post_meta($order_id,'_billing_email',$request['email']);
		update_post_meta( $order_id, '_order_key', uniqid( 'wc_order_' ), true );

		$products_array = explode(",", $products);

		foreach ($products_array as $productId) {
			$product = new WC_Product( $productId );
			$item_id = wc_add_order_item( $order_id, array(
		 		'order_item_name' 		=> $product->id,
		 		'order_item_type' 		=> 'line_item',
		 	) );

			if ( $item_id ) {
	            wc_add_order_item_meta( $item_id, '_qty', 1 );
	            wc_add_order_item_meta( $item_id, '_tax_class', $product->get_tax_class() );
	            wc_add_order_item_meta( $item_id, '_product_id', $product->id );
				wc_add_order_item_meta( $item_id, '_variation_id', '' );
	            wc_add_order_item_meta( $item_id, '_line_subtotal', 0 );
	            wc_add_order_item_meta( $item_id, '_line_total', 0 );
				wc_add_order_item_meta( $item_id, '_line_tax', 0 );
				wc_add_order_item_meta( $item_id, '_line_subtotal_tax', 0 );
	        }
		}
		
		return json_encode(array('order' => $order_id));
	}

	/**
	 * Get all options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_items( $request ) {
		
		$args                         = array();
		$args['author__in']           = $request['author'];
		$args['author__not_in']       = $request['author_exclude'];
		$args['menu_order']           = $request['menu_order'];
		$args['offset']               = $request['offset'];
		$args['order']                = $request['order'];
		$args['orderby']              = $request['orderby'];
		$args['paged']                = $request['page'];
		$args['post__in']             = $request['include'];
		$args['post__not_in']         = $request['exclude'];
		$args['posts_per_page']       = 20;
		$args['name']                 = $request['slug'];
		$args['post_parent__in']      = $request['parent'];
		$args['post_parent__not_in']  = $request['parent_exclude'];
		$args['post_status']          = $request['status'];
		$args['s']                    = $request['search'];

		if ( is_array( $request['filter'] ) ) {
			$args = array_merge( $args, $request['filter'] );
			unset( $args['filter'] );
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = 'product';

		$args = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args = $this->prepare_items_query( $args, $request );

		$posts_query = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		$posts = array();
		foreach ( $query_result as $post ) {

			$product = new WC_Product( $post->ID );

			$related_products_array = array();
			foreach ($product->get_related() as $related_product_item) {
				$post_item = get_post($related_product_item);
				$product_item = new WC_Product( $post->ID );
				$post_item->price = $product_item->price;
				$post_item->regular_price = $product_item->regular_price;
				$post_item->is_on_sale = $product_item->is_on_sale();

				$args = array( 'post_type' => 'attachment', 'post_parent' => $post_item->ID );
				$attachments = get_posts( $args );

				if ( $attachments && count($attachments) > 0) {
					$post_item->imageurl = $attachments[0]->guid;
				}
				$related_products_array[] = $post_item;
			}

			$post->related_products = $related_products_array;
			$post->rating = $product->get_average_rating();
			$post->price = $product->price;
			$post->regular_price = $product->regular_price;
			$post->stock = $product->stock;
			$post->is_on_sale = $product->is_on_sale();
			$post->rating_count = $product->get_rating_count();
			$categories = explode(",", $product->get_categories());
			$category_string = "";
			foreach($categories as $category) {
				$category_string .= " ".trim($this->everything_in_tags($category, "a"));
			}
			$post->categories = trim($category_string);

			$args = array( 'post_type' => 'attachment', 'post_parent' => $post->ID );
			$attachments = get_posts( $args );

			if ( $attachments && count($attachments) > 0) {
				$post->imageurl = $attachments[0]->guid;
			}
			$images = array();
			$attachment_ids = $product->get_gallery_attachment_ids();
			foreach( $attachment_ids as $attachment_id )
			{
				$image_link = wp_get_attachment_url( $attachment_id );
				$images[] = $image_link;
			}
			$post->images = $images;

			$data = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$page = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count
			unset( $query_args['paged'] );
			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $query_args['posts_per_page'] );

		if ($request['sort'] != '') {
			if ($request['sort'] == "price_high") {
				usort($posts, array($this, "cmp_price_high"));
			} else if ($request['sort'] == "date") {
				usort($posts, array($this, "cmp_date"));
			} else if ($request['sort'] == "rating") {
				usort($posts, array($this, "cmp_rating"));
			} else if ($request['sort'] == "price_low") {
				usort($posts, array($this, "cmp_price_low"));
			}
		}

		$response = rest_ensure_response( $posts );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		if ( ! empty( $request_params['filter'] ) ) {
			// Normalize the pagination params.
			unset( $request_params['filter']['posts_per_page'] );
			unset( $request_params['filter']['paged'] );
		}
		$base = add_query_arg( $request_params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}


	/**
	 * Get all options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_items_by_category( $request ) {
		
		$args                         = array();
		$args['author__in']           = $request['author'];
		$args['author__not_in']       = $request['author_exclude'];
		$args['menu_order']           = $request['menu_order'];
		$args['offset']               = $request['offset'];
		$args['order']                = $request['order'];
		$args['orderby']              = $request['orderby'];
		$args['paged']                = $request['page'];
		$args['post__in']             = $request['include'];
		$args['post__not_in']         = $request['exclude'];
		$args['posts_per_page']       = 20;
		$args['name']                 = $request['slug'];
		$args['post_parent__in']      = $request['parent'];
		$args['post_parent__not_in']  = $request['parent_exclude'];
		$args['post_status']          = $request['status'];
		$args['s']                    = $request['search'];
		$args['product_cat']		  	  = $request['id'];

		if ( is_array( $request['filter'] ) ) {
			$args = array_merge( $args, $request['filter'] );
			unset( $args['filter'] );
		}

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = 'product';

		$args = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		$query_args = $this->prepare_items_query( $args, $request );

		$posts_query = new WP_Query();
		$query_result = $posts_query->query( $query_args );

		$posts = array();
		foreach ( $query_result as $post ) {

			$product = new WC_Product( $post->ID );

			$related_products_array = array();
			foreach ($product->get_related() as $related_product_item) {
				$post_item = get_post($related_product_item);
				$product_item = new WC_Product( $post->ID );
				$post_item->price = $product_item->price;
				$post_item->regular_price = $product_item->regular_price;
				$post_item->is_on_sale = $product_item->is_on_sale();

				$args = array( 'post_type' => 'attachment', 'post_parent' => $post_item->ID );
				$attachments = get_posts( $args );

				if ( $attachments && count($attachments) > 0) {
					$post_item->imageurl = $attachments[0]->guid;
				}
				$related_products_array[] = $post_item;
			}

			$post->related_products = $related_products_array;
			$post->rating = $product->get_average_rating();
			$post->price = $product->price;
			$post->regular_price = $product->regular_price;
			$post->stock = $product->stock;
			$post->is_on_sale = $product->is_on_sale();
			$post->rating_count = $product->get_rating_count();
			$categories = explode(",", $product->get_categories());
			$category_string = "";
			foreach($categories as $category) {
				$category_string .= " ".trim($this->everything_in_tags($category, "a"));
			}
			$post->categories = trim($category_string);

			$args = array( 'post_type' => 'attachment', 'post_parent' => $post->ID );
			$attachments = get_posts( $args );

			if ( $attachments && count($attachments) > 0) {
				$post->imageurl = $attachments[0]->guid;
			}
			$images = array();
			$attachment_ids = $product->get_gallery_attachment_ids();
			foreach( $attachment_ids as $attachment_id )
			{
				$image_link = wp_get_attachment_url( $attachment_id );
				$images[] = $image_link;
			}
			$post->images = $images;

			$data = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$page = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count
			unset( $query_args['paged'] );
			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $query_args['posts_per_page'] );

		if ($request['sort'] != '') {
			if ($request['sort'] == "price_high") {
				usort($posts, array($this, "cmp_price_high"));
			} else if ($request['sort'] == "date") {
				usort($posts, array($this, "cmp_date"));
			} else if ($request['sort'] == "rating") {
				usort($posts, array($this, "cmp_rating"));
			} else if ($request['sort'] == "price_low") {
				usort($posts, array($this, "cmp_price_low"));
			}
		}

		$response = rest_ensure_response( $posts );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		if ( ! empty( $request_params['filter'] ) ) {
			// Normalize the pagination params.
			unset( $request_params['filter']['posts_per_page'] );
			unset( $request_params['filter']['paged'] );
		}
		$base = add_query_arg( $request_params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}


	/**
	 * Get all options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_items_search( $request ) {
		
		$args                    = array();
		$args['s']               = $request['search'];
		$args['post_type']		= 'product';
		$args['post_status']	= 'publish';
		$args['search_columns'] = 'post_title';

		// Force the post_type argument, since it's not a user input variable.
		$args['post_type'] = 'product';

		// $args = apply_filters( "rest_{$this->post_type}_query", $args, $request );
		// $query_args = $this->prepare_items_query( $args, $request );

		$posts_query = new WP_Query();
		$query_result = $posts_query->query( $args );

		$posts = array();
		foreach ( $query_result as $post ) {

			$product = new WC_Product( $post->ID );

			$related_products_array = array();
			foreach ($product->get_related() as $related_product_item) {
				$post_item = get_post($related_product_item);
				$product_item = new WC_Product( $post->ID );
				$post_item->price = $product_item->price;
				$post_item->regular_price = $product_item->regular_price;
				$post_item->is_on_sale = $product_item->is_on_sale();

				$args = array( 'post_type' => 'attachment', 'post_parent' => $post_item->ID );
				$attachments = get_posts( $args );

				if ( $attachments && count($attachments) > 0) {
					$post_item->imageurl = $attachments[0]->guid;
				}
				$related_products_array[] = $post_item;
			}

			$post->related_products = $related_products_array;
			$post->rating = $product->get_average_rating();
			$post->price = $product->price;
			$post->regular_price = $product->regular_price;
			$post->stock = $product->stock;
			$post->is_on_sale = $product->is_on_sale();
			$post->rating_count = $product->get_rating_count();
			$categories = explode(",", $product->get_categories());
			$category_string = "";
			foreach($categories as $category) {
				$category_string .= " ".trim($this->everything_in_tags($category, "a"));
			}
			$post->categories = trim($category_string);

			$args = array( 'post_type' => 'attachment', 'post_parent' => $post->ID );
			$attachments = get_posts( $args );

			if ( $attachments && count($attachments) > 0) {
				$post->imageurl = $attachments[0]->guid;
			}
			$images = array();
			$attachment_ids = $product->get_gallery_attachment_ids();
			foreach( $attachment_ids as $attachment_id )
			{
				$image_link = wp_get_attachment_url( $attachment_id );
				$images[] = $image_link;
			}
			$post->images = $images;

			$data = $this->prepare_item_for_response( $post, $request );
			$posts[] = $this->prepare_response_for_collection( $data );
		}

		$page = (int) $query_args['paged'];
		$total_posts = $posts_query->found_posts;

		if ( $total_posts < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count
			unset( $query_args['paged'] );
			$count_query = new WP_Query();
			$count_query->query( $query_args );
			$total_posts = $count_query->found_posts;
		}

		$max_pages = ceil( $total_posts / (int) $query_args['posts_per_page'] );

		if ($request['sort'] != '') {
			if ($request['sort'] == "price_high") {
				usort($posts, array($this, "cmp_price_high"));
			} else if ($request['sort'] == "date") {
				usort($posts, array($this, "cmp_date"));
			} else if ($request['sort'] == "rating") {
				usort($posts, array($this, "cmp_rating"));
			} else if ($request['sort'] == "price_low") {
				usort($posts, array($this, "cmp_price_low"));
			}
		}

		$response = rest_ensure_response( $posts );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		if ( ! empty( $request_params['filter'] ) ) {
			// Normalize the pagination params.
			unset( $request_params['filter']['posts_per_page'] );
			unset( $request_params['filter']['paged'] );
		}
		$base = add_query_arg( $request_params, rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;
			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}
			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );
			$response->link_header( 'next', $next_link );
		}

		return $response;
	}


	/**
	 * Prepare links for the request.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $post ) {
		$base = sprintf( '/%s/%s', $this->namespace, $this->rest_base );

		// Entity meta
		$links = array(
			'self' => array(
				'href'   => rest_url( trailingslashit( $base ) . $post->ID ),
			),
			'collection' => array(
				'href'   => rest_url( $base ),
			),
			'about'      => array(
				'href'   => rest_url( '/wp/v2/types/' . $this->post_type ),
			),
		);

		if ( ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'author' ) )
			&& ! empty( $post->post_author ) ) {
			$links['author'] = array(
				'href'       => rest_url( '/wp/v2/users/' . $post->post_author ),
				'embeddable' => true,
			);
		};

		if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'comments' ) ) {
			$replies_url = rest_url( '/wp/v2/comments' );
			$replies_url = add_query_arg( 'post', $post->ID, $replies_url );
			$links['replies'] = array(
				'href'         => $replies_url,
				'embeddable'   => true,
			);
		}

		if ( in_array( $post->post_type, array( 'post', 'page' ) ) || post_type_supports( $post->post_type, 'revisions' ) ) {
			$links['version-history'] = array(
				'href' => rest_url( trailingslashit( $base ) . $post->ID . '/revisions' ),
			);
		}
		$post_type_obj = get_post_type_object( $post->post_type );
		if ( $post_type_obj->hierarchical && ! empty( $post->post_parent ) ) {
			$links['up'] = array(
				'href'       => rest_url( trailingslashit( $base ) . (int) $post->post_parent ),
				'embeddable' => true,
			);
		}

		// If we have a featured media, add that.
		if ( $featured_media = get_post_thumbnail_id( $post->ID ) ) {
			$image_url = rest_url( 'wp/v2/media/' . $featured_media );
			$links['https://api.w.org/featuredmedia'] = array(
				'href'       => $image_url,
				'embeddable' => true,
			);
		}
		if ( ! in_array( $post->post_type, array( 'attachment', 'nav_menu_item', 'revision' ) ) ) {
			$attachments_url = rest_url( 'wp/v2/media' );
			$attachments_url = add_query_arg( 'parent', $post->ID, $attachments_url );
			$links['https://api.w.org/attachment'] = array(
				'href'       => $attachments_url,
			);
		}

		$taxonomies = get_object_taxonomies( $post->post_type );
		if ( ! empty( $taxonomies ) ) {
			$links['https://api.w.org/term'] = array();

			foreach ( $taxonomies as $tax ) {
				$taxonomy_obj = get_taxonomy( $tax );
				// Skip taxonomies that are not public.
				if ( empty( $taxonomy_obj->show_in_rest ) ) {
					continue;
				}

				$tax_base = ! empty( $taxonomy_obj->rest_base ) ? $taxonomy_obj->rest_base : $tax;
				$terms_url = add_query_arg(
					'post',
					$post->ID,
					rest_url( 'wp/v2/' . $tax_base )
				);

				$links['https://api.w.org/term'][] = array(
					'href'       => $terms_url,
					'taxonomy'   => $tax,
					'embeddable' => true,
				);
			}
		}

		if ( post_type_supports( $post->post_type, 'custom-fields' ) ) {
			$links['https://api.w.org/meta'] = array(
				'href' => rest_url( trailingslashit( $base ) . $post->ID . '/meta' ),
				'embeddable' => true,
			);
		}

		return $links;
	}

	/**
	 * Check the post_date_gmt or modified_gmt and prepare any post or
	 * modified date for single post output.
	 *
	 * @param string       $date_gmt
	 * @param string|null  $date
	 * @return string|null ISO8601/RFC3339 formatted datetime.
	 */
	protected function prepare_date_response( $date_gmt, $date = null ) {
		// Use the date if passed.
		if ( isset( $date ) ) {
			return mysql_to_rfc3339( $date );
		}

		// Return null if $date_gmt is empty/zeros.
		if ( '0000-00-00 00:00:00' === $date_gmt ) {
			return null;
		}

		// Return the formatted datetime.
		return mysql_to_rfc3339( $date_gmt );
	}

	protected function everything_in_tags($string, $tagname) {
	    $pattern = "#<\s*?$tagname\b[^>]*>(.*?)</$tagname\b[^>]*>#s";
	    preg_match($pattern, $string, $matches);
	    return $matches[1];
	}

	/**
	 * Prepare a single post output for response.
	 *
	 * @param WP_Post $post Post object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response $data
	 */
	public function prepare_item_for_response( $post, $request ) {
		$GLOBALS['post'] = $post;
		setup_postdata( $post );

		// Base fields for every post.
		$data = array(
			'id'           => $post->ID,
			'date'         => $this->prepare_date_response( $post->post_date_gmt, $post->post_date ),
			'date_gmt'     => $this->prepare_date_response( $post->post_date_gmt ),
			'guid'         => array(
				/** This filter is documented in wp-includes/post-template.php */
				'rendered' => apply_filters( 'get_the_guid', $post->guid ),
				'raw'      => $post->guid,
			),
			'modified'     => $this->prepare_date_response( $post->post_modified_gmt, $post->post_modified ),
			'modified_gmt' => $this->prepare_date_response( $post->post_modified_gmt ),
			'password'     => $post->post_password,
			'slug'         => $post->post_name,
			'status'       => $post->post_status,
			'title'		   => $post->post_title,
			'content'	   => $post->post_content,
			'imageurl'     => $post->imageurl,
			'is_on_sale'   => $post->is_on_sale,
			'regular_price'=> $post->regular_price,
			'rating_count' => $post->rating_count,
			'categories'   => $post->categories,
			'price'        => $post->price,
			'images'	   => $post->images,
			'type'         => $post->post_type,
			'stock'		   => $post->stock,
			'related_products' => $post->related_products,
			'rating'		=> $post->rating,
			'link'         => get_permalink( $post->ID ),
		);

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['title'] ) ) {
			$data['title'] = array(
				'raw'      => $post->post_title,
				'rendered' => get_the_title( $post->ID ),
			);
		}

		if ( ! empty( $schema['properties']['content'] ) ) {

			if ( ! empty( $post->post_password ) ) {
				$this->prepare_password_response( $post->post_password );
			}

			$data['content'] = array(
				'raw'      => $post->post_content,
				/** This filter is documented in wp-includes/post-template.php */
				'rendered' => apply_filters( 'the_content', $post->post_content ),
			);

			if ( ! empty( $post->post_password ) ) {
				$_COOKIE[ 'wp-postpass_' . COOKIEHASH ] = '';
			}
		}

		if ( ! empty( $schema['properties']['excerpt'] ) ) {
			$data['excerpt'] = array(
				'raw'      => $post->post_excerpt,
				'rendered' => $this->prepare_excerpt_response( $post->post_excerpt ),
			);
		}
		
		if ( ! empty( $schema['properties']['author'] ) ) {
			$data['author'] = (int) $post->post_author;
			$data['author_name'] = (string) get_the_author_meta('nicename', $data['author']);	
			$data['author_avatar'] = get_avatar_url($post->post_author);
		}

		if ( ! empty( $schema['properties']['featured_media'] ) ) {
			$data['featured_media'] = (int) get_post_thumbnail_id( $post->ID );
			$imageUrl = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "large" );
			if (isset($imageUrl)) {
		       $var = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), "large" );
                $data['main_media'] = $var[0];
			} else {
				$data['main_media'] = '';
			}
		}
		$data['comments_count'] = wp_count_comments($post->ID);
		
		if ( ! empty( $schema['properties']['parent'] ) ) {
			$data['parent'] = (int) $post->post_parent;
		}

		if ( ! empty( $schema['properties']['imageurl'] ) ) {
			$data['imageurl'] = (int) $post->imageurl;
		}

		if ( ! empty( $schema['properties']['menu_order'] ) ) {
			$data['menu_order'] = (int) $post->menu_order;
		}

		if ( ! empty( $schema['properties']['comment_status'] ) ) {
			$data['comment_status'] = $post->comment_status;
		}

		if ( ! empty( $schema['properties']['ping_status'] ) ) {
			$data['ping_status'] = $post->ping_status;
		}

		if ( ! empty( $schema['properties']['sticky'] ) ) {
			$data['sticky'] = is_sticky( $post->ID );
		}

		if ( ! empty( $schema['properties']['template'] ) ) {
			if ( $template = get_page_template_slug( $post->ID ) ) {
				$data['template'] = $template;
			} else {
				$data['template'] = '';
			}
		}

		if ( ! empty( $schema['properties']['format'] ) ) {
			$data['format'] = get_post_format( $post->ID );
			// Fill in blank post format.
			if ( empty( $data['format'] ) ) {
				$data['format'] = 'standard';
			}
		}

		$taxonomies = wp_list_filter( get_object_taxonomies( $this->post_type, 'objects' ), array( 'show_in_rest' => true ) );
		foreach ( $taxonomies as $taxonomy ) {
			$base = ! empty( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name;
			$terms = get_the_terms( $post, $taxonomy->name );
			$data[ $base ] = $terms ? wp_list_pluck( $terms, 'term_id' ) : array();
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data = $this->add_additional_fields_to_object( $data, $request );
		$data = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		$response->add_links( $this->prepare_links( $post ) );

		/**
		 * Filter the post data for a response.
		 *
		 * The dynamic portion of the hook name, $this->post_type, refers to post_type of the post being
		 * prepared for the response.
		 *
		 * @param WP_REST_Response   $response   The response object.
		 * @param WP_Post            $post       Post object.
		 * @param WP_REST_Request    $request    Request object.
		 */
		return apply_filters( "rest_prepare_{$this->post_type}", $response, $post, $request );
	}

	/**
	 * Determine the allowed query_vars for a get_items() response and
	 * prepare for WP_Query.
	 *
	 * @param array           $prepared_args
	 * @param WP_REST_Request $request
	 * @return array          $query_args
	 */
	protected function prepare_items_query( $prepared_args = array(), $request = null ) {

		$valid_vars = array_flip( $this->get_allowed_query_vars() );
		$query_args = array();
		foreach ( $valid_vars as $var => $index ) {
			if ( isset( $prepared_args[ $var ] ) ) {
				$query_args[ $var ] = apply_filters( "rest_query_var-{$var}", $prepared_args[ $var ] );
			}
		}

		if ( 'post' !== $this->post_type || ! isset( $query_args['ignore_sticky_posts'] ) ) {
			$query_args['ignore_sticky_posts'] = true;
		}

		if ( 'include' === $query_args['orderby'] ) {
			$query_args['orderby'] = 'post__in';
		}

		return $query_args;
	}

		/**
	 * Check if a given post type should be viewed or managed.
	 *
	 * @param object|string $post_type
	 * @return boolean Is post type allowed?
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		if ( ! is_object( $post_type ) ) {
			$post_type = get_post_type_object( $post_type );
		}

		if ( ! empty( $post_type ) && ! empty( $post_type->show_in_rest ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get all the WP Query vars that are allowed for the API request.
	 *
	 * @return array
	 */
	protected function get_allowed_query_vars() {
		global $wp;

		$valid_vars = apply_filters( 'query_vars', $wp->public_query_vars );

		$post_type_obj = get_post_type_object( $this->post_type );
		if ( current_user_can( $post_type_obj->cap->edit_posts ) ) {
			$private = apply_filters( 'rest_private_query_vars', $wp->private_query_vars );
			$valid_vars = array_merge( $valid_vars, $private );
		}
		// Define our own in addition to WP's normal vars.
		$rest_valid = array(
			'author__in',
			'author__not_in',
			'ignore_sticky_posts',
			'menu_order',
			'offset',
			'post__in',
			'post__not_in',
			'post_parent',
			'post_parent__in',
			'post_parent__not_in',
			'posts_per_page',
		);
		$valid_vars = array_merge( $valid_vars, $rest_valid );
		$valid_vars = apply_filters( 'rest_query_vars', $valid_vars );

		return $valid_vars;
	}


	/**
	 * Get the query params for collections
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params = parent::get_collection_params();

		$params['context']['default'] = 'view';

		if ( post_type_supports( $this->post_type, 'author' ) ) {
			$params['author'] = array(
				'description'         => __( 'Limit result set to posts assigned to specific authors.' ),
				'type'                => 'array',
				'default'             => array(),
				'sanitize_callback'   => 'wp_parse_id_list',
				'validate_callback'   => 'rest_validate_request_arg',
			);
			$params['author_exclude'] = array(
				'description'         => __( 'Ensure result set excludes posts assigned to specific authors.' ),
				'type'                => 'array',
				'default'             => array(),
				'sanitize_callback'   => 'wp_parse_id_list',
				'validate_callback'   => 'rest_validate_request_arg',
			);
		}
		$params['exclude'] = array(
			'description'        => __( 'Ensure result set excludes specific ids.' ),
			'type'               => 'array',
			'default'            => array(),
			'sanitize_callback'  => 'wp_parse_id_list',
		);
		$params['include'] = array(
			'description'        => __( 'Limit result set to specific ids.' ),
			'type'               => 'array',
			'default'            => array(),
			'sanitize_callback'  => 'wp_parse_id_list',
		);
		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$params['menu_order'] = array(
				'description'        => __( 'Limit result set to resources with a specific menu_order value.' ),
				'type'               => 'integer',
				'sanitize_callback'  => 'absint',
				'validate_callback'  => 'rest_validate_request_arg',
			);
		}
		$params['offset'] = array(
			'description'        => __( 'Offset the result set by a specific number of items.' ),
			'type'               => 'integer',
			'sanitize_callback'  => 'absint',
			'validate_callback'  => 'rest_validate_request_arg',
		);
		$params['order'] = array(
			'description'        => __( 'Order sort attribute ascending or descending.' ),
			'type'               => 'string',
			'default'            => 'desc',
			'enum'               => array( 'asc', 'desc' ),
			'validate_callback'  => 'rest_validate_request_arg',
		);
		$params['orderby'] = array(
			'description'        => __( 'Sort collection by object attribute.' ),
			'type'               => 'string',
			'default'            => 'date',
			'enum'               => array(
				'date',
				'id',
				'include',
				'title',
				'slug',
			),
			'validate_callback'  => 'rest_validate_request_arg',
		);
		if ( 'page' === $this->post_type || post_type_supports( $this->post_type, 'page-attributes' ) ) {
			$params['orderby']['enum'][] = 'menu_order';
		}

		// $post_type_obj = get_post_type_object( $this->post_type );
		// if ( $post_type_obj->hierarchical || 'attachment' === $this->post_type ) {
		// 	$params['parent'] = array(
		// 		'description'       => _( 'Limit result set to those of particular parent ids.' ),
		// 		'type'              => 'array',
		// 		'sanitize_callback' => 'wp_parse_id_list',
		// 		'default'           => array(),
		// 	);
		// 	$params['parent_exclude'] = array(
		// 		'description'       => _( 'Limit result set to all items except those of a particular parent id.' ),
		// 		'type'              => 'array',
		// 		'sanitize_callback' => 'wp_parse_id_list',
		// 		'default'           => array(),
		// 	);
		// }

		$params['slug'] = array(
			'description'       => __( 'Limit result set to posts with a specific slug.' ),
			'type'              => 'string',
			'validate_callback' => 'rest_validate_request_arg',
		);
	
		$params['filter'] = array(
			'description'       => __( 'Use WP Query arguments to modify the response; private query vars require appropriate authorization.' ),
		);
		return $params;
	}

}
