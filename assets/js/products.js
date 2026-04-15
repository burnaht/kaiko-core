/**
 * KAIKO Products Page.
 *
 * Disables filter button functionality — buttons are aesthetic only.
 * They still show the active state on click for visual feedback,
 * but do not filter products.
 *
 * @package KaikoCore
 */
(function () {
	'use strict';

	document.addEventListener('DOMContentLoaded', function () {
		var filterButtons = document.querySelectorAll('.filter-btn');

		if (!filterButtons.length) return;

		filterButtons.forEach(function (btn) {
			// Remove any existing click handlers by cloning
			var newBtn = btn.cloneNode(true);
			btn.parentNode.replaceChild(newBtn, btn);

			// Add visual-only click: toggle active state
			newBtn.addEventListener('click', function (e) {
				e.preventDefault();
				e.stopPropagation();

				// Remove active from all, add to clicked
				document.querySelectorAll('.filter-btn').forEach(function (b) {
					b.classList.remove('active');
				});
				newBtn.classList.add('active');
			});

			// Remove data-filter to prevent any other JS from using it
			newBtn.removeAttribute('data-filter');
		});
	});
})();
