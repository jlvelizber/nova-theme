(function () {
	'use strict';

	var form = document.querySelector('form.header-search.js-header-search');
	if (!form) {
		return;
	}

	var input = form.querySelector('.header-search-input');
	var toggleBtn = form.querySelector('.header-search-toggle');
	if (!input || !toggleBtn) {
		return;
	}

	function isOpen() {
		return form.classList.contains('is-open');
	}

	function updateToggleButton() {
		var openLabel = toggleBtn.getAttribute('data-label-open') || '';
		var submitLabel = toggleBtn.getAttribute('data-label-submit') || '';
		toggleBtn.setAttribute('aria-label', isOpen() ? submitLabel : openLabel);
	}

	function openSearch() {
		form.classList.add('is-open');
		toggleBtn.setAttribute('aria-expanded', 'true');
		input.removeAttribute('tabindex');
		updateToggleButton();
		requestAnimationFrame(function () {
			input.focus({ preventScroll: true });
		});
	}

	function submitSearch() {
		if (typeof form.requestSubmit === 'function') {
			form.requestSubmit();
		} else {
			form.submit();
		}
	}

	toggleBtn.addEventListener('click', function (e) {
		e.preventDefault();
		if (!isOpen()) {
			openSearch();
			return;
		}
		submitSearch();
	});

	input.addEventListener('keydown', function (e) {
		if (e.key === 'Enter') {
			e.preventDefault();
			submitSearch();
		}
	});

	function init() {
		var hasValue = input.value && String(input.value).trim() !== '';
		if (hasValue) {
			form.classList.add('is-open');
			toggleBtn.setAttribute('aria-expanded', 'true');
			input.removeAttribute('tabindex');
		} else {
			form.classList.remove('is-open');
			toggleBtn.setAttribute('aria-expanded', 'false');
			input.setAttribute('tabindex', '-1');
		}
		updateToggleButton();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
