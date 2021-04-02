import { arrayMove } from "@dnd-kit/sortable";

const { apiFetch } = wp;

/**
 * Updates new field order in the model state and WP database.
 *
 * @param event
 * @param fields
 * @param modelSlug
 * @param dispatch
 */
export function handleDragEnd(event, fields, modelSlug, dispatch) {
	const {active, over} = event;

	// Skips updates if field order has not changed.
	if (active.id === over.id) {
		return;
	}

	const oldIndex = fields.indexOf(active.id);
	const newIndex = fields.indexOf(over.id);
	const newOrder = arrayMove(fields, oldIndex, newIndex);
	const fieldPositionGap = 10000;

	let pos = 0;
	const idsAndNewPositions = newOrder.reduce((result, id) => {
		result[id] = {position: pos};
		pos += fieldPositionGap;
		return result;
	}, {});

	dispatch({type: 'reorderFields', positions: idsAndNewPositions, model: modelSlug});

	const updatePositions = async () => {
		await apiFetch({
			path: `/wpe/content-model-fields/${modelSlug}`,
			method: "PATCH",
			_wpnonce: wpApiSettings.nonce,
			data: {fields: idsAndNewPositions},
		});
	};

	updatePositions().catch(err => console.error(err));
}
