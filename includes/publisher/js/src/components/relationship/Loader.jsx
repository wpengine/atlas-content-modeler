import React from "react";
import { __ } from "@wordpress/i18n";

const Loader = () => {
	return (
		<div
			className="loader"
			aria-label={__("Loading…", "atlas-content-modeler")}
		></div>
	);
};

export default Loader;
