<?php

namespace WeglotWP\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Weglot\Parser\Check\Regex\RegexChecker;
use Weglot\Util\SourceType;
use Weglot\Util\Text;
use WeglotWP\Models\Third_Active_Interface_Weglot;

/**
 * Dom Checkers
 *
 * @since 2.0
 * @version 2.0.6
 */
class Regex_Checkers_Service_Weglot {

	/**
	 * @since 2.3.0
	 */
	public function __construct() {

	}

	/**
	 * @return array<string,mixed>
	 * @throws \Exception
	 * @since 2.0
	 */
	public function get_regex_checkers() {

		$checkers = array();

		$other_words = apply_filters( 'weglot_words_translate', array() );
		foreach ( $other_words as $other_word ) {
			array_push( $checkers, new RegexChecker( '#\b' . $other_word . '\b#u', SourceType::SOURCE_TEXT, 0 ) );
		}

		$scandir_thirds = scandir( WEGLOT_DIR . '/src/third' );
		if ( false === $scandir_thirds ) {
			$scandir_thirds = [];
		}

		$thirds = array_diff( $scandir_thirds, array( '..', '.' ) );
		foreach ( $thirds as $third ) {
			$third_path = WEGLOT_DIR . '/src/third/' . $third;
			if ( ! is_dir( $third_path ) ) {
				continue;
			}

			$scandir_files = scandir( $third_path );
			if ( false === $scandir_files ) {
				$scandir_files = [];
			}
			$files = array_diff( $scandir_files, array( '..', '.' ) );

			foreach ( $files as $file ) {
				if ( strpos( $file, 'active.php' ) !== false ) {
					$file    = Text::removeFileExtension( $file );
					$file    = str_replace( 'class-', '', $file );
					$class_name_part = implode( '', array_map( 'ucfirst', explode( '-', $file ) ) );
					$namespace_part  = implode( '', array_map( 'ucfirst', explode( '-', $third ) ) );
					$fqcn = '\\WeglotWP\\Third\\' . $namespace_part . '\\' . $class_name_part;
					if ( ! class_exists( $fqcn ) ) {
						continue;
					}

					$service = weglot_get_service( $fqcn );
					if ( ! $service instanceof Third_Active_Interface_Weglot ) {
						continue;
					}

					$active = $service->is_active();

					if ( $active ) {
						$regex_dir = WEGLOT_DIR . '/src/third/' . $third . '/regexcheckers/';
						if ( is_dir( $regex_dir ) ) {
							$files = scandir( WEGLOT_DIR . '/src/third/' . $third . '/regexcheckers/' );
							if(is_array($files)){
								$regex_files = array_diff( $files, array( '..', '.' ) );
								foreach ( $regex_files as $regex_file ) {
									$filename = Text::removeFileExtension( $regex_file );
									$filename = str_replace( 'class-', '', $filename );
									$filename = implode( '_', array_map( 'ucfirst', explode( '-', $filename ) ) );
									$class    = '\\WeglotWP\\Third\\' . implode( '', array_map( 'ucfirst', explode( '-', $third ) ) ) . '\\Regexcheckers\\' . $filename;
									$checkers[] = new RegexChecker( $class::REGEX, $class::TYPE, $class::VAR_NUMBER, $class::$KEYS );
								}
							}
						}
					}
				}
			}
		}

		return apply_filters( 'weglot_get_regex_checkers', $checkers );
	}

}
