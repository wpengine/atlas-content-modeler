/* global atlasContentModelerFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "./../../scss/index.scss";

const { models, postType } = atlasContentModelerFormEditingExperience;
const container = document.getElementById("atlas-content-modeler-fields-app");

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	ReactDOM.render(<App model={model} />, container);
	// Add TinyMCE to rich text fields.
	// @todo use wp.oldEditor instead of tinymce directly? Move this code to proper script file.
	window.addEventListener("DOMContentLoaded", (event) => {
		if (
			!atlasContentModelerFormEditingExperience?.models ||
			!atlasContentModelerFormEditingExperience?.models[
				atlasContentModelerFormEditingExperience.postType
			]
		) {
			return;
		}
		const richTextFields = document.querySelectorAll(".richtext textarea");
		if (!richTextFields.length > 0) {
			return;
		}
		richTextFields.forEach((field) =>
			tinymce.execCommand("mceAddEditor", false, field)
		);
	});

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
