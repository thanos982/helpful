<?php
/**
 * Helper for getting stored values in database.
 *
 * @package Helpful
 * @author  Pixelbart <me@pixelbart.de>
 */
class Helpful_Helper_Stats {

	/**
	 * Helpful color helper
	 *
	 * @var $green
	 */
	public static $green = '#88c057';

	/**
	 * Helpful color helper
	 *
	 * @var $red
	 */
	public static $red = '#ed7161';

	/**
	 * Todo: Helper for wp_date since WorddPress 5.4.
	 * Todo: Currently there is a bug with the time zone here.
	 *
	 * @url https://developer.wordpress.org/reference/functions/date_i18n/
	 *
	 * @param string      $format Format to display the date.
	 * @param int|boolean $timestamp_with_offset Whether to use GMT timezone. Only applies if timestamp is not provided.
	 * @param boolean     $gmt Whether to use GMT timezone. Only applies if timestamp is not provided.
	 * @return string The date, translated if locale specifies it.
	 */
	private static function helpful_date( $format, $timestamp_with_offset = false, $gmt = false )
	{
		if ( function_exists( 'wp_date' ) ) {
			return wp_date( $format, $timestamp_with_offset, $gmt );
		} elseif ( function_exists( 'date_i18n' ) ) {
			return date_i18n( $format, $timestamp_with_offset, $gmt );
		} else {
			return date( $format, $timestamp_with_offset );
		}
	}

	/**
	 * Get amount of pro by post id.
	 *
	 * @global $wpdb, $post
	 *
	 * @param int  $post_id     if null current post id.
	 * @param bool $percentages return percentage values on true.
	 *
	 * @return string
	 */
	public static function getPro( int $post_id = null, bool $percentages = false ):string
	{
		if ( is_null( $post_id ) ) {
			global $post;
			$post_id = $post->ID;
		}

		global $wpdb;

		$post_id = absint( $post_id );
		$helpful = $wpdb->prefix . 'helpful';
		$sql     = $wpdb->prepare( "SELECT COUNT(*) FROM $helpful WHERE pro = 1 AND post_id = %d", intval( $post_id ) );

		$cache_name   = 'helpful_pro_' . $post_id;
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$var          = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$var = $wpdb->get_var( $sql );
		} elseif ( false === $var ) {
			$var = $wpdb->get_var( $sql );
			set_transient( $cache_name, maybe_serialize( $var ), $cache_time );
		}

		$var = maybe_unserialize( $var );

		if ( false === $percentages ) {
			return $var;
		}

		$pro         = $var ?: 0;
		$contra      = self::getContra( $post_id );
		$percentage  = 0;

		if ( 0 !== $pro ) {
			$average    = (int) ( $pro - $contra );
			$total      = (int) ( $pro + $contra );
			$percentage = ( $pro / $total ) * 100;
			$percentage = round( $percentage, 2 );
			$percentage = number_format( $percentage, 2 );
		}

		return str_replace( '.00', '', $percentage );
	}

	/**
	 * Get contra count by post id.
	 *
	 * @global $wpdb, $post
	 *
	 * @param int  $post_id     if null current post id.
	 * @param bool $percentages return percentage values on true.
	 *
	 * @return string
	 */
	public static function getContra( int $post_id = null, bool $percentages = false ):string
	{
		if ( is_null( $post_id ) ) {
			global $post, $wpdb;
			$post_id = $post->ID;
		}

		global $wpdb;

		$post_id = absint( $post_id );
		$helpful = $wpdb->prefix . 'helpful';
		$sql     = $wpdb->prepare( "SELECT COUNT(*) FROM $helpful WHERE contra = 1 AND post_id = %d", intval( $post_id ) );

		$cache_name   = 'helpful_contra_' . $post_id;
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$var          = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$var = $wpdb->get_var( $sql );
		} elseif ( false === $var ) {
			$var = $wpdb->get_var( $sql );
			set_transient( $cache_name, maybe_serialize( $var ), $cache_time );
		}

		$var = maybe_unserialize( $var );

		if ( false === $percentages ) {
			return $var;
		}

		$contra      = $var ?: 0;
		$pro         = self::getPro( $post_id );
		$percentage  = 0;

		if ( 0 !== $contra ) {
			$average    = (int) ( $contra - $pro );
			$total      = (int) ( $contra + $pro );
			$percentage = ( $contra / $total ) * 100;
			$percentage = round( $percentage, 2 );
			$percentage = number_format( $percentage, 2 );
		}

		return str_replace( '.00', '', $percentage );
	}

	/**
	 * Get pro count of all posts.
	 *
	 * @global $wpdb
	 *
	 * @param bool $percentages return percentage values on true.
	 *
	 * @return int count
	 */
	public static function getProAll( bool $percentages = false ):string
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$sql     = "SELECT COUNT(*) FROM $helpful WHERE pro = 1";

		$cache_name   = 'helpful_pro_all';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$var          = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$var = $wpdb->get_var( $sql );
		} elseif ( false === $var ) {
			$var = $wpdb->get_var( $sql );
			set_transient( $cache_name, maybe_serialize( $var ), $cache_time );
		}

		$var = maybe_unserialize( $var );

		if ( false === $percentages ) {
			return $var;
		}

		$pro         = $var ?: 0;
		$contra      = self::getContraAll();
		$pro_percent = 0;

		if ( is_int( $pro ) && 0 !== $pro && 0 <= $pro ) {
			$pro_percent = ( ( $pro / ( $pro + $contra ) ) * 100 );
		}

		$pro_percent = number_format( $pro_percent, 2 );

		return (float) str_replace( '.00', '', $pro_percent );
	}

	/**
	 * Get contra count of all posts.
	 *
	 * @global $wpdb
	 *
	 * @param bool $percentages return percentage values on true.
	 *
	 * @return int count
	 */
	public static function getContraAll( bool $percentages = false ):string
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$sql     = "SELECT COUNT(*) FROM $helpful WHERE contra = 1";

		$cache_name   = 'helpful_contra_all';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$var          = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$var = $wpdb->get_var( $sql );
		} elseif ( false === $var ) {
			$var = $wpdb->get_var( $sql );
			set_transient( $cache_name, maybe_serialize( $var ), $cache_time );
		}

		$var = maybe_unserialize( $var );

		if ( false === $percentages ) {
			return $var;
		}

		$contra         = $var ?: 0;
		$pro            = self::getProAll();
		$contra_percent = 0;

		if ( is_int( $contra ) && 0 !== $contra && 0 <= $contra ) {
			$contra_percent = ( ( $contra / ( $pro + $contra ) ) * 100 );
		}

		$contra_percent = number_format( $contra_percent, 2 );
		return (float) str_replace( '.00', '', $contra_percent );
	}

	/**
	 * Get years
	 *
	 * @global $wpdb
	 *
	 * @return array
	 */
	public static function getYears():array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$sql     = "SELECT time FROM $helpful ORDER BY time DESC";

		$cache_name   = 'helpful_years';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [];
		}

		$years = [];

		foreach ( $results as $result ) :
			$years[] = date( 'Y', strtotime( $result->time ) );
		endforeach;

		$years = array_unique( $years );

		return $years;
	}

	/**
	 * Stats for today.
	 *
	 * @global $wpdb
	 *
	 * @param int $year response year.
	 *
	 * @return array
	 */
	public static function getStatsToday( int $year ):array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$query   = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE DAYOFYEAR(time) = DAYOFYEAR(NOW())
		AND YEAR(time) = %d
		";

		$sql = $wpdb->prepare( $query, intval( $year ) );

		$cache_name   = 'helpful_today';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [
				'status'  => 'error',
				'message' => __( 'No entries found', 'helpful' ),
			];
		}

		$pro    = wp_list_pluck( $results, 'pro' );
		$pro    = array_sum( $pro );
		$contra = wp_list_pluck( $results, 'contra' );
		$contra = array_sum( $contra );

		/* Response for ChartJS */
		$response = [
			'type'    => 'doughnut',
			'data'    => [
				'datasets' => [
					[
						'data'            => [
							absint( $pro ),
							absint( $contra ),
						],
						'backgroundColor' => [
							self::$green,
							self::$red,
						],
					],
				],
				'labels'   => [
					__( 'Pro', 'helpful' ),
					__( 'Contra', 'helpful' ),
				],
			],
			'options' => [
				'legend' => [
					'position' => 'bottom',
				],
			],
		];

		return $response;
	}

	/**
	 * Stats for yesterday.
	 *
	 * @global $wpdb
	 *
	 * @param int $year response year.
	 *
	 * @return array
	 */
	public static function getStatsYesterday( int $year ):array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$query   = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE DAYOFYEAR(time) = DAYOFYEAR(SUBDATE(CURDATE(),1))
		AND YEAR(time) = %d
		";

		$sql = $wpdb->prepare( $query, intval( $year ) );

		$cache_name   = 'helpful_yesterday';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [
				'status'  => 'error',
				'message' => __( 'No entries found', 'helpful' ),
			];
		}

		$pro    = wp_list_pluck( $results, 'pro' );
		$pro    = array_sum( $pro );
		$contra = wp_list_pluck( $results, 'contra' );
		$contra = array_sum( $contra );

		/* Response for ChartJS */
		$response = [
			'type'    => 'doughnut',
			'data'    => [
				'datasets' => [
					[
						'data'            => [
							absint( $pro ),
							absint( $contra ),
						],
						'backgroundColor' => [
							self::$green,
							self::$red,
						],
					],
				],
				'labels'   => [
					__( 'Pro', 'helpful' ),
					__( 'Contra', 'helpful' ),
				],
			],
			'options' => [
				'legend' => [
					'position' => 'bottom',
				],
			],
		];

		return $response;
	}

	/**
	 * Stats for week
	 *
	 * @global $wpdb
	 *
	 * @param int $year response year.
	 *
	 * @return array
	 */
	public static function getStatsWeek( int $year ):array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$query   = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE WEEK(time, 1) = WEEK(CURDATE(), 1)
		AND YEAR(time) = %d
		";

		$sql = $wpdb->prepare( $query, intval( $year ) );

		$cache_name   = 'helpful_week';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [
				'status'  => 'error',
				'message' => __( 'No entries found', 'helpful' ),
			];
		}

		$pro       = [];
		$contra    = [];
		$labels    = [];
		$timestamp = strtotime( 'monday this week' );
		$days      = 7;

		for ( $i = 0; $i < $days; $i++ ) :
			$date            = date_i18n( 'Ymd', strtotime( "+$i days", $timestamp ) );
			$day             = date_i18n( 'D', strtotime( "+$i days", $timestamp ) );
			$pro[ $date ]    = 0;
			$contra[ $date ] = 0;
			$labels[]        = $day;
		endfor;

		foreach ( $results as $result ) :
			for ( $i = 0; $i < $days; $i++ ) :
				$day  = date_i18n( 'Ymd', strtotime( "+$i days", $timestamp ) );
				$date = date_i18n( 'Ymd', strtotime( $result->time ) );

				if ( $day === $date ) {
					$pro[ $date ]    += $result->pro;
					$contra[ $date ] += $result->contra;
				}
			endfor;
		endforeach;

		/* Response for ChartJS */
		$response = [
			'type'    => 'bar',
			'data'    => [
				'datasets' => [
					[
						'label'           => __( 'Pro', 'helpful' ),
						'data'            => array_values( $pro ),
						'backgroundColor' => self::$green,
					],
					[
						'label'           => __( 'Contra', 'helpful' ),
						'data'            => array_values( $contra ),
						'backgroundColor' => self::$red,
					],
				],
				'labels'   => $labels,
			],
			'options' => [
				'scales' => [
					'xAxes' => [
						[ 'stacked' => true ],
					],
					'yAxes' => [
						[ 'stacked' => true ],
					],
				],
				'legend' => [
					'position' => 'bottom',
				],
			],
		];

		return $response;
	}

	/**
	 * Stats for month
	 *
	 * @global $wpdb
	 *
	 * @param int $year response year.
	 * @param int $month response month.
	 *
	 * @return array
	 */
	public static function getStatsMonth( int $year, int $month = null ):array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';

		if ( is_null( $month ) ) {
			$month = date( 'm' );
		} else {
			$month = absint( $month );
		}

		$query   = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE MONTH(time) = %d
		AND YEAR(time) = %d
		";

		$sql = $wpdb->prepare( $query, intval( $month ), intval( $year ) );

		$cache_name   = 'helpful_month';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [
				'status'  => 'error',
				'message' => __( 'No entries found', 'helpful' ),
			];
		}

		$pro       = [];
		$contra    = [];
		$labels    = [];
		$timestamp = strtotime( date( "$year-$month-1" ) );
		$days      = date_i18n( 't', $timestamp ) - 1;

		for ( $i = 0; $i < $days; $i++ ) :
			$date            = date_i18n( 'Ymd', strtotime( "+$i days", $timestamp ) );
			$day             = date_i18n( 'j M', strtotime( "+$i days", $timestamp ) );
			$pro[ $date ]    = 0;
			$contra[ $date ] = 0;
			$labels[]        = $day;
		endfor;

		foreach ( $results as $result ) :
			for ( $i = 0; $i < $days; $i++ ) :
				$day  = date_i18n( 'Ymd', strtotime( "+$i days", $timestamp ) );
				$date = date_i18n( 'Ymd', strtotime( $result->time ) );

				if ( $day === $date ) {
					$pro[ $date ]    += $result->pro;
					$contra[ $date ] += $result->contra;
				}
			endfor;
		endforeach;

		/* Response for ChartJS */
		$response = [
			'type'    => 'bar',
			'data'    => [
				'datasets' => [
					[
						'label'           => __( 'Pro', 'helpful' ),
						'data'            => array_values( $pro ),
						'backgroundColor' => self::$green,
					],
					[
						'label'           => __( 'Contra', 'helpful' ),
						'data'            => array_values( $contra ),
						'backgroundColor' => self::$red,
					],
				],
				'labels'   => $labels,
			],
			'options' => [
				'scales' => [
					'xAxes' => [
						[ 'stacked' => true ],
					],
					'yAxes' => [
						[ 'stacked' => true ],
					],
				],
				'legend' => [
					'position' => 'bottom',
				],
			],
		];

		return $response;
	}

	/**
	 * Stats for year
	 *
	 * @global $wpdb
	 *
	 * @param int $year response year.
	 *
	 * @return array
	 */
	public static function getStatsYear( int $year ):array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$query   = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE YEAR(time) = %d
		";

		$sql = $wpdb->prepare( $query, intval( $year ) );

		$cache_name   = 'helpful_year';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [
				'status'  => 'error',
				'message' => __( 'No entries found', 'helpful' ),
			];
		}

		$pro       = [];
		$contra    = [];
		$labels    = [];
		$timestamp = strtotime( sprintf( date( '%d-1-1' ), intval( $year ) ) );
		$days      = 12;

		for ( $i = 0; $i < $days; $i++ ) :
			$month             = date_i18n( 'M', strtotime( "+$i months", $timestamp ) );
			$pro[ $month ]     = 0;
			$contra [ $month ] = 0;
			$labels[]          = $month;
		endfor;

		foreach ( $results as $result ) :
			for ( $i = 0; $i < $days; $i++ ) :
				$month = date_i18n( 'M', strtotime( "+$i months", $timestamp ) );
				$m     = date_i18n( 'M', strtotime( $result->time ) );

				if ( $month === $m ) {
					$pro[ $month ]    += $result->pro;
					$contra[ $month ] += $result->contra;
				}
			endfor;
		endforeach;

		/* Response for ChartJS */
		$response = [
			'type'    => 'bar',
			'data'    => [
				'datasets' => [
					[
						'label'           => __( 'Pro', 'helpful' ),
						'data'            => array_values( $pro ),
						'backgroundColor' => self::$green,
					],
					[
						'label'           => __( 'Contra', 'helpful' ),
						'data'            => array_values( $contra ),
						'backgroundColor' => self::$red,
					],
				],
				'labels'   => $labels,
			],
			'options' => [
				'scales' => [
					'xAxes' => [
						[ 'stacked' => true ],
					],
					'yAxes' => [
						[ 'stacked' => true ],
					],
				],
				'legend' => [
					'position' => 'bottom',
				],
			],
		];

		return $response;
	}

	/**
	 * Stats by range
	 *
	 * @global $wpdb
	 *
	 * @param string $from time string.
	 * @param string $to time string.
	 *
	 * @return array
	 */
	public static function getStatsRange( string $from, string $to ):array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$query   = "
		SELECT pro, contra, time
		FROM $helpful
		WHERE DATE(time) >= DATE(%s)
		AND DATE(time) <= DATE(%s)
		";

		$sql = $wpdb->prepare( $query, $from, $to );

		$cache_name   = 'helpful_from_' . $from . '_to_' . $to;
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [
				'status'  => 'error',
				'message' => __( 'No entries found', 'helpful' ),
			];
		}

		$from_date = new DateTime( $from );
		$to_date   = new DateTime( $to );
		$diff      = $from_date->diff( $to_date );
		$pro       = [];
		$contra    = [];
		$labels    = [];
		$timestamp = strtotime( $from );
		$limit     = ( $diff->format( '%a' ) + 1 );

		for ( $i = 0; $i < $limit; $i++ ) :
			$date            = date_i18n( 'Ymd', strtotime( "+$i days", $timestamp ) );
			$day             = date_i18n( 'j M', strtotime( "+$i days", $timestamp ) );
			$pro[ $date ]    = 0;
			$contra[ $date ] = 0;
			$labels[]        = $day;
		endfor;

		foreach ( $results as $result ) {
			$date             = date_i18n( 'Ymd', strtotime( $result->time ) );
			$pro[ $date ]    += (int) $result->pro;
			$contra[ $date ] += (int) $result->contra;
		}

		/* Response for ChartJS */
		$response = [
			'type'    => 'bar',
			'data'    => [
				'datasets' => [
					[
						'label'           => __( 'Pro', 'helpful' ),
						'data'            => array_values( $pro ),
						'backgroundColor' => self::$green,
					],
					[
						'label'           => __( 'Contra', 'helpful' ),
						'data'            => array_values( $contra ),
						'backgroundColor' => self::$red,
					],
				],
				'labels'   => $labels,
			],
			'options' => [
				'legend' => [
					'position' => 'bottom',
				],
			],
		];

		return $response;
	}

	/**
	 * Stats for total
	 *
	 * @global $wpdb
	 *
	 * @return array
	 */
	public static function getStatsTotal():array
	{
		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$sql   = "SELECT pro, contra, time FROM $helpful";

		$cache_name   = 'helpful_total';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( ! $results ) {
			return [
				'status'  => 'error',
				'message' => __( 'No entries found', 'helpful' ),
			];
		}

		$pro = wp_list_pluck( $results, 'pro' );
		$pro = array_sum( $pro );

		$contra = wp_list_pluck( $results, 'contra' );
		$contra = array_sum( $contra );

		/* Response for ChartJS */
		$response = [
			'type'    => 'doughnut',
			'data'    => [
				'datasets' => [
					[
						'data'            => [
							absint( $pro ),
							absint( $contra ),
						],
						'backgroundColor' => [
							self::$green,
							self::$red,
						],
					],
				],
				'labels'   => [
					__( 'Pro', 'helpful' ),
					__( 'Contra', 'helpful' ),
				],
			],
			'options' => [
				'legend' => [
					'position' => 'bottom',
				],
			],
		];

		return $response;
	}

	/**
	 * Get most helpful posts.
	 *
	 * @param int $limit posts per page.
	 *
	 * @return array
	 */
	public static function getMostHelpful( int $limit = null ):array
	{
		if ( is_null( $limit ) ) {
			$limit = intval( get_option( 'helpful_widget_amount' ) );
		} else {
			$limit = intval( $limit );
		}

		$args  = [
			'post_type'      => get_option( 'helpful_post_types' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		$cache_name   = 'helpful_most_helpful';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$query        = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$query = new WP_Query( $args );
		} elseif ( false === $query ) {
			$query = new WP_Query( $args );
			set_transient( $cache_name, maybe_serialize( $query ), $cache_time );
		}

		$query = maybe_unserialize( $query );
		$posts = [];

		if ( $query->found_posts ) {
			foreach ( $query->posts as $post_id ) :
				$pro               = self::getPro( $post_id ) ? self::getPro( $post_id ) : 0;
				$contra            = self::getContra( $post_id ) ? self::getContra( $post_id ) : 0;
				$posts[ $post_id ] = (int) ( $pro - $contra );
			endforeach;

			if ( 1 < count( $posts ) ) {

				arsort( $posts );

				$results = [];
				$posts   = array_slice( $posts, 0, $limit, true );

				foreach ( $posts as $post_id => $value ) :
					if ( 0 === $value ) {
						continue;
					}

					$post_time = '';

					if ( get_the_date( 'U', $post_id ) ) {
						$post_time = sprintf(
							/* translators: %s time difference */
							__( 'Published %s ago', 'helpful' ),
							human_time_diff( get_the_date( 'U', $post_id ), date_i18n( 'U' ) )
						);
					}

					$pro        = self::getPro( $post_id ) ? self::getPro( $post_id ) : 0;
					$contra     = self::getContra( $post_id ) ? self::getContra( $post_id ) : 0;
					$average    = (int) ( $pro - $contra );
					$total      = (int) ( $pro + $contra );
					$percentage = ( $pro / $total ) * 100;
					$percentage = round( $percentage, 2 );
					$results[]  = [
						'ID'         => $post_id,
						'url'        => get_the_permalink( $post_id ),
						'name'       => get_the_title( $post_id ),
						'pro'        => $pro,
						'contra'     => $contra,
						'percentage' => $percentage,
						'time'       => $post_time,
					];
				endforeach;
			}
		}

		if ( is_array( $results ) ) {
			$results = array_filter( $results );
		}

		return $results;
	}

	/**
	 * Get least helpful posts.
	 *
	 * @param int $limit posts per page.
	 *
	 * @return array
	 */
	public static function getLeastHelpful( int $limit = null ):array
	{
		if ( is_null( $limit ) ) {
			$limit = absint( get_option( 'helpful_widget_amount' ) );
		}

		$args  = [
			'post_type'      => get_option( 'helpful_post_types' ),
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
		];

		$cache_name   = 'helpful_least_helpful';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$query        = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$query = new WP_Query( $args );
		} elseif ( false === $query ) {
			$query = new WP_Query( $args );
			set_transient( $cache_name, maybe_serialize( $query ), $cache_time );
		}

		$query = maybe_unserialize( $query );
		$posts = [];

		if ( $query->found_posts ) {
			foreach ( $query->posts as $post_id ) :
				$pro               = self::getPro( $post_id ) ? self::getPro( $post_id ) : 0;
				$contra            = self::getContra( $post_id ) ? self::getContra( $post_id ) : 0;
				$posts[ $post_id ] = (int) ( $contra - $pro );
			endforeach;

			if ( 1 < count( $posts ) ) {

				arsort( $posts );

				$results = [];
				$posts   = array_slice( $posts, 0, $limit, true );

				foreach ( $posts as $post_id => $value ) :
					if ( 0 === $value ) {
						continue;
					}

					$pro        = self::getPro( $post_id ) ? self::getPro( $post_id ) : 0;
					$contra     = self::getContra( $post_id ) ? self::getContra( $post_id ) : 0;
					$average    = (int) ( $contra - $pro );
					$total      = (int) ( $pro + $contra );
					$percentage = ( $contra / $total );

					if ( 1 !== $percentage ) {
						$percentage = ( $pro / $total ) * 100;
						$percentage = round( $percentage, 2 );
					} else {
						$percentage = 0;
					}

					$post_time = '';

					if ( get_the_date( 'U', $post_id ) ) {
						$post_time = sprintf(
							/* translators: %s time difference */
							__( 'Published %s ago', 'helpful' ),
							human_time_diff( get_the_date( 'U', $post_id ), date_i18n( 'U' ) )
						);
					}

					$results[]  = [
						'ID'         => $post_id,
						'url'        => get_the_permalink( $post_id ),
						'name'       => get_the_title( $post_id ),
						'pro'        => $pro,
						'contra'     => $contra,
						'percentage' => $percentage,
						'time'       => $post_time,
					];
				endforeach;
			}
		}

		if ( is_array( $results ) ) {
			$results = array_filter( $results );
		}

		return $results;
	}

	/**
	 * Get recently helpful pro posts
	 *
	 * @global $wpdb
	 *
	 * @param int $limit posts per page.
	 *
	 * @return array
	 */
	public static function getRecentlyPro( int $limit = null ):array
	{
		if ( is_null( $limit ) ) {
			$limit = absint( get_option( 'helpful_widget_amount' ) );
		}

		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$sql     = "
		SELECT post_id, time
		FROM $helpful
		WHERE pro = %d
		ORDER BY id DESC
		LIMIT %d
		";

		$posts = [];
		$sql   = $wpdb->prepare( $sql, 1, intval( $limit ) );

		$cache_name   = 'helpful_recently_pro';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( $results ) {
			foreach ( $results as $post ) :

				$pro        = self::getPro( $post->post_id ) ? self::getPro( $post->post_id ) : 0;
				$contra     = self::getContra( $post->post_id ) ? self::getContra( $post->post_id ) : 0;
				$average    = (int) ( $pro - $contra );
				$total      = (int) ( $pro + $contra );
				$percentage = ( $average / $total ) * 100;
				$percentage = round( $percentage, 2 );
				$timestamp  = strtotime( $post->time );
				$posts[]    = [
					'ID'         => $post->post_id,
					'url'        => get_the_permalink( $post->post_id ),
					'name'       => get_the_title( $post->post_id ),
					'percentage' => $percentage,
					'time'       => sprintf(
						/* translators: %s time difference */
						__( 'Submitted %s ago', 'helpful' ),
						human_time_diff( $timestamp, date_i18n( 'U' ) )
					),
				];
			endforeach;
		}

		return $posts;
	}

	/**
	 * Get recently unhelpful pro posts.
	 *
	 * @global $wpdb
	 *
	 * @param int $limit posts per page.
	 *
	 * @return array
	 */
	public static function getRecentlyContra( int $limit = null ):array
	{
		if ( is_null( $limit ) ) {
			$limit = absint( get_option( 'helpful_widget_amount' ) );
		}

		global $wpdb;

		$helpful = $wpdb->prefix . 'helpful';
		$sql     = "
		SELECT post_id, time
		FROM $helpful
		WHERE contra = %d
		ORDER BY id DESC
		LIMIT %d
		";

		$posts = [];
		$sql   = $wpdb->prepare( $sql, 1, intval( $limit ) );

		$cache_name   = 'helpful_recently_contra';
		$cache_time   = get_option( 'helpful_cache_time', 'minute' );
		$cache_active = get_option( 'helpful_caching', 'off' );
		$cache_times  = Helpful_Helper_Cache::get_cache_times( false );
		$cache_time   = $cache_times[ $cache_time ];
		$results      = get_transient( $cache_name );

		if ( 'on' !== $cache_active ) {
			$results = $wpdb->get_results( $sql );
		} elseif ( false === $results ) {
			$results = $wpdb->get_results( $sql );
			set_transient( $cache_name, maybe_serialize( $results ), $cache_time );
		}

		$results = maybe_unserialize( $results );

		if ( $results ) {
			foreach ( $results as $post ) :

				$pro        = self::getPro( $post->post_id ) ? self::getPro( $post->post_id ) : 0;
				$contra     = self::getContra( $post->post_id ) ? self::getContra( $post->post_id ) : 0;
				$average    = (int) ( $contra - $pro );
				$total      = (int) ( $pro + $contra );
				$percentage = ( $average / $total ) * 100;
				$percentage = round( $percentage, 2 );
				$timestamp  = strtotime( $post->time );

				$posts[] = [
					'ID'         => $post->post_id,
					'url'        => get_the_permalink( $post->post_id ),
					'name'       => get_the_title( $post->post_id ),
					'percentage' => $percentage,
					'time'       => sprintf(
						/* translators: %s time difference */
						__( 'Submitted %s ago', 'helpful' ),
						human_time_diff( $timestamp, date_i18n( 'U' ) )
					),
				];
			endforeach;
		}

		return $posts;
	}
}
