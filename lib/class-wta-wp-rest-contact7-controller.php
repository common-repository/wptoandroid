<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

class WTA_REST_Contact7_Aux_WTA_Controller extends WTA_REST_Aux_WTA_Controller {

	public function __construct() {
		$this->namespace = 'wp-android';
		$this->rest_base = 'contact7';
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

		register_rest_route( $this->namespace, '/' . $this->rest_base . "/submit", array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'submit' ),
				'args'            => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'args'            => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get one options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_item( $request ) {

		$id = (int) $request['id'];
		global $wpdb;

		$query = "
	        SELECT      ID, post_content, post_title
	        FROM        ".$wpdb->posts."
	        WHERE       ".$wpdb->posts.".post_content LIKE '%[contact-form-7%' AND post_status = 'publish' AND ID = ".$id."
	        ORDER BY    ".$wpdb->posts.".post_title
		";
		$query_result = $wpdb->get_results($query);
		$posts = array();
		$contactArray = array();
		if (!empty($query_result)) {
			foreach ( $query_result as $post ) {

				$post_id = explode('"', $post->post_content);
				$post_id = $post_id[1];
				$post_meta = get_post_meta($post_id);	

				foreach ($post_meta as $key => $value) {
					if ($key == '_form') {
						$form = $value[0];
						$form = preg_replace( "/\r|\n/", "", strip_tags($form));
						$array_form = explode("]", $form);

						$i=0;
						foreach ($array_form as $form_inside) {
							$contactArray[$i++] = $form_inside."]";
						}
						unset($contactArray[--$i]);
						$parsedData = $this->process($contactArray);

					} else if ($key == '_mail') {
						$mail = $value;
					} else if ($key == '_mail_2') {
						$mail2 = $value;
					}
				}
				$posts[] = array("name" => $post->post_title, "id" => $post_id, "fields" => $parsedData);
			}
		}

		$response = rest_ensure_response( $posts );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}

	/**
	 * Post sent email 
	 * 
	 * 
	 */
	public function submit( $request ) {

		//Sending email
		echo $request['id'];
		$mail_content = get_post_meta($request['id'], '_mail');
		$mail_content = $mail_content[0];

		$mail_content_body = $mail_content['body'];

		// Get data
		$data = $_REQUEST['data_json'];
		$data_array = json_decode($data);

		$string = "";
		foreach ($data_array as $key => $value) {
			$string .= "Input: ".$key. " => ".$value;
		}
		$mail_content_body .= "\n\n".$string;

		mail ( $mail_content['recipient'] , $mail_content['subject'] , $mail_content_body, $mail_content['additional_headers'] );
	}

	/**
	 * Get all options from android wordpress plugin
	 *
	 * @param WP_REST_Request $request
	 */
	public function get_items( $request ) {

		global $wpdb;

		$query = "
	        SELECT      ID, post_content, post_title
	        FROM        ".$wpdb->posts."
	        WHERE       ".$wpdb->posts.".post_content LIKE '%[contact-form-7%' AND post_status = 'publish'
	        ORDER BY    ".$wpdb->posts.".post_title
		";
		$query_result = $wpdb->get_results($query);
		$posts = array();
		$contactArray = array();

		if (!empty($query_result)) {
			foreach ( $query_result as $post ) {

				$post_id = explode('"', $post->post_content);
				$post_id = $post_id[1];
				$post_meta = get_post_meta($post_id);	

				foreach ($post_meta as $key => $value) {
					if ($key == '_form') {
						$form = $value[0];
						$form = preg_replace( "/\r|\n/", "", strip_tags($form));
						$array_form = explode("]", $form);

						$i=0;
						foreach ($array_form as $form_inside) {
							$contactArray[$i++] = $form_inside."]";
						}
						unset($contactArray[--$i]);
						$parsedData = $this->process($contactArray);

					} else if ($key == '_mail') {
						$mail = $value;
					} else if ($key == '_mail_2') {
						$mail2 = $value;
					}
				}
				$posts[] = array("name" => $post->post_title, "id" => $post_id, "fields" => $parsedData);
			}
		} 

		$response = rest_ensure_response( $posts );
		$response->header( 'X-WP-Total', (int) $total_posts );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		return $response;
	}


	function process(array $data)
	{
	    $processedData = array();
	    foreach($data as $key => $line)
	    {
	        $processedData[] = $this->processLine($line);
	    }
	    return $processedData;
	}

	/**
	 * Processes a line
	 * @param $data
	 */
	function processLine($data)
	{
	    $line = $this->getInbetweenStrings("\[","\]",$data);
	    $analizedLine = $this->analizeLine($line);
	    $linePos = strpos($data,'[');
	    $hasLabel = $linePos>0;
	    if($hasLabel)
	    {
	        $analizedLine['label'] = trim(substr($data,0,$linePos));
	    }
	    return $analizedLine;
	}

	/**
	 * Analizes a specific line.
	 * @param $line
	 */
	function analizeLine($line)
	{
	    $result = array();
	    $lineData = explode(" ", $line);
	    if(isset($lineData[0]))
	    {
	        $type = $lineData[0];
	        $isRequired = strpos($type,'*')>-1;
	        $result['required'] = $isRequired?true:false;
	        $result['type'] = $isRequired?substr($type,0,-1):$type;
	    }

	    if(isset($result['type']))
	    {
	        switch($result['type'])
	        {
	            case 'text':
	            case 'email':
	            case 'textarea':
	            case 'url':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                break;
	            case 'submit':
	                if(isset($lineData[1]))
	                {
	                    $result['value'] = $lineData[1];
	                }
	                break;
	            case 'checkbox':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                if(isset($lineData[2]) AND strcmp($lineData[2],"label_first")==0)
	                {
	                    $result['label_first'] = true;
	                }
	                break;
	            case 'radio':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                if(isset($lineData[2]) AND strcmp($lineData[2],"label_first")==0)
	                {
	                    $result['label_first'] = true;
	                }
	                break;
	            case 'quiz':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                if(isset($lineData[2]))
	                {
	                    $rest = array_slice($lineData,2);
	                    $result['queries'] = implode(' ',$rest);
	                }
	                break;
	            case 'select':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                if(isset($lineData[2]))
	                {
	                    $rest = array_slice($lineData,2);
	                    $result['elements'] = $rest;
	                }
	                break;
	            case 'date':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                if(isset($lineData[2]))
	                {
	                    $rest = array_slice($lineData,2);
	                    $result = array_merge($result,$this->getAttributes($rest));
	                    $result = array_merge($result,$this->getDefaultValue($rest));
	                }
	                break;
	            case 'tel':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                if(isset($lineData[2]))
	                {
	                    $rest = array_slice($lineData,2);
	                    $result = array_merge($result,$this->getAttributes($rest));
	                    $result = array_merge($result,$this->getDefaultValue($rest));
	                }
	                break;
	            case 'file':
	                if(isset($lineData[1]))
	                {
	                    $result['name'] = $lineData[1];
	                }
	                if(isset($lineData[2]))
	                {
	                    $rest = array_slice($lineData,2);
	                    $result = array_merge($result,$this->getAttributes($rest));
	                }
	                break;
	        }
	    }

	    return $result;
	}

	/**
	* Obtains attributes of the type name:value
	* @param $data
	* @return array
	 */
	function getDefaultValue($data)
	{
	    $resultData = array();
	    foreach($data as $attr)
	    {
	        $default= $this->getInbetweenStrings('\"','\"',$attr);
	        $isDefault = $default!=null;
	        if($isDefault)
	        {
	            $resultData['default'] = $default;
	            break;
	        }
	    }
	    return $resultData;
	}

	/**
	 * Obtains attributes of the type name:value
	 * @param $data
	 * @return array
	 */
	function getAttributes($data)
	{
	    $resultData = array();
	    foreach($data as $attr)
	    {
	        $attrArr=explode(':',$attr);
	        if(count($attrArr)>1)
	        {
	            $resultData[$attrArr[0]] = $attrArr[1];
	        }
	    }
	    return $resultData;
	}

	/**
	 * Obtains string between two other strings.
	 * @param $start
	 * @param $end
	 * @param $value
	 * @return mixed
	 */
	function getInbetweenStrings($start, $end, $value){
	    $matches = array();
	    $regex = "/$start(.*?)$end/";
	    preg_match_all($regex, $value, $matches);
	    return isset($matches[1])?(isset($matches[1][0])?$matches[1][0]:null):null;
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
