/**
 * Analytics helpers.
 *
 * {@link https://github.com/PriceRunner/react-ga4#readme}
 */
import ReactGA from "react-ga4";

const ANALYTICS_ID = "G-S056CLLZ34";

const telemetryEnabled = () => {
	return (
		(typeof atlasContentModelerFormEditingExperience !== "undefined" &&
			atlasContentModelerFormEditingExperience?.usageTrackingEnabled) ||
		(typeof atlasContentModeler !== "undefined" &&
			atlasContentModeler?.usageTrackingEnabled)
	);
};

const maybeInitializeAnalytics = () => {
	if (telemetryEnabled() && !ReactGA?.isInitialized) {
		ReactGA.initialize(ANALYTICS_ID, {
			gtagOptions: { anonymize_ip: true },
		});
	}
};

/**
 * Checks to see if we should send GA event or not for specific conditions
 * @returns bool
 */
const isExcluded = () => {
	// exclude ga event or page view if domain is wpengine.com
	const excludeRegex = /.+\.wpengine\.com/gi;
	return excludeRegex.test(window.location.href);
};

/**
 * Checks for tracking setting and exclusions
 * @returns bool
 */
const shouldTrack = () => {
	return !isExcluded() && telemetryEnabled();
};

/**
 * Send google analytics event
 * @param {*} data
 * @returns
 */
export const sendEvent = (data) => {
	if (shouldTrack()) {
		maybeInitializeAnalytics();
		return ReactGA.event(data);
	}
};

/**
 * Send google analytics page view
 * @param {*} page
 * @returns
 */
export const sendPageView = (page = "") => {
	if (shouldTrack()) {
		maybeInitializeAnalytics();
		return ReactGA.send({
			hitType: "pageview",
			page,
		});
	}
};

module.exports = {
	sendEvent,
	sendPageView,
	telemetryEnabled,
	shouldTrack,
	isExcluded,
};
