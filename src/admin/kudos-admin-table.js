(() => {
	const cancelButtons = document.querySelectorAll('.row-actions .cancel');
	const deleteButtons = document.querySelectorAll('.row-actions .delete');

	cancelButtons.forEach((button) => {
		button.addEventListener('click', (e) => {
			if (!window.confirm(window.kudos.confirmationCancel)) {
				e.preventDefault();
			}
		});
	});

	deleteButtons.forEach((button) => {
		button.addEventListener('click', (e) => {
			if (!window.confirm(window.kudos.confirmationDelete)) {
				e.preventDefault();
			}
		});
	});
})();
