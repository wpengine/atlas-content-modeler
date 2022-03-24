/**
 * Analytics helpers.
 *
 * {@link https://github.com/PriceRunner/react-ga4#readme}
 */
import ReactGA from "react-ga4";

const ANALYTICS_ID = "G-S056CLLZ34";

const telemetryEnabled =
	(typeof atlasContentModelerFormEditingExperience !== "undefined" &&
		atlasContentModelerFormEditingExperience?.usageTrackingEnabled) ||
	(typeof atlasContentModeler !== "undefined" &&
		atlasContentModeler?.usageTrackingEnabled);

const maybeInitializeAnalytics = () => {
	if (telemetryEnabled && !ReactGA?.isInitialized) {
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
	const excludeRegex = /.+\.wpengine\.com/gi;
	return excludeRegex.test(window.location.href);
};

export const sendEvent = (data) => {
	if (telemetryEnabled && !isExcluded()) {
		maybeInitializeAnalytics();
		return ReactGA.event(data);
	}
};

export const sendPageView = (page = "") => {
	if (telemetryEnabled && !isExcluded()) {
		maybeInitializeAnalytics();
		return ReactGA.send({
			hitType: "pageview",
			page,
		});
	}
};
