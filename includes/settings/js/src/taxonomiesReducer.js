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
		case "removeModel":
			Object.values(state).forEach((taxonomy) => {
				state[taxonomy.slug].types = taxonomy.types.filter(
					(type) => type !== action.slug
				);
			});

			return state;
		default:
			throw new Error(`${action.type} not found`);
	}
}
