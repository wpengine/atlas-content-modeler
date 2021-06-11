const { wp } = window;

/**
 * Saves the time a user dismissed the feedback banner.
 */
function feedbackTrigger() {
	wp.apiFetch({
		path: "/wpe/atlas/dismiss-feedback-banner",
		method: "POST",
		_wpnonce: wpApiSettings.nonce,
	}).then((res) => {
		return;
	});
}

// bind to feedback banner buttons to trigger API call
window.addEventListener("DOMContentLoaded", (event) => {
	const feedbackDismissBtnClass = "notice-dismiss";
	const feedbackBtn = document.querySelector("#feedbackFormBtn");

	document.addEventListener("click", (event) => {
		if (
			event.target.id === feedbackBtn.id ||
			event.target.className === feedbackDismissBtnClass
		) {
			feedbackTrigger();
		}
	});
});
