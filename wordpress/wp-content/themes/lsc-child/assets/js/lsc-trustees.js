(function () {
	var activeModal = null;
	var activeTrigger = null;

	function closeModal() {
		if (!activeModal) {
			return;
		}

		activeModal.hidden = true;
		document.body.classList.remove('lsc-trustee-modal-open');

		if (activeTrigger) {
			activeTrigger.focus();
		}

		activeModal = null;
		activeTrigger = null;
	}

	function openModal(trigger) {
		var id = trigger.getAttribute('data-lsc-trustee-open');
		var modal = document.querySelector('[data-lsc-trustee-modal="' + id + '"]');
		var panel = modal ? modal.querySelector('.lsc-trustee-modal__panel') : null;

		if (!modal || !panel) {
			return;
		}

		activeModal = modal;
		activeTrigger = trigger;
		modal.hidden = false;
		document.body.classList.add('lsc-trustee-modal-open');
		panel.focus();
	}

	document.addEventListener('click', function (event) {
		var trigger = event.target.closest('[data-lsc-trustee-open]');
		if (trigger) {
			openModal(trigger);
			return;
		}

		if (event.target.closest('[data-lsc-trustee-close]')) {
			closeModal();
		}
	});

	document.addEventListener('keydown', function (event) {
		if (event.key === 'Escape') {
			closeModal();
		}
	});
}());
