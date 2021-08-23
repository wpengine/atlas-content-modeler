import React from "react";
import ReactGA from "react-ga4";
import { BrowserRouter as Router } from "react-router-dom";
import { ToastContainer, Flip } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

import CreateContentModel from "./components/CreateContentModel.jsx";
import ViewContentModelsList from "./components/ViewContentModelsList";
import EditContentModel from "./components/EditContentModel";
import Taxonomies from "./components/Taxonomies";
import { useLocationSearch } from "./utils";
import { ModelsContextProvider } from "./ModelsContext";

const GA_ID = "G-S056CLLZ34";

/**
 * React-GA4 Library and usage
 * {@link https://github.com/PriceRunner/react-ga4#readme}
 *
 * Page send
 * ReactGA.send("pageview"); OR
 * ReactGA.send({ hitType: "pageview", page: "/my-path" });
 *
 * Send custom event
 * ReactGA.event({
 *	category: "your category",
 *	action: "your action",
 *	label: "your label", // optional
 *	value: 99, // optional, must be a number
 *	nonInteraction: true, // optional, true/fals
 *	transport: "xhr", // optional, beacon/xhr/image
 *	});
 */
ReactGA.initialize(GA_ID, { gtagOptions: { anonymize_ip: true } });

export default function App() {
	return (
		<div className="app atlas-content-modeler">
			<ModelsContextProvider>
				<Router>
					<ToastContainer
						autoClose={2000}
						draggable={false}
						position="top-center"
						transition={Flip}
					/>
					<ViewTemplate />
				</Router>
			</ModelsContextProvider>
		</div>
	);
}

function ViewTemplate() {
	const query = useLocationSearch();
	const view = query.get("view");

	if (view === "create-model") {
		return <CreateContentModel />;
	}

	if (view === "edit-model") {
		return <EditContentModel />;
	}

	if (view === "taxonomies") {
		return <Taxonomies />;
	}

	return <ViewContentModelsList />;
}
