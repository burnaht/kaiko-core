/**
 * KAIKO Navigation — mobile menu toggle.
 *
 * @package KaikoCore
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var hamburger = document.querySelector('.kaiko-hamburger');
		var navLinks = document.querySelector('.kaiko-nav-links');

		if (!hamburger || !navLinks) return;

		hamburger.addEventListener('click', function () {
			var isOpen = hamburger.classList.toggle('active');
			navLinks.classList.toggle('mobile-open');
			hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
		});

		// Close mobile menu when clicking a link
		navLinks.querySelectorAll('a').forEach(function (link) {
			link.addEventListener('click', function () {
				hamburger.classList.remove('active');
				navLinks.classList.remove('mobile-open');
				hamburger.setAttribute('aria-expanded', 'false');
			});
		});

		// Close mobile menu on Escape key
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape' && navLinks.classList.contains('mobile-open')) {
				hamburger.classList.remove('active');
				navLinks.classList.remove('mobile-open');
				hamburger.setAttribute('aria-expanded', 'false');
			}
		});
	});
})();
