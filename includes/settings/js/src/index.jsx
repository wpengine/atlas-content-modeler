import React from "react";
import ReactDOM from "react-dom";
import GA4React, { useGA4React } from "ga-4-react";
import App from "./App";
import "../../scss/index.scss";

// GA
const ga4react = new GA4React("G-S056CLLZ34");

(async () => {
	await ga4react.initialize();

	ReactDOM.render(
		<React.StrictMode>
			<App />
		</React.StrictMode>,
		document.getElementById("root")
	);
})();
