import React, { useReducer } from "react";
import { reducer } from "./reducer";

export const ModelsContext = React.createContext(null);

/**
 * Gives access to `models` data and `dispatch` function to child components.
 * - `models` contains the shared global state of all model data.
 * - `dispatch` lets child components safely alter global model data.
 *
 * WordPress writes the `wpeContentModel` JS global to the settings page in
 * `settings-callbacks.php` to set initial state without needing a fetch here.
 */
export function ModelsContextProvider(props) {
	const [ models, dispatch ] = useReducer(
		reducer,
		wpeContentModel?.initialState
	);

	return (
		<ModelsContext.Provider
			value={ {
				models,
				dispatch,
			} }
		>
			{ props.children }
		</ModelsContext.Provider>
	);
}
