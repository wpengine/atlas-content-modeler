import React from "react";
import Fields from "./components/Fields";

export default function App({ model }) {
	return (
		<div className="app classic-form">
			<Fields model={model} />
		</div>
	);
}
