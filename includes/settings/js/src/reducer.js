/**
 * Actions to update global content model state.
 */
export function reducer(state, action) {
	switch (action.type) {
		case "updateModels":
			return action.data;
		case "addModel":
			return {
				...state,
				[action.data.postTypeSlug]: action.data,
			};
		case "removeModel":
			const { [action.slug]: deleted, ...otherModels } = state;
			return otherModels;
		case "addField":
			return { todo: "fill me in" };
		case "openField":
			return { todo: "fill me in" };
		case "closeField":
			return { todo: "fill me in" };
		case "updateField":
			return { todo: "fill me in" };
		case "removeField":
			return { todo: "fill me in" };
		case "swapFieldPositions":
			return { todo: "fill me in" };
		default:
			throw new Error(`${action.type} not found`);
	}
}
