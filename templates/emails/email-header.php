<?php
/**
 * KAIKO Email Header.
 *
 * Overrides WooCommerce's default emails/email-header.php.
 * Teal branded header with centered KAIKO logo, clean white content area.
 *
 * @package KaikoCore
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$logo_url = plugins_url( 'assets/images/kaiko.png', dirname( __FILE__, 2 ) );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title><?php echo esc_html( get_bloginfo( 'name' ) ); ?></title>
</head>
<body leftmargin="0" marginwidth="0" topmargin="0" marginheight="0" offset="0" style="margin: 0; padding: 0; background-color: #f4f4f4;">

	<!-- Outer wrapper — full-width background -->
	<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #f4f4f4;">
		<tr>
			<td align="center" style="padding: 40px 20px 0;">

				<!-- Email container — max 600px -->
				<table width="600" border="0" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; width: 100%;">

					<!-- Header bar — teal with logo -->
					<tr>
						<td align="center" style="background-color: #1a5c52; border-radius: 8px 8px 0 0; padding: 32px 40px;">
							<img
								src="<?php echo esc_url( $logo_url ); ?>"
								alt="KAIKO"
								width="200"
								style="display: block; max-width: 200px; width: 100%; height: auto; border: 0;"
							/>
						</td>
					</tr>

					<!-- Content area — white -->
					<tr>
						<td style="background-color: #ffffff; padding: 48px 48px 16px;">

							<?php if ( $email_heading ) : ?>
								<h1 style="margin: 0 0 24px; font-family: Helvetica, Arial, sans-serif; font-size: 24px; font-weight: 600; line-height: 1.3; color: #2d2d2d; letter-spacing: -0.3px;">
									<?php echo wp_kses_post( $email_heading ); ?>
								</h1>
							<?php endif; ?>
