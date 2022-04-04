/* global atlasContentModelerFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "./../../scss/index.scss";
import "../../../settings/scss/index.scss";

const { models, postType } = atlasContentModelerFormEditingExperience;
const container = document.getElementById("atlas-content-modeler-fields-app");

/**
 * Adds a "submitted" class when the publish form is submitted.
 *
 * Allows styling of :invalid input fields only when the form was
 * submitted. Prevents an issue where error messages appear for
 * required fields when the form is first loaded.
 */
const addClassOnFormSubmit = () => {
	const form = document.querySelector("form#post");
	const publishButton = document.querySelector("input#publish");
	const addSubmittedClass = () => form.classList.add("submitted");

	publishButton.addEventListener("click", addSubmittedClass);
	form.addEventListener("submit", addSubmittedClass);
};

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	const urlParams = new URLSearchParams(window.location.search);

	ReactDOM.render(
		<App model={model} mode={urlParams.get("action")} />,
		container
	);

	addClassOnFormSubmit();
}
