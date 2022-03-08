/* global atlasContentModelerFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import { ThemeProvider } from "@emotion/react";
import "./../../scss/index.scss";
import "../../../settings/scss/index.scss";
import theme from "../../../shared-assets/js/theme";

const { models, postType } = atlasContentModelerFormEditingExperience;
const container = document.getElementById("atlas-content-modeler-fields-app");

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	const urlParams = new URLSearchParams(window.location.search);

	ReactDOM.render(
		<ThemeProvider theme={theme}>
			<App model={model} mode={urlParams.get("action")} />
		</ThemeProvider>,
		container
	);

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
