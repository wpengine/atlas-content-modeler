import React from "react";
import { BrowserRouter as Router } from "react-router-dom";

import CreateContentModel from './components/CreateContentModel.jsx';
import ViewContentModelsList from "./components/ViewContentModelsList";
import EditContentModel from "./components/EditContentModel";
import { useLocationSearch } from "./utils";
import { ModelsContextProvider } from "./ModelsContext";

export default function App() {
	return (
		<div className="app">
			<ModelsContextProvider>
				<Router>
					<ViewTemplate/>
				</Router>
			</ModelsContextProvider>
		</div>
	);
}

function ViewTemplate() {
	const query = useLocationSearch();
	const view = query.get( 'view' );

	if ( view === 'create-model' ) {
		return <CreateContentModel />
	}

	if ( view === 'edit-model' ) {
		return <EditContentModel />
	}

	return <ViewContentModelsList />
}
