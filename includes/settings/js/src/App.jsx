import React, { useEffect } from "react";
import { BrowserRouter as Router } from "react-router-dom";
import { ToastContainer, Flip } from "react-toastify";
import { sendPageView } from "acm-analytics";
import "react-toastify/dist/ReactToastify.css";

import CreateContentModel from "./components/CreateContentModel.jsx";
import ViewContentModelsList from "./components/ViewContentModelsList";
import EditContentModel from "./components/EditContentModel";
import Taxonomies from "./components/Taxonomies";
import { useLocationSearch } from "./utils";
import { ModelsContextProvider } from "./ModelsContext";

export default function App() {
	useEffect(() => {
		sendPageView("ACM Models Home");
	}, []);

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
