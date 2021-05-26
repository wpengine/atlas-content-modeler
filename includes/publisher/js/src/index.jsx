/* global wpeContentModelFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "./../../scss/index.scss";

const { models, postType } = wpeContentModelFormEditingExperience;
const container = document.getElementById("wpe-content-model-fields-app");
const { wp } = window;

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	ReactDOM.render(<App model={model} />, container);
	// Add TinyMCE to rich text fields.
	// @todo use wp.oldEditor instead of tinymce directly? Move this code to proper script file.
	window.addEventListener("DOMContentLoaded", (event) => {
		if (
			!wpeContentModelFormEditingExperience?.models ||
			!wpeContentModelFormEditingExperience?.models[
				wpeContentModelFormEditingExperience.postType
			]
		) {
			return;
		}
		const richTextFields = document.querySelectorAll(".richtext textarea");
		if (!richTextFields.length > 0) {
			return;
		}

		const defaultTinymceSettings = wp.editor.getDefaultSettings();

		defaultTinymceSettings.tinymce.toolbar1 =
			"undo redo | styleselect | bold, italic | bullist, numlist | blockquote | alignleft, aligncenter, alignright | link unlink";

		console.log(defaultTinymceSettings);

		richTextFields.forEach((field) => {
			//wp.editor.remove(field.getAttribute("id"));
			wp.editor.initialize(
				field.getAttribute("id"),
				defaultTinymceSettings
			);
		});
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
