import { getFieldOrder } from "../../../settings/js/src/queries";
const { postType, models } = atlasContentModelerFormEditingExperience;

/**
 * Adds a "submitted" class when the publish form is submitted.
 *
 * Allows styling of :invalid input fields only when the form was
 * submitted. Prevents an issue where error messages appear for
 * required fields when the form is first loaded.
 */
export const addClassOnFormSubmit = () => {
	const form = document.querySelector("form#post");
	const publishButton = document.querySelector("input#publish");
	const addSubmittedClass = () => form.classList.add("submitted");

	publishButton.addEventListener("click", addSubmittedClass);
	form.addEventListener("submit", addSubmittedClass);
};

/**
 * Puts focus on the first field.
 *
 * Allows the user to start interacting with the form immediately,
 * the same behavior as WordPress core post types.
 *
 * Acts when creating new posts, not when editing existing ones.
 */
export const focusFirstField = () => {
	if (!document.body.classList.contains("post-new-php")) {
		return;
	}

	const fieldOrder = getFieldOrder(models[postType]?.fields);
	const firstFieldId = fieldOrder[0] ?? false;
	const firstField = firstFieldId
		? models[postType]?.fields[firstFieldId]
		: false;

	if (!firstField) {
		return;
	}

	const { type, inputType } = firstField;

	if (type === "richtext") {
		return; // Rich Text field focus is handled in the useWPEditor hook.
	}

	let selector = "input";

	if (type === "text" && inputType === "multi") {
		selector = "textarea";
	}

	if (type === "media" || type === "relationship") {
		selector = "button";
	}

	const field = document.querySelector(
		`div[data-first-field=true] ${selector}`
	);

	if (field) {
		field.focus();
	}
};
