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
	if (!ReactGA?.isInitialized) {
		ReactGA.initialize(ANALYTICS_ID, {
			gtagOptions: { anonymize_ip: true },
		});
	}
};

export const sendEvent = (data) => {
	maybeInitializeAnalytics();
	if (telemetryEnabled) {
		return ReactGA.event(data);
	}
};

export const sendPageView = (page = "") => {
	maybeInitializeAnalytics();
	if (telemetryEnabled) {
		return ReactGA.send({
			hitType: "pageview",
			page,
		});
	}
};
