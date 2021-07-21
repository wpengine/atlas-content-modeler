/**
 * Actions to update global taxonomy state.
 */

export function taxonomiesReducer(state, action) {
	switch (action.type) {
		case "updateTaxonomy":
			return {
				...state,
				[action.data.slug]: action.data,
			};
		case "removeTaxonomy":
			const { [action.slug]: deleted, ...otherTaxonomies } = state;
			return otherTaxonomies;
		default:
			throw new Error(`${action.type} not found`);
	}
}
