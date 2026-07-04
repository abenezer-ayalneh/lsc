(function () {
	'use strict';

	function activateTab(tabs, panels, activeTab) {
		tabs.forEach(function (tab) {
			var selected = tab === activeTab;
			var panel = document.getElementById(tab.getAttribute('aria-controls'));

			tab.setAttribute('aria-selected', selected ? 'true' : 'false');
			tab.setAttribute('tabindex', selected ? '0' : '-1');

			if (panel) {
				panel.hidden = !selected;
			}
		});

		panels.forEach(function (panel) {
			var controlsPanel = tabs.some(function (tab) {
				return tab.getAttribute('aria-controls') === panel.id;
			});

			if (!controlsPanel) {
				panel.hidden = true;
			}
		});
	}

	function initTabs(root) {
		var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
		var panels = Array.prototype.slice.call(root.querySelectorAll('[role="tabpanel"]'));

		if (!tabs.length || !panels.length) {
			return;
		}

		var selectedTab = tabs.find(function (tab) {
			return tab.getAttribute('aria-selected') === 'true';
		}) || tabs[0];

		activateTab(tabs, panels, selectedTab);

		tabs.forEach(function (tab, index) {
			tab.addEventListener('click', function () {
				activateTab(tabs, panels, tab);
			});

			tab.addEventListener('keydown', function (event) {
				var nextIndex = null;

				if ('ArrowRight' === event.key || 'ArrowDown' === event.key) {
					nextIndex = (index + 1) % tabs.length;
				}

				if ('ArrowLeft' === event.key || 'ArrowUp' === event.key) {
					nextIndex = (index - 1 + tabs.length) % tabs.length;
				}

				if ('Home' === event.key) {
					nextIndex = 0;
				}

				if ('End' === event.key) {
					nextIndex = tabs.length - 1;
				}

				if (null === nextIndex) {
					return;
				}

				event.preventDefault();
				tabs[nextIndex].focus();
				activateTab(tabs, panels, tabs[nextIndex]);
			});
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('[data-lsc-tabs]').forEach(initTabs);
	});
}());
