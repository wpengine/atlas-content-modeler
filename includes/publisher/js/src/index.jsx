/* global wpeContentModelFormEditingExperience */
import React from "react";
import ReactDOM from "react-dom";
import App from "./App";

const { models, postType } = wpeContentModelFormEditingExperience;

const container = document.getElementById("wpe-content-model-fields-app");

if (!container) {
	return;
}

if (!models.hasOwnProperty(postType)) {
	return;
}

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

	richTextFields.forEach((field) =>
		tinymce.execCommand("mceAddEditor", false, field)
	);
});

jQuery(document).ready(function($){
	$('.wpe-upload-btn').click(function(e) {
		e.preventDefault();
		var btnTarget = $(e.target);
		var image = wp.media({
			title: 'Upload Media',
			// multiple: true if you want to upload multiple files at once
			multiple: false
		}).open()
			.on('select', function(e){
				console.log('button', btnTarget);
				console.log('on select', e);
				// This will return the selected image from the Media Uploader, the result is an object
				var uploaded_image = image.state().get('selection').first();
				// We convert uploaded_image to a JSON object to make accessing it easier
				// Output to the console uploaded_image
				console.log(uploaded_image);
				var image_url = uploaded_image.toJSON().url;
				// Let's assign the url value to the input field
				$('').val(image_url);
			});
	});
});
