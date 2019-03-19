<?php

class WPML_TM_Jobs_Composite_Query implements WPML_TM_Jobs_Query {
	const METHODD_UNION = 'union';
	const METHOD_COUNT  = 'count';

	/** @var WPML_TM_Jobs_Composite_Query[] */
	private $queries;

	/** @var WPML_TM_Jobs_Limit_Query_Helper */
	private $limit_query_helper;

	/** @var WPML_TM_Jobs_Order_Query_Helper */
	private $order_query_helper;

	/**
	 * @param WPML_TM_Jobs_Composite_Query[]  $queries
	 * @param WPML_TM_Jobs_Limit_Query_Helper
	 * @param WPML_TM_Jobs_Order_Query_Helper $order_helper
	 */
	public function __construct(
		array $queries,
		WPML_TM_Jobs_Limit_Query_Helper $limit_helper,
		WPML_TM_Jobs_Order_Query_Helper $order_helper
	) {
		$queries = array_filter( $queries, array( $this, 'is_query_valid' ) );
		if ( empty( $queries ) ) {
			throw new InvalidArgumentException( 'Collection of sub-queries is empty or contains only invalid elements' );
		}

		$this->queries            = $queries;
		$this->limit_query_helper = $limit_helper;
		$this->order_query_helper = $order_helper;
	}


	/**
	 * @param WPML_TM_Jobs_Search_Params $params
	 *
	 * @throws InvalidArgumentException
	 * @return string
	 */
	public function get_data_query( WPML_TM_Jobs_Search_Params $params ) {
		if ( ! $params->get_job_types() ) {
			// We are merging subqueries here, that's why LIMIT must be applied to final query
			$params_without_pagination_and_sorting = clone $params;
			$params_without_pagination_and_sorting->set_limit( 0 )->set_offset( 0 );
			$params_without_pagination_and_sorting->set_sorting( array() );

			$query = $this->get_sql( $params_without_pagination_and_sorting, self::METHODD_UNION );

			$order = $this->order_query_helper->get_order( $params );
			if ( $order ) {
				$query .= ' ' . $order;
			}
			$limit = $this->limit_query_helper->get_limit( $params );
			if ( $limit ) {
				$query .= ' ' . $limit;
			}

			return $query;
		} else {
			return $this->get_sql( $params, self::METHODD_UNION );
		}
	}

	public function get_count_query( WPML_TM_Jobs_Search_Params $params ) {
		$params_without_pagination_and_sorting = clone $params;
		$params_without_pagination_and_sorting->set_limit( 0 )->set_offset( 0 );
		$params_without_pagination_and_sorting->set_sorting( array() );

		return $this->get_sql( $params_without_pagination_and_sorting, self::METHOD_COUNT );
	}

	/**
	 * @param WPML_TM_Jobs_Search_Params $params
	 * @param string                     $method
	 *
	 * @return string
	 */
	private function get_sql( WPML_TM_Jobs_Search_Params $params, $method ) {
		switch ( $method ) {
			case self::METHODD_UNION:
				$query_method = 'get_data_query';
				break;
			case self::METHOD_COUNT:
				$query_method = 'get_count_query';
				break;
			default:
				throw new InvalidArgumentException( 'Invalid $method argument' );
		}

		$parts = array();
		foreach ( $this->queries as $query ) {
			$query_string = $query->$query_method( $params );
			if ( $query_string ) {
				$parts[] = $query_string;
			}
		}

		if ( ! $parts ) {
			throw new RuntimeException( 'None of subqueries matches to specified search parameters' );
		}

		if ( 1 === count( $parts ) ) {
			return current( $parts );
		}

		switch ( $method ) {
			case self::METHODD_UNION:
				return $this->get_union( $parts );
			case self::METHOD_COUNT:
				return $this->get_count( $parts );
		}

		return null;
	}

	private function get_union( array $parts ) {
		return '( ' . implode( ' ) UNION ( ', $parts ) . ' )';
	}

	private function get_count( array $parts ) {
		return 'SELECT ( ' . implode( ' ) + ( ', $parts ) . ' )';
	}

	/**
	 * @param mixed $query
	 *
	 * @return bool
	 */
	private function is_query_valid( $query ) {
		return $query instanceof WPML_TM_Jobs_Query;
	}
}
