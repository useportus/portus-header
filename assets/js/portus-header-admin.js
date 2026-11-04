(function () {
	'use strict';

	var config = window.PortusHeaderAdmin || { defaults: {}, presets: {}, i18n: {} };
	var sizeKeys = [
		'container_max_width',
		'desktop_height',
		'mobile_height',
		'desktop_logo_width',
		'desktop_logo_height',
		'mobile_logo_width',
		'mobile_logo_height',
		'menu_gap',
		'cta_radius',
		'submenu_width',
		'drawer_width',
		'drawer_padding',
		'menu_icon_size',
		'close_icon_size',
		'toggle_button_size',
		'desktop_menu_font_size',
		'mobile_menu_font_size',
		'submenu_font_size',
		'cta_font_size',
		'menu_font_weight',
		'desktop_menu_font_weight',
		'mobile_menu_font_weight',
		'desktop_menu_letter_spacing',
		'mobile_menu_letter_spacing',
		'submenu_icon_size',
		'submenu_animation_duration',
		'drawer_animation_duration',
		'search_icon_size',
		'account_icon_size',
		'cart_icon_size',
		'header_action_gap',
		'search_panel_width',
		'search_button_animation_duration',
		'search_result_image_size',
		'search_result_gap',
		'search_result_radius',
		'search_result_max_height',
		'search_result_name_size',
		'search_result_meta_size',
		'search_view_all_radius',
		'mega_menu_width',
		'mega_menu_gap',
		'mega_menu_padding',
		'mega_menu_radius',
		'mega_menu_image_height',
		'mega_menu_animation_duration',
		'account_dropdown_width',
		'panel_radius',
		'scroll_threshold',
		'scrolled_height',
		'transparent_opacity'
	];
	var visualKeys = sizeKeys.concat([
		'font_family',
		'layout_preset',
		'mega_menu_preset'
	]);
	var layoutLabels = {
		standard: 'Portus padrão',
		compact: 'Compacto',
		centered: 'Centralizado',
		cta: 'CTA forte'
	};

	function initialize() {
		var tabs = document.querySelectorAll('[data-portus-admin-tab]');
		var sections = document.querySelectorAll('[data-portus-admin-section]');
		var preview = document.querySelector('[data-portus-live-preview]');
		var params = new URLSearchParams(window.location.search || '');

		function showTab(name, updateUrl) {
			var hasTab = false;

			tabs.forEach(function (tab) {
				if (tab.getAttribute('data-portus-admin-tab') === name) {
					hasTab = true;
				}
			});

			if (!hasTab) {
				name = 'general';
			}

			tabs.forEach(function (tab) {
				var active = tab.getAttribute('data-portus-admin-tab') === name;
				tab.classList.toggle('is-active', active);
				tab.setAttribute('aria-selected', active ? 'true' : 'false');
			});
			sections.forEach(function (section) {
				section.hidden = section.getAttribute('data-portus-admin-section') !== name;
			});

			if (updateUrl && window.history && window.history.replaceState) {
				params.set('portus_header_tab', name);
				window.history.replaceState({}, '', window.location.pathname + '?' + params.toString());
			}
		}

		tabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				showTab(tab.getAttribute('data-portus-admin-tab'), true);
			});
		});

		showTab(params.get('portus_header_tab') || 'general', false);

		function findSetting(key) {
			return document.querySelector('[name$="[' + key + ']"]');
		}

		function updateColorLabel(input) {
			var value = input && input.parentElement ? input.parentElement.querySelector('[data-color-value]') : null;
			if (value) {
				value.textContent = String(input.value || '').toUpperCase();
			}
		}

		function emitFieldChange(input) {
			input.dispatchEvent(new Event('change', { bubbles: true }));
			input.dispatchEvent(new Event('input', { bubbles: true }));
		}

		function setFieldValue(key, value) {
			var input = findSetting(key);

			if (!input || value === undefined || value === null) {
				return;
			}

			if (input.type === 'checkbox') {
				input.checked = Boolean(Number(value));
			} else {
				input.value = value;
			}

			if (input.type === 'color') {
				updateColorLabel(input);
			}

			emitFieldChange(input);
		}

		function applySettings(settings) {
			Object.keys(settings || {}).forEach(function (key) {
				setFieldValue(key, settings[key]);
			});
			updatePreview();
		}

		function updateLiveLogo(source) {
			var logo = document.querySelector('[data-portus-preview-logo]');
			var image;
			var fallback;

			if (!logo) {
				return;
			}

			logo.innerHTML = '';
			if (source) {
				image = document.createElement('img');
				image.src = source;
				image.alt = '';
				logo.appendChild(image);
				return;
			}

			fallback = document.createElement('span');
			fallback.className = 'portus-header-admin__preview-logo-fallback';
			fallback.innerHTML = '<b>Portus</b>';
			logo.appendChild(fallback);
		}

		function getSettingValue(key, fallback) {
			var input = findSetting(key);

			if (!input) {
				return fallback;
			}

			if (input.type === 'checkbox') {
				return input.checked ? 1 : 0;
			}

			return input.value || fallback;
		}

		function updatePreview() {
			if (!preview) {
				return;
			}

			var layout = getSettingValue('layout_preset', 'standard');
			var ctaVisible = Boolean(Number(getSettingValue('cta_enabled', 1)));
			var cta = preview.querySelector('[data-portus-preview-cta]');
			var layoutLabel = preview.querySelector('[data-portus-preview-layout-label]');

			preview.style.setProperty('--preview-bg', getSettingValue('color_header_bg', '#00053A'));
			preview.style.setProperty('--preview-text', getSettingValue('color_text', '#FFFFFF'));
			preview.style.setProperty('--preview-hover', getSettingValue('color_hover', '#C0A821'));
			preview.style.setProperty('--preview-cta-bg', getSettingValue('color_cta_bg', '#C0A821'));
			preview.style.setProperty('--preview-cta-text', getSettingValue('color_cta_text', '#00053A'));
			preview.style.setProperty('--preview-cta-hover-bg', getSettingValue('color_cta_hover_bg', '#D3C45A'));
			preview.style.setProperty('--preview-search-icon', getSettingValue('color_search_icon', '#C0A821'));
			preview.style.setProperty('--preview-account-icon', getSettingValue('color_account_icon', '#C0A821'));
			preview.style.setProperty('--preview-cart-icon', getSettingValue('color_cart_icon', '#C0A821'));
			preview.style.setProperty('--preview-height', getSettingValue('desktop_height', 82) + 'px');
			preview.style.setProperty('--preview-menu-gap', getSettingValue('menu_gap', 30) + 'px');
			preview.style.setProperty('--preview-action-gap', getSettingValue('header_action_gap', 10) + 'px');
			preview.style.setProperty('--preview-radius', getSettingValue('cta_radius', 6) + 'px');
			preview.style.setProperty('--preview-font-size', getSettingValue('desktop_menu_font_size', 15) + 'px');
			preview.setAttribute('data-preview-layout', layout);

			if (layoutLabel) {
				layoutLabel.textContent = layoutLabels[layout] || layout;
			}

			if (cta) {
				cta.hidden = !ctaVisible;
				cta.textContent = getSettingValue('cta_text', 'CTA') || 'CTA';
			}
		}

		document.querySelectorAll('[data-portus-color]').forEach(function (input) {
			input.addEventListener('input', function () {
				updateColorLabel(input);
				updatePreview();
			});
		});

		document.querySelectorAll('.portus-header-admin input, .portus-header-admin select, .portus-header-admin textarea').forEach(function (input) {
			input.addEventListener('change', updatePreview);
			input.addEventListener('input', updatePreview);
		});

		document.querySelectorAll('[data-portus-preset]').forEach(function (button) {
			button.addEventListener('click', function () {
				var key = button.getAttribute('data-portus-preset');
				var preset = config.presets && config.presets[key] ? config.presets[key] : null;

				if (!preset || !preset.settings) {
					return;
				}

				if (!window.confirm(config.i18n.preset || 'Aplicar este preset visual?')) {
					return;
				}

				applySettings(preset.settings);
			});
		});

		document.querySelectorAll('[data-portus-restore-group]').forEach(function (button) {
			button.addEventListener('click', function () {
				var group = button.getAttribute('data-portus-restore-group');
				var keys = [];
				var message = config.i18n.restoreVisual || 'Restaurar visual Portus?';

				if (group === 'colors') {
					message = config.i18n.restoreColors || message;
					keys = Object.keys(config.defaults || {}).filter(function (key) {
						return key.indexOf('color_') === 0;
					});
				} else if (group === 'sizes') {
					message = config.i18n.restoreSizes || message;
					keys = sizeKeys.slice();
				} else {
					keys = Object.keys(config.defaults || {}).filter(function (key) {
						return key.indexOf('color_') === 0 || visualKeys.indexOf(key) !== -1;
					});
				}

				if (!window.confirm(message)) {
					return;
				}

				keys.forEach(function (key) {
					if (config.defaults[key] !== undefined) {
						setFieldValue(key, config.defaults[key]);
					}
				});
				updatePreview();
			});
		});

		function collectSettings() {
			var settings = {};
			document.querySelectorAll('[name^="portus_header_settings["]').forEach(function (input) {
				var match = input.name.match(/\[([^\]]+)\]$/);
				if (match) {
					settings[match[1]] = input.type === 'checkbox' ? (input.checked ? 1 : 0) : input.value;
				}
			});
			return settings;
		}

		var exportButton = document.querySelector('[data-portus-export]');
		if (exportButton) {
			exportButton.addEventListener('click', function () {
				var payload = {
					plugin: 'portus-header',
					version: '2.3.9',
					exportedAt: new Date().toISOString(),
					settings: collectSettings()
				};
				var blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
				var url = URL.createObjectURL(blob);
				var link = document.createElement('a');
				link.href = url;
				link.download = 'portus-header-config.json';
				document.body.appendChild(link);
				link.click();
				link.remove();
				URL.revokeObjectURL(url);
			});
		}

		var importInput = document.querySelector('[data-portus-import]');
		if (importInput) {
			importInput.addEventListener('change', function () {
				var file = importInput.files && importInput.files[0];
				if (!file) {
					return;
				}

				var reader = new FileReader();
				reader.onload = function () {
					try {
						var payload = JSON.parse(reader.result);
						var settings = payload && payload.plugin === 'portus-header' ? payload.settings : null;
						if (!settings || typeof settings !== 'object') {
							throw new Error('invalid');
						}

						Object.keys(settings).forEach(function (key) {
							setFieldValue(key, settings[key]);
						});

						window.alert(config.i18n.importDone || 'Configurações importadas.');
						updatePreview();
					} catch (error) {
						window.alert(config.i18n.importError || 'Arquivo inválido.');
					}
					importInput.value = '';
				};
				reader.readAsText(file);
			});
		}

		document.querySelectorAll('[data-media-field]').forEach(function (field) {
			var selectButton = field.querySelector('[data-media-select]');
			var removeButton = field.querySelector('[data-media-remove]');
			var idInput = field.querySelector('[data-media-id]');
			var previewElement = field.querySelector('[data-media-preview]');
			var mediaKey = field.getAttribute('data-media-key');
			var frame = null;

			if (!selectButton || !removeButton || !idInput || !previewElement || !window.wp || !window.wp.media) {
				return;
			}

			selectButton.addEventListener('click', function () {
				if (frame) {
					frame.open();
					return;
				}

				frame = window.wp.media({
					title: config.i18n.mediaTitle || 'Selecionar logo',
					button: { text: config.i18n.mediaButton || 'Usar esta imagem' },
					library: { type: 'image' },
					multiple: false
				});

				frame.on('select', function () {
					var attachment = frame.state().get('selection').first().toJSON();
					var source = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;

					idInput.value = attachment.id;
					previewElement.innerHTML = '';
					var image = document.createElement('img');
					image.src = source;
					image.alt = '';
					previewElement.appendChild(image);
					previewElement.hidden = false;
					removeButton.hidden = false;
					if (mediaKey === 'desktop_logo_id') {
						updateLiveLogo(source);
					}
					emitFieldChange(idInput);
				});

				frame.open();
			});

			removeButton.addEventListener('click', function () {
				idInput.value = '';
				previewElement.innerHTML = '';
				previewElement.hidden = true;
				removeButton.hidden = true;
				if (mediaKey === 'desktop_logo_id') {
					updateLiveLogo('');
				}
				emitFieldChange(idInput);
			});
		});

		updatePreview();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initialize);
	} else {
		initialize();
	}
}());
