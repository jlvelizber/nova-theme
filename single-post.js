/**
 * Copy post URL to clipboard (share button).
 */
(function () {
	'use strict';

	document.addEventListener('click', function (event) {
		var btn = event.target.closest('.nova-post-share__btn--copy');
		if (!btn) {
			return;
		}

		var url = btn.getAttribute('data-copy-url');
		if (!url) {
			return;
		}

		event.preventDefault();

		function onCopied() {
			btn.setAttribute('aria-label', btn.getAttribute('data-copied-label') || 'Link copied');
			btn.classList.add('is-copied');
			window.setTimeout(function () {
				btn.classList.remove('is-copied');
			}, 2000);
		}

		if (navigator.clipboard && navigator.clipboard.writeText) {
			navigator.clipboard.writeText(url).then(onCopied).catch(function () {
				window.prompt('', url);
			});
			return;
		}

		var input = document.createElement('input');
		input.value = url;
		input.setAttribute('readonly', '');
		input.style.position = 'absolute';
		input.style.left = '-9999px';
		document.body.appendChild(input);
		input.select();
		try {
			document.execCommand('copy');
			onCopied();
		} catch (e) {
			window.prompt('', url);
		}
		document.body.removeChild(input);
	});
})();
