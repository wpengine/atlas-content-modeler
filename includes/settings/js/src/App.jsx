import React from "react";
import { BrowserRouter as Router } from "react-router-dom";
import { ToastContainer, Flip } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

import CreateContentModel from "./components/CreateContentModel.jsx";
import ViewContentModelsList from "./components/ViewContentModelsList";
import EditContentModel from "./components/EditContentModel";
import { useLocationSearch } from "./utils";
import { ModelsContextProvider } from "./ModelsContext";

export default function App() {
	return (
		<div className="app wpe-content-model">
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

	return <ViewContentModelsList />;
}
