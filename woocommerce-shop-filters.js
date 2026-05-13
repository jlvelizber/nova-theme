(function () {
	'use strict';

	var root = document.querySelector('[data-nova-shop-filters]');
	if (!root) {
		return;
	}

	var btn = root.querySelector('.nova-shop-filters__submit');
	var selects = root.querySelectorAll('select[data-filter-key]');
	var list = document.querySelector('ul.products.nova-products');
	var emptyMsg = root.querySelector('[data-nova-filter-empty]');

	if (!list || !btn) {
		return;
	}

	function getFilters() {
		var filters = {};
		selects.forEach(function (sel) {
			var key = sel.getAttribute('data-filter-key');
			if (key) {
				filters[key] = (sel.value || '').trim();
			}
		});
		return filters;
	}

	function productMatches(li, filters) {
		var keys = Object.keys(filters);
		for (var i = 0; i < keys.length; i++) {
			var key = keys[i];
			var wanted = filters[key];
			if (!wanted) {
				continue;
			}
			var raw = li.getAttribute('data-nova-' + key) || '';
			var slugs = raw.split(',').map(function (s) {
				return s.trim();
			}).filter(Boolean);
			if (slugs.indexOf(wanted) === -1) {
				return false;
			}
		}
		return true;
	}

	function applyFilters() {
		var filters = getFilters();
		var items = list.querySelectorAll('li.product');
		var visible = 0;

		items.forEach(function (li) {
			var show = productMatches(li, filters);
			li.style.display = show ? '' : 'none';
			if (show) {
				visible++;
			}
		});

		if (emptyMsg) {
			if (visible === 0 && items.length > 0) {
				emptyMsg.hidden = false;
				emptyMsg.classList.remove('screen-reader-text');
			} else {
				emptyMsg.hidden = true;
				emptyMsg.classList.add('screen-reader-text');
			}
		}
	}

	btn.addEventListener('click', function (e) {
		e.preventDefault();
		applyFilters();
	});
})();
