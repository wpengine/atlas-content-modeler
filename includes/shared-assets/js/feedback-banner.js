const { wp } = window;

/**
 * Sets the time for the banner metadata
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
	const feedbackBtn = document.querySelector("#feedbackFormBtn");
	const feedbackBanner = document.querySelector("#feedbackBanner");
	let feedbackDismissBtn = null;
	if (feedbackBanner) {
		feedbackDismissBtn = feedbackBanner.getElementsByClassName(
			"notice-dismiss"
		);
	}

	document.addEventListener("click", (event) => {
		if (
			event.target === feedbackBtn ||
			(feedbackDismissBtn && feedbackDismissBtn.length)
		) {
			feedbackTrigger();
		}
	});
});
