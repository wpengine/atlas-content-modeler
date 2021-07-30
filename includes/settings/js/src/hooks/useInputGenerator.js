import { useState } from "react";
import { toValidApiId } from "../formats";

/**
 * Uses the value of a source input to generate and apply a valid value
 * to a another input.
 *
 * Updates take place as long as:
 * - The form is not presenting stored data for editing. (So that the user has
 *   to explicitly edit generated field input if they intend it to change.)
 * - The generated field has not been manually edited. (So that editing the
 *   source field after editing the generated field does not remove changes to the
 *   generated field.)
 *
 * @param {string}   sourceValue       The initial value of the source input field.
 * @param {boolean}  editing           Is the form showing stored data for editing?
 * @param {function} setGeneratedValue Function for updating the value of the generated field.
 * @return {Object}                    Where `setInputGeneratorSourceValue` should pass the source input
 *                                     field's value whenever it is updated, and
 *                                     `onChangeGeneratedValue` should be called whenever the
 *                                     generated input is manually updated.
 */
export function useInputGenerator({
	sourceValue = "",
	editing = false,
	setGeneratedValue,
}) {
	const [input, setInputGeneratorSourceValue] = useState(sourceValue);

	// The source and generated fields are “linked” if typing into the source field
	// should update the value of the generated field.
	const [fieldsAreLinked, setFieldsAreLinked] = useState(!editing);

	if (fieldsAreLinked) {
		setGeneratedValue(toValidApiId(input));
	}

	return {
		setFieldsAreLinked,
		setInputGeneratorSourceValue,
		onChangeGeneratedValue: (value) => {
			// Unlinks fields if the user edits the generated field. So that
			// users who edit the generated field and then the source field do
			// not have their changes to the generated field overwritten.
			setFieldsAreLinked(false);
			// Prevents invalid values being entered into the generated field.
			// An alternative is to show a validation error, but that forces
			// the user to understand legal input formats.
			setGeneratedValue(toValidApiId(value));
		},
	};
}
