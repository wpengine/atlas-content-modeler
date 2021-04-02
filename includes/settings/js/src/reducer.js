/**
 * Actions to update global content model state.
 */
import {getChildrenOfField} from "./queries";

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
			const newId = Date.now();
			state[action.model]["fields"] = {
				...state[action.model]["fields"],
				[newId]: {
					id: newId,
					type: "text",
					open: true,
					position: action.position,
					parent: action?.parent,
				},
			};
			return { ...state };
		case "openField":
			state[action.model]["fields"][action.id].open = true;
			state[action.model]["fields"][action.id].editing = true;
			return { ...state };
		case "closeField":
			state[action.model]["fields"][action.id].open = false;
			state[action.model]["fields"][action.id].editing = false;
			return { ...state };
		case "updateField":
			state[action.model]["fields"][action.data.id] = {
				...action.data,
				open: false,
				editing: false,
			};
			return { ...state };
		case "removeField":
			// Also remove descendents of repeater fields.
			if (state[action.model]["fields"][action.id]?.type === 'repeater') {
				const children = getChildrenOfField(action.id, state[action.model]["fields"]);
				children.forEach((subfieldId) => {
					delete state[action.model]["fields"][subfieldId];
				});
			}

			// Remove the deleted field itself.
			delete state[action.model]["fields"][action.id];

			return { ...state };
		case "reorderFields":
			Object.keys(action.positions).forEach((fieldId) => {
				state[action.model]["fields"][fieldId].position = action.positions[fieldId].position;
			});
			return { ...state };
		case "swapFieldPositions":
			const fields = state[action.model]["fields"];
			state[action.model]["fields"] = {
				...fields,
				[action.id1]: {
					...fields[action.id1],
					position: fields[action.id2].position,
				},
				[action.id2]: {
					...fields[action.id2],
					position: fields[action.id1].position,
				},
			};
			return { ...state };
		default:
			throw new Error(`${action.type} not found`);
	}
}
