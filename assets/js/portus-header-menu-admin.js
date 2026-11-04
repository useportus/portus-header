(function () {
	'use strict';

	document.addEventListener('click', function (event) {
		var selectButton = event.target.closest('[data-portus-menu-media-select]');
		var removeButton = event.target.closest('[data-portus-menu-media-remove]');
		var field = event.target.closest('[data-portus-menu-media]');

		if (!field || (!selectButton && !removeButton)) {
			return;
		}

		var input = field.querySelector('[data-portus-menu-media-id]');
		var name = field.querySelector('[data-portus-menu-media-name]');

		if (removeButton) {
			input.value = '';
			name.textContent = '';
			removeButton.hidden = true;
			return;
		}

		if (!window.wp || !window.wp.media) {
			return;
		}

		var frame = window.wp.media({
			title: 'Selecionar imagem',
			button: { text: 'Usar esta imagem' },
			library: { type: 'image' },
			multiple: false
		});

		frame.on('select', function () {
			var attachment = frame.state().get('selection').first().toJSON();
			input.value = attachment.id;
			name.textContent = attachment.title || attachment.filename || '';
			removeButton.hidden = false;
		});
		frame.open();
	});
}());
