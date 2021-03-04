import React from "react";
import {
	BrowserRouter as Router,
	Link,
	useLocation,
} from "react-router-dom";

import CreateContentModel from './components/CreateContentModel.jsx';

export default function App() {
	return (
		<Router>
			<ViewTemplate/>
		</Router>
	);
}

/**
 * Parses query string and returns value.
 *
 * @credit https://reactrouter.com/web/example/query-parameters
 * @returns {URLSearchParams}
 */
function useLocationSearch() {
	return new URLSearchParams( useLocation().search );
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

function ViewContentModelsList() {
	return (
		<Link className="button button-primary" to="/wp-admin/admin.php?page=wpe-content-model&view=create-model">Add New</Link>
	);
}
