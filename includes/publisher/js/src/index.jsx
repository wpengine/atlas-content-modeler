import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "./../../scss/index.scss";

const container = document.getElementById("wpe-content-model-fields-app");

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	ReactDOM.render(<App model={model} />, container);

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
