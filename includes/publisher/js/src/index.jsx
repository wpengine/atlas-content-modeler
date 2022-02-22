/* global atlasContentModelerFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import { registerPlugin } from "@wordpress/plugins";
import BlockEditorSidebar from "./components/sidebar/BlockEditorSidebar";
import App from "./App";
import "./../../scss/index.scss";
import "../../../settings/scss/index.scss";

const { models, postType } = atlasContentModelerFormEditingExperience;
const container = document.getElementById("atlas-content-modeler-fields-app");

if (container && models.hasOwnProperty(postType)) {
	const model = models[postType];
	const urlParams = new URLSearchParams(window.location.search);

	ReactDOM.render(
		<App model={model} mode={urlParams.get("action")} />,
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

if (wp?.plugins?.registerPlugin) {
	wp?.plugins?.registerPlugin("acm-sidebar", { render: BlockEditorSidebar });
}

// TODO: Set up the meta box for the Classic Editor here.
