/* global atlasContentModelerFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import GA4React, { useGA4React } from "ga-4-react";
import App from "./App";
import "./../../scss/index.scss";

// GA
const ga4react = new GA4React("G-S056CLLZ34");

const { models, postType } = atlasContentModelerFormEditingExperience;
const container = document.getElementById("atlas-content-modeler-fields-app");

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	const urlParams = new URLSearchParams(window.location.search);

	(async () => {
		await ga4react.initialize();

		ReactDOM.render(
			<React.StrictMode>
				<App model={model} mode={urlParams.get("action")} />
			</React.StrictMode>,
			container
		);
	})();
	/**
	 * Allows styling of :invalid input fields only when the form was
	 * submitted. Prevents an issue where error messages appear for
	 * required fields when the form is first loaded.
	 */
	const form = document.querySelector("form#post");
	const publishButton = document.querySelector("input#publish");
	const addSubmittedClass = () => form.classList.add("submitted");

	publishButton.addEventListener("click", addSubmittedClass);
	form.addEventListener("submit", addSubmittedClass);
}
