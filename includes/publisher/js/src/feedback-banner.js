// global wpeContentModelFormEditingExperience
const { wp } = window;

/**
 * Sets the time for the banner metadata
 */
function feedbackTrigger() {
	wp.apiFetch({
		path: "/wpe/feedback-meta",
		method: "POST",
		_wpnonce: wpApiSettings.nonce,
	}).then((res) => {
		return;
	});
}
