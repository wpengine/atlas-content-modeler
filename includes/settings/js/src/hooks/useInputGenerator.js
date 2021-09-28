import { useState, useEffect } from "react";

/**
 * Uses the value of a source input to generate and apply a valid value
 * to another input.
 *
 * Updates take place as long as:
 * - The form is not presenting stored data for editing. (So that the user has
 *   to explicitly edit generated field input if they intend it to change.)
 * - The generated field has not been manually edited. (So that editing the
 *   source field after editing the generated field does not remove changes to the
 *   generated field.)
 *
 * @param {boolean}  linked            Should the inputs be linked on initial render?
 * @param {function} setGeneratedValue Function for updating the value of the generated field.
 * @param {function} format            Function that takes the source value and returns a valid
 *                                     generated value.
 * @return {Object}                    Where `setInputGeneratorSourceValue` should pass the
 *                                     source input field's value whenever it is updated, and
 *                                     `onChangeGeneratedValue` should be called whenever the
 *                                     generated input is manually updated.
 */
export function useInputGenerator({
	linked = true,
	setGeneratedValue,
	setDefaultInputValue,
	defaultInputValue = "",
	format,
}) {
	if (typeof format !== "function") {
		format = (value) => value;
	}

	const [input, setInputGeneratorSourceValue] = useState(defaultInputValue);

	// The source and generated fields are “linked” if typing into the source field
	// should update the value of the generated field.
	const [fieldsAreLinked, setFieldsAreLinked] = useState(linked);

	useEffect(() => {
		if (defaultInputValue && setDefaultInputValue) {
			setDefaultInputValue(defaultInputValue);
		}
	}, []);

	if (fieldsAreLinked) {
		setGeneratedValue(format(input));
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
			setGeneratedValue(format(value));
		},
	};
}
