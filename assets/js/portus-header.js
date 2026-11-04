/* Portus Header Base 2.3.4 */
(function () {
	'use strict';

	var i18n = window.PortusHeaderFrontend || {};

	function initialize() {
		var header = document.querySelector('[data-portus-header]');

		if (!header) {
			return;
		}

		document.body.classList.add('portus-header-is-rendered');

		var drawer = document.querySelector('[data-portus-menu-drawer]');
		var overlay = document.querySelector('[data-portus-menu-overlay]');
		var openButton = header.querySelector('[data-portus-menu-open]');
		var closeButton = drawer ? drawer.querySelector('[data-portus-menu-close]') : null;
		var lastFocused = null;
		var closeTimer = null;
		var ticking = false;

		function updateAdminBarOffset() {
			var adminBar = document.getElementById('wpadminbar');
			var offset = 0;

			if (adminBar) {
				offset = Math.max(0, Math.round(adminBar.getBoundingClientRect().bottom));
			}

			[header, drawer, overlay].forEach(function (element) {
				if (element) {
					element.style.setProperty('--portus-admin-bar-offset', offset + 'px');
				}
			});
			ticking = false;
		}

		function requestOffsetUpdate() {
			if (!ticking) {
				window.requestAnimationFrame(updateAdminBarOffset);
				ticking = true;
			}
		}

		function unlockScroll() {
			document.body.classList.remove('portus-header-scroll-lock');
			document.documentElement.classList.remove('portus-header-scroll-lock');
		}

		function lockScroll() {
			document.body.classList.add('portus-header-scroll-lock');
			document.documentElement.classList.add('portus-header-scroll-lock');
		}

		function setupDesktopMenuKeyboard() {
			var topItems = header.querySelectorAll('.portus-header__menu > .menu-item-has-children');

			topItems.forEach(function (item) {
				var trigger = Array.prototype.find.call(item.children, function (child) {
					return child.tagName === 'A';
				});
				var submenu = Array.prototype.find.call(item.children, function (child) {
					return child.classList && child.classList.contains('sub-menu');
				});

				if (!trigger || !submenu) {
					return;
				}

				trigger.setAttribute('aria-haspopup', 'true');
				trigger.setAttribute('aria-expanded', 'false');

				item.addEventListener('mouseenter', function () {
					item.classList.remove('is-keyboard-closed');
					trigger.setAttribute('aria-expanded', 'true');
				});
				item.addEventListener('mouseleave', function () {
					trigger.setAttribute('aria-expanded', 'false');
				});
				item.addEventListener('focusout', function (event) {
					if (!item.contains(event.relatedTarget)) {
						item.classList.remove('is-keyboard-closed');
						trigger.setAttribute('aria-expanded', 'false');
					}
				});
				item.addEventListener('keydown', function (event) {
					var links = submenu.querySelectorAll('a[href]');

					if (event.key === 'ArrowDown' && document.activeElement === trigger && links.length) {
						event.preventDefault();
						item.classList.remove('is-keyboard-closed');
						trigger.setAttribute('aria-expanded', 'true');
						links[0].focus();
					} else if (event.key === 'Escape') {
						event.preventDefault();
						trigger.focus();
						item.classList.add('is-keyboard-closed');
						trigger.setAttribute('aria-expanded', 'false');
					}
				});
			});
		}

		function getFocusableElements() {
			if (!drawer) {
				return [];
			}

			return Array.prototype.slice.call(
				drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')
			).filter(function (element) {
				return element.offsetParent !== null;
			});
		}

		function openDrawer(event) {
			if (!drawer || !overlay || !openButton || !closeButton) {
				return;
			}

			window.clearTimeout(closeTimer);
			lastFocused = event && event.currentTarget ? event.currentTarget : document.activeElement;
			drawer.hidden = false;
			overlay.hidden = false;
			drawer.setAttribute('aria-hidden', 'false');
			openButton.setAttribute('aria-expanded', 'true');
			openButton.setAttribute('aria-label', i18n.closeMenu || 'Fechar menu');
			lockScroll();
			updateAdminBarOffset();

			window.requestAnimationFrame(function () {
				drawer.classList.add('is-open');
				overlay.classList.add('is-open');
				closeButton.focus();
			});
		}

		function closeDrawer(restoreFocus) {
			var configuredDuration;

			if (!drawer || !overlay || !openButton) {
				return;
			}

			configuredDuration = parseFloat(
				window.getComputedStyle(drawer).getPropertyValue('--portus-header-drawer-animation-duration')
			) || 280;

			drawer.classList.remove('is-open');
			overlay.classList.remove('is-open');
			drawer.setAttribute('aria-hidden', 'true');
			openButton.setAttribute('aria-expanded', 'false');
			openButton.setAttribute('aria-label', i18n.openMenu || 'Abrir menu');
			closeTimer = window.setTimeout(function () {
				drawer.hidden = true;
				overlay.hidden = true;
				unlockScroll();
			}, configuredDuration + 30);

			if (restoreFocus && lastFocused && typeof lastFocused.focus === 'function') {
				lastFocused.focus();
			}
		}

		function setupSubmenus() {
			var items = drawer ? drawer.querySelectorAll('.menu-item-has-children') : [];

			function updateAncestorHeights(submenu) {
				var ancestor = submenu.parentElement.closest('.sub-menu.is-open');

				while (ancestor) {
					ancestor.style.maxHeight = ancestor.scrollHeight + 'px';
					ancestor = ancestor.parentElement.closest('.sub-menu.is-open');
				}
			}

			items.forEach(function (item, index) {
				var submenu = Array.prototype.find.call(item.children, function (child) {
					return child.classList && child.classList.contains('sub-menu');
				});
				var link = Array.prototype.find.call(item.children, function (child) {
					return child.tagName === 'A';
				});
				var label;
				var submenuId;
				var button;
				var icon;

				if (!submenu || !link) {
					return;
				}

				label = link.textContent.trim();
				submenuId = 'portus-mobile-submenu-' + index;
				button = document.createElement('button');
				icon = document.createElement('img');

				submenu.id = submenuId;
				submenu.style.maxHeight = '0px';
				button.type = 'button';
				button.className = 'portus-header-drawer__submenu-toggle';
				button.setAttribute('aria-expanded', 'false');
				button.setAttribute('aria-controls', submenuId);
				button.setAttribute('aria-label', (i18n.expandSubmenu || 'Expandir submenu:') + ' ' + label);
				icon.className = 'portus-header-drawer__submenu-icon';
				icon.src = i18n.submenuIcon || '';
				icon.alt = '';
				icon.setAttribute('aria-hidden', 'true');
				button.appendChild(icon);
				item.insertBefore(button, submenu);

				button.addEventListener('click', function () {
					var expanded = button.getAttribute('aria-expanded') === 'true';

					button.setAttribute('aria-expanded', expanded ? 'false' : 'true');
					button.setAttribute(
						'aria-label',
						(expanded ? (i18n.expandSubmenu || 'Expandir submenu:') : (i18n.collapseSubmenu || 'Recolher submenu:')) + ' ' + label
					);

					if (expanded) {
						submenu.style.maxHeight = submenu.scrollHeight + 'px';

						window.requestAnimationFrame(function () {
							submenu.classList.remove('is-open');
							submenu.style.maxHeight = '0px';
							updateAncestorHeights(submenu);
						});
					} else {
						submenu.classList.add('is-open');

						window.requestAnimationFrame(function () {
							submenu.style.maxHeight = submenu.scrollHeight + 'px';
							updateAncestorHeights(submenu);
						});
					}
				});
			});
		}

		updateAdminBarOffset();
		setupDesktopMenuKeyboard();

		if (drawer && overlay && openButton && closeButton) {
			setupSubmenus();
			openButton.addEventListener('click', openDrawer);
			closeButton.addEventListener('click', function () {
				closeDrawer(true);
			});
			overlay.addEventListener('click', function () {
				closeDrawer(true);
			});
			drawer.addEventListener('click', function (event) {
				if (event.target.closest('a[href]')) {
					closeDrawer(false);
				}
			});
			document.addEventListener('keydown', function (event) {
				var focusable;
				var first;
				var last;

				if (drawer.hidden) {
					return;
				}

				if (event.key === 'Escape') {
					event.preventDefault();
					closeDrawer(true);
					return;
				}

				if (event.key !== 'Tab') {
					return;
				}

				focusable = getFocusableElements();
				if (!focusable.length) {
					return;
				}

				first = focusable[0];
				last = focusable[focusable.length - 1];

				if (event.shiftKey && document.activeElement === first) {
					event.preventDefault();
					last.focus();
				} else if (!event.shiftKey && document.activeElement === last) {
					event.preventDefault();
					first.focus();
				}
			});
			window.addEventListener('resize', function () {
				drawer.querySelectorAll('.sub-menu.is-open').forEach(function (submenu) {
					submenu.style.maxHeight = submenu.scrollHeight + 'px';
				});

				if (window.innerWidth > 960 && !drawer.hidden) {
					closeDrawer(false);
				}
			}, { passive: true });
		}

		window.addEventListener('resize', requestOffsetUpdate, { passive: true });
		window.addEventListener('scroll', requestOffsetUpdate, { passive: true });
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialize);
	} else {
		initialize();
	}
}());
