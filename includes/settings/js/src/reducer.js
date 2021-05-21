/**
 * Actions to update global content model state.
 */
import { getChildrenOfField } from "./queries";

export function reducer(state, action) {
	switch (action.type) {
		case "updateModels":
			return action.data;
		case "updateModel":
			state[action.data.postTypeSlug] = {
				...state[action.data.postTypeSlug],
				...action.data,
			};
			state[action.data.postTypeSlug].name = action.data.plural;
			state[action.data.postTypeSlug].singular_name =
				action.data.singular;
			return { ...state };
		case "addModel":
			return {
				...state,
				[action.data.postTypeSlug]: action.data,
			};
		case "removeModel":
			const { [action.slug]: deleted, ...otherModels } = state;
			return otherModels;
		case "addField":
			Object.values(state[action.model]["fields"]).forEach((field) => {
				field.open = false;
				field.editing = false;
			});
			const newId = Date.now();
			state[action.model]["fields"] = {
				...state[action.model]["fields"],
				[newId]: {
					id: newId,
					type: action?.fieldType || "text",
					open: true,
					position: action.position,
					parent: action?.parent,
				},
			};
			return { ...state };
		case "openField":
			Object.values(state[action.model]["fields"]).forEach((field) => {
				if (field === state[action.model]["fields"][action.id]) {
					// If parent is undefined it is a new unsaved field.
					if (!state[action.model]["fields"][action.id]["parent"]) {
						field.open = true;
						// We set editing to false so when the cancel button is clicked, it is not interpreted as a saved and editing field.
						field.editing = false;
					} else {
						field.open = true;
						field.editing = true;
					}
				} else {
					field.open = false;
					field.editing = false;
				}
			});
			return { ...state };
		case "closeField":
			// If the action.id object object does not have a parent, it is a new field.
			if (!state[action.model]["fields"][action.id]["parent"]) {
				delete state[action.model]["fields"][action.id];
			}

			if (action?.originalState) {
				state[action.model]["fields"] = action.originalState;
			}
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
		case "setTitleField":
			const fields = state[action.model]["fields"];
			Object.values(fields).forEach((field) => {
				state[action.model]["fields"][field.id]["isTitle"] = false;
			});
			state[action.model]["fields"][action.id]["isTitle"] = true;
			return { ...state };
		case "setFieldProperties":
			action.properties.forEach((property) => {
				state[action.model]["fields"][action.id][property.name] =
					property.value;
			});
			return { ...state };
		case "removeField":
			if (action?.originalState) {
				state[action.model]["fields"] = action.originalState;
			}
			// Also remove descendents of repeater fields.
			if (state[action.model]["fields"][action.id]?.type === "repeater") {
				const children = getChildrenOfField(
					action.id,
					state[action.model]["fields"]
				);
				children.forEach((subfieldId) => {
					delete state[action.model]["fields"][subfieldId];
				});
			}

			// Remove the deleted field itself.
			delete state[action.model]["fields"][action.id];

			return { ...state };
		case "reorderFields":
			Object.keys(action.positions).forEach((fieldId) => {
				state[action.model]["fields"][fieldId].position =
					action.positions[fieldId].position;
			});
			return { ...state };
		default:
			throw new Error(`${action.type} not found`);
	}
}
