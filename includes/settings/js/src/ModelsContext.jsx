import React, { useEffect, useState } from "react";

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
	const [ models, setModels ] = useState({});

	const refreshModels = async () => {
		await getAllModels().then((models) => {
			if (Object.keys(models).length === 0) {
				setModels('none');
			} else {
				setModels(models);
			}
		});
	}

	useEffect(() => {
		refreshModels();
	}, [models]);

	return (
		<ModelsContext.Provider
			value={ {
				models,
				refreshModels
			} }
		>
			{ props.children }
		</ModelsContext.Provider>
	);
}
