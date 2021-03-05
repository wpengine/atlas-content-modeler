import React from "react";
import { BrowserRouter as Router } from "react-router-dom";

import CreateContentModel from './components/CreateContentModel.jsx';
import ViewContentModelsList from "./components/ViewContentModelsList";
import EditContentModel from "./components/EditContentModel";
import { useLocationSearch } from "./utils";

export default function App() {
	return (
		<div className="app">
			<Router>
				<ViewTemplate/>
			</Router>
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
		return ( <p>edit-model goes here.</p> );
	}

	return <ViewContentModelsList />
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
