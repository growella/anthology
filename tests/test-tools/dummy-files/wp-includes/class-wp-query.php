<?php

class WP_Query {

	public static $__data;
	public static $__posts;
	public static $__instance;

	public function __construct( $data = null ) {
		self::$__data     = $data;
		self::$__instance = $this;
		$this->posts      = is_array( self::$__posts ) ? self::$__posts : array();
		$this->post_count = $this->found_posts = count( $this->posts );
		$this->post       = current( $this->posts );
		$this->query_vars = $data;
	}

	// Reset the object at the end of each test.
	public static function reset() {
		self::$__data  = null;
		self::$__posts = null;
	}

	public function have_posts() {
		return $this->has_next( $this->posts );
	}

	public function the_post() {
		$this->post = next( $this->posts );
	}

	protected function has_next( $array ) {
		return false !== next( $array );
	}
}
