import React from "react";
import ReactGA from "react-ga4";

const GA_ID = "G-S056CLLZ34";

/**
 * React-GA4 Library and usage
 * {@link https://github.com/PriceRunner/react-ga4#readme}
 */
ReactGA.initialize(GA_ID, { gtagOptions: { anonymize_ip: true } });

export const GaContext = React.createContext(null);

export function GaProvider(props) {
	return (
		<GaProvider.Provider
			value={{
				sendEvent,
				sendPageView,
			}}
		>
			{props.children}
		</GaProvider.Provider>
	);
}

export const useGa = () => {
	return React.useContext(GaContext);
};

const sendEvent = (data) => {
	return ReactGA.event(data);
};

const sendPageView = (data) => {
	return ReactGA.send(data);
};
