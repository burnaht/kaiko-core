<?php
/**
 * KAIKO Email Footer.
 *
 * Overrides WooCommerce's default emails/email-footer.php.
 * Branded teal footer with links, social placeholders, copyright.
 *
 * @package KaikoCore
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
						</td>
					</tr>

					<!-- Spacer between content and footer -->
					<tr>
						<td style="background-color: #ffffff; padding: 0 48px 48px;">
							&nbsp;
						</td>
					</tr>

					<!-- Footer — teal background -->
					<tr>
						<td style="background-color: #1a5c52; border-radius: 0 0 8px 8px; padding: 40px 48px; text-align: center;">

							<!-- Website link -->
							<p style="margin: 0 0 20px; font-family: Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.6;">
								<a href="https://www.kaikoproducts.com" style="color: #ffffff; text-decoration: none; letter-spacing: 0.5px; text-transform: uppercase; font-weight: 600; font-size: 11px;">
									kaikoproducts.com
								</a>
							</p>

							<!-- Social links placeholder -->
							<p style="margin: 0 0 24px; font-family: Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.6;">
								<!--
								Replace with social icon images or text links:
								<a href="#" style="color: #ffffff; text-decoration: none; margin: 0 8px;">Instagram</a>
								<a href="#" style="color: #ffffff; text-decoration: none; margin: 0 8px;">Facebook</a>
								-->
							</p>

							<!-- Divider -->
							<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
								<tr>
									<td style="padding: 0 0 24px;">
										<hr style="border: none; border-top: 1px solid rgba(255,255,255,0.2); margin: 0;" />
									</td>
								</tr>
							</table>

							<!-- Copyright -->
							<p style="margin: 0 0 12px; font-family: Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.6; color: rgba(255,255,255,0.7);">
								&copy; <?php echo esc_html( date( 'Y' ) ); ?> KAIKO Products. All rights reserved.
							</p>

							<!-- Unsubscribe / fine print -->
							<p style="margin: 0; font-family: Helvetica, Arial, sans-serif; font-size: 11px; line-height: 1.6; color: rgba(255,255,255,0.5);">
								You are receiving this email because you placed an order or created an account at
								<a href="https://www.kaikoproducts.com" style="color: rgba(255,255,255,0.7); text-decoration: underline;">kaikoproducts.com</a>.
							</p>

						</td>
					</tr>

				</table>
				<!-- /Email container -->

				<!-- Bottom padding -->
				<table width="600" border="0" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; width: 100%;">
					<tr>
						<td style="padding: 40px 0;">&nbsp;</td>
					</tr>
				</table>

			</td>
		</tr>
	</table>
	<!-- /Outer wrapper -->

</body>
</html>
