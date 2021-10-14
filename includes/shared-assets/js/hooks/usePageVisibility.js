/**
 * The usePageVisibility hook monitors the browser page for focus.
 *
 * @link https://github.com/Darth-Knoppix/example-react-page-visibility
 */
import { useEffect, useState } from "react";

/**
 * usePageVisibility hook.
 *
 * @example
 * ```
 * const pageIsVisible = usePageVisibility();
 * ```
 */
export default function usePageVisibility() {
	const [isVisible, setIsVisible] = useState(getIsDocumentHidden());
	const onVisibilityChange = () => setIsVisible(getIsDocumentHidden());

	useEffect(() => {
		const visibilityChange = getBrowserVisibilityProp();

		document.addEventListener(visibilityChange, onVisibilityChange, false);

		return () => {
			document.removeEventListener(visibilityChange, onVisibilityChange);
		};
	});

	return isVisible;
}

function getBrowserVisibilityProp() {
	if (typeof document.hidden !== "undefined") {
		// Opera 12.10 and Firefox 18 and later support
		return "visibilitychange";
	} else if (typeof document.msHidden !== "undefined") {
		return "msvisibilitychange";
	} else if (typeof document.webkitHidden !== "undefined") {
		return "webkitvisibilitychange";
	}
}

function getBrowserDocumentHiddenProp() {
	if (typeof document.hidden !== "undefined") {
		return "hidden";
	} else if (typeof document.msHidden !== "undefined") {
		return "msHidden";
	} else if (typeof document.webkitHidden !== "undefined") {
		return "webkitHidden";
	}
}

function getIsDocumentHidden() {
	return !document[getBrowserDocumentHiddenProp()];
}
