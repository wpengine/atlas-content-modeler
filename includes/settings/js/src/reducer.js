/**
 * Actions to update global content model state.
 */

export function reducer(state, action) {
	switch (action.type) {
		case "updateModels":
			return action.data;
		case "updateModel":
			state[action.data.slug] = {
				...state[action.data.slug],
				...action.data,
			};
			state[action.data.slug].name = action.data.plural;
			state[action.data.slug].singular = action.data.singular;
			state[action.data.slug].api_visibility = action.data.api_visibility;
			return { ...state };
		case "addModel":
			return {
				...state,
				[action.data.slug]: action.data,
			};
		case "removeModel":
			const { [action.slug]: deleted, ...otherModels } = state;
			return otherModels;
		case "addField":
			Object.values(state[action.model]["fields"] ?? {}).forEach(
				(field) => {
					field.open = false;
					field.editing = false;
				}
			);
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
					field.open = true;
					// We set editing to false when there is no name so delete succeeds when cancel button is clicked on a new field.
					field.editing = !state[action.model]["fields"][action.id][
						"name"
					]
						? false
						: true;
				} else {
					field.open = false;
					field.editing = false;
				}
			});
			return { ...state };
		case "closeField":
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
		case "setFeaturedImageField":
			const fields = state[action.model]["fields"];
			Object.values(fields).forEach((field) => {
				state[action.model]["fields"][field.id]["isFeatured"] = false;
			});
			state[action.model]["fields"][action.id]["isFeatured"] = true;
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

			// Remove the deleted field itself.
			delete state[action.model]["fields"][action.id];

			return { ...state };
		case "removeFields":
			action.fields.forEach((field) => {
				delete state?.[field.model]?.["fields"]?.[field.id];
			});

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
