/**
 * Actions to update global taxonomy state.
 */

export function taxonomiesReducer(state, action) {
	switch (action.type) {
		case "addTaxonomy":
			return {
				...state,
				[action.data.slug]: action.data,
			};
		default:
			throw new Error(`${action.type} not found`);
	}
}
