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

jQuery(document).ready(function ($) {
	// on banner button click or dismissal, trigger feedback meta api call
	$(document).on(
		"click",
		"#feedbackBanner .notice-dismiss, #feedbackFormBtn",
		function () {
			feedbackTrigger();
		}
	);
});
