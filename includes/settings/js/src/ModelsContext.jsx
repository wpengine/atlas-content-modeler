import React, { useEffect, useReducer } from "react";
import { reducer } from "./reducer";

export const ModelsContext = React.createContext(null);

const { apiFetch } = wp;

function getAllModels() {
	const allModels = apiFetch({
		path: "/wpe/content-models",
		method: "GET",
		_wpnonce: wpApiSettings.nonce,
	}).then((res) => {
		return res;
	});
	return allModels;
}

export function ModelsContextProvider(props) {
	const [ models, dispatch ] = useReducer( reducer, null );

	const refreshModels = async () => {
		await getAllModels().then((models) => {
			dispatch({type: 'updateModels', data: models})
		});
	}

	useEffect(() => {
		refreshModels();
	}, []);

	return (
		<ModelsContext.Provider
			value={ {
				models,
				dispatch,
				refreshModels // TODO: remove refreshModels from fields update logic so this can be removed in favour of dispatch.
			} }
		>
			{ props.children }
		</ModelsContext.Provider>
	);
}
