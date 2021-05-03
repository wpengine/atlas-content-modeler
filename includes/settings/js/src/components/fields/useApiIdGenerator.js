import { useState } from "react";
import { toValidApiId } from "./toValidApiId";

/**
 * Automatically updates the value of the `apiFieldId` field with a valid
 * GraphQL name generated from the value of the `inputFieldId` field.
 *
 * Updates take place as long as:
 * - The form is not presenting stored data for editing. (So that the user has
 *   to explicitly edit existing API IDs if they intend them to change.)
 * - The `apiFieldId` field has not been manually edited. (So that editing the
 *   name after editing the API ID does not remove changes to the API ID.)
 *
 * @param {string} apiFieldId The HTML id attribute of the API ID field.
 * @param {string} inputFieldId The HTML id attribute of the field that is used
 *                              to generate the API ID.
 * @param {Object} storedData Saved data used to set initial input value state.
 * @param {boolean} editing Is the form showing stored data for editing?
 * @param {function} setValue The function to call to update the value of the
 *                            API ID field.
 * @return {Object} Where `setApiIdGeneratorInput` should pass the current input
 *                  field value to the generator, and `apiIdFieldAttributes`
 *                  should be spread in the API ID input field.
 */
export function useApiIdGenerator({
	apiFieldId = "slug",
	inputFieldId = "name",
	storedData = {},
	editing = false,
	setValue,
}) {
	const [input, setApiIdGeneratorInput] = useState(
		storedData[inputFieldId] || ""
	);

	// The name and API ID fields are “linked” if typing into the name field
	// should update the value of the API ID field.
	const [fieldsAreLinked, setFieldsAreLinked] = useState(!editing);

	if (fieldsAreLinked) {
		setValue(apiFieldId, toValidApiId(input));
	}

	return {
		setApiIdGeneratorInput,
		apiIdFieldAttributes: {
			onChange: (event) => {
				// Unlinks fields if the user edits the API ID field. So that
				// users who edit the API ID field and then the name field do
				// not have their changes to the API ID field overwritten.
				setFieldsAreLinked(false);
				// Prevents invalid values being entered into the API ID field.
				// An alternative is to show a validation error, but that forces
				// the user to understand legal GraphQL name formats.
				setValue(apiFieldId, toValidApiId(event.target.value));
			},
		},
	};
}
