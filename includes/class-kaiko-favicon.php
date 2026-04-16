<?php
/**
 * Favicon Module.
 *
 * Generates 32x32 and 180x180 favicons from the kaiko.png logo using PHP GD
 * on a one-time admin action. Outputs <link> tags in wp_head on all pages.
 *
 * @package KaikoCore
 */

defined( 'ABSPATH' ) || exit;

class Kaiko_Favicon {

	/** @var string Option key to track whether favicons have been generated. */
	private const GENERATED_FLAG = 'kaiko_favicons_generated';

	/** @var string Path to source logo. */
	private string $source;

	/** @var string Path to 32x32 favicon. */
	private string $favicon_32;

	/** @var string Path to apple touch icon. */
	private string $apple_icon;

	public function __construct() {
		$images_dir       = KAIKO_CORE_PATH . 'assets/images/';
		$this->source     = $images_dir . 'kaiko.png';
		$this->favicon_32 = $images_dir . 'favicon-32.png';
		$this->apple_icon = $images_dir . 'apple-touch-icon.png';
	}

	/**
	 * Register hooks.
	 */
	public function init(): void {
		// Generate favicons once on admin load (similar to Kaiko_Setup pattern).
		add_action( 'admin_init', [ $this, 'maybe_generate' ] );

		// Output favicon link tags on all pages (not limited to KAIKO frontend).
		add_action( 'wp_head', [ $this, 'output_tags' ], 1 );
	}

	/**
	 * Generate favicons if the source logo exists and favicons haven't been created yet.
	 */
	public function maybe_generate(): void {
		if ( get_option( self::GENERATED_FLAG ) ) {
			return;
		}

		if ( ! file_exists( $this->source ) ) {
			return;
		}

		if ( ! function_exists( 'imagecreatefrompng' ) ) {
			return;
		}

		$source_img = @imagecreatefrompng( $this->source );
		if ( ! $source_img ) {
			return;
		}

		$generated = true;

		// Generate 32x32 favicon.
		$generated = $this->resize_and_save( $source_img, 32, $this->favicon_32 ) && $generated;

		// Generate 180x180 apple touch icon.
		$generated = $this->resize_and_save( $source_img, 180, $this->apple_icon ) && $generated;

		imagedestroy( $source_img );

		if ( $generated ) {
			update_option( self::GENERATED_FLAG, true, false );
		}
	}

	/**
	 * Resize source image to a square and save as PNG.
	 *
	 * @param \GdImage $source Source GD image resource.
	 * @param int      $size   Target width and height in pixels.
	 * @param string   $path   Output file path.
	 * @return bool True on success.
	 */
	private function resize_and_save( $source, int $size, string $path ): bool {
		$src_w = imagesx( $source );
		$src_h = imagesy( $source );

		$thumb = imagecreatetruecolor( $size, $size );
		if ( ! $thumb ) {
			return false;
		}

		// Preserve transparency.
		imagealphablending( $thumb, false );
		imagesavealpha( $thumb, true );
		$transparent = imagecolorallocatealpha( $thumb, 0, 0, 0, 127 );
		imagefill( $thumb, 0, 0, $transparent );

		imagecopyresampled( $thumb, $source, 0, 0, 0, 0, $size, $size, $src_w, $src_h );

		$result = imagepng( $thumb, $path, 9 );
		imagedestroy( $thumb );

		return $result;
	}

	/**
	 * Output favicon link tags in <head>.
	 */
	public function output_tags(): void {
		if ( file_exists( $this->favicon_32 ) ) {
			$url = KAIKO_CORE_URL . 'assets/images/favicon-32.png';
			echo '<link rel="icon" type="image/png" sizes="32x32" href="' . esc_url( $url ) . '">' . "\n";
		}

		if ( file_exists( $this->apple_icon ) ) {
			$url = KAIKO_CORE_URL . 'assets/images/apple-touch-icon.png';
			echo '<link rel="apple-touch-icon" sizes="180x180" href="' . esc_url( $url ) . '">' . "\n";
		}
	}
}
