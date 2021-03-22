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
			state[action.model]['fields'][action.id].open = true;
			state[action.model]['fields'][action.id].editing = true;
			return {...state};
		case "closeField":
			state[action.model]['fields'][action.id].open = false;
			state[action.model]['fields'][action.id].editing = false;
			return {...state};
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
