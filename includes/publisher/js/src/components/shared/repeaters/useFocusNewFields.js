import { useRef, useEffect } from "react";

/**
 * Moves focus to the final field in a repeating field group when a new field
 * is added.
 *
 * @param {string} modelSlug
 * @param {string} fieldSlug
 * @param {array} fieldValues
 */
const useFocusNewFields = (modelSlug, fieldSlug, fieldValues) => {
	const fieldCount = useRef(fieldValues?.length);

	/**
	 * Move focus to the last field if the list of fieldValues grows.
	 */
	useEffect(() => {
		const focusLastField = () => {
			const lastField = document.querySelector(
				`[name="atlas-content-modeler[${modelSlug}][${fieldSlug}][${
					fieldValues?.length - 1
				}]"`
			);
			if (lastField) {
				lastField.focus();
			}
		};

		const fieldWasAdded = fieldValues?.length === fieldCount.current + 1;

		if (fieldWasAdded) {
			focusLastField();
		}

		fieldCount.current = fieldValues?.length;
	}, [fieldValues, fieldCount]);
};

export default useFocusNewFields;
