/* global atlasContentModelerFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "./../../scss/index.scss";
import "../../../settings/scss/index.scss";
import { addClassOnFormSubmit, focusFirstField } from "./publisherFormActions";

const { models, postType } = atlasContentModelerFormEditingExperience;
const container = document.getElementById("atlas-content-modeler-fields-app");

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	const urlParams = new URLSearchParams(window.location.search);

	ReactDOM.render(
		<App model={model} mode={urlParams.get("action")} />,
		container
	);

	addClassOnFormSubmit();
	focusFirstField();
}
