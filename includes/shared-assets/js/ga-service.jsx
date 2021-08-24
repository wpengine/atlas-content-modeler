import React from "react";
import ReactGA from "react-ga4";

const GA_ID = "G-S056CLLZ34";

/**
 * React-GA4 Library and usage
 * {@link https://github.com/PriceRunner/react-ga4#readme}
 */
ReactGA.initialize(GA_ID, { gtagOptions: { anonymize_ip: true } });

const { createContext, useContext } = React;

const GaContext = createContext(null);

export const GaProvider = (props) => {
	const value = {
		sendEvent: props.sendEvent || sendEvent,
		sendPageView: props.sendPageView || sendPageView,
	};

	return (
		<GaProvider.Provider value={value}>
			{props.children}
		</GaProvider.Provider>
	);
};

export const useGa = () => {
	return useContext(GaContext);
};

const sendEvent = (data) => {
	return ReactGA.event(data);
};

const sendPageView = (data) => {
	return ReactGA.send(data);
};
