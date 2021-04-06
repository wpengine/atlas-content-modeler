import {POSITION_GAP} from "../../queries";

const {apiFetch} = wp;

/**
 * Updates the model store and WordPress database when fields are reordered.
 */
export function onDragEnd(result, fields, model, dispatch) {
	const {destination, source} = result;

	const reorder = (list, startIndex, endIndex) => {
		const result = Array.from(list);
		const [removed] = result.splice(startIndex, 1);
		result.splice(endIndex, 0, removed);

		return result;
	};

	if (!destination) {
		return;
	}

	if (
		destination.droppableId === source.droppableId
		&& destination.index === source.index
	) {
		return;
	}

	const newOrder = reorder(
		fields,
		result.source.index,
		result.destination.index
	);

	let position = 0;
	const idsAndNewPositions = newOrder.reduce((result, id) => {
		result[id] = {position};
		position += POSITION_GAP;
		return result;
	}, {});

	dispatch({type: 'reorderFields', positions: idsAndNewPositions, model: model});

	const updatePositions = async () => {
		await apiFetch({
			path: `/wpe/content-model-fields/${model}`,
			method: "PATCH",
			_wpnonce: wpApiSettings.nonce,
			data: {fields: idsAndNewPositions},
		});
	};

	updatePositions().catch(err => console.error(err));
}
