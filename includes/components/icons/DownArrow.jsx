import React from "react";
import { sprintf, __ } from "@wordpress/i18n";

const DownArrow = () => {
	return (
		<svg
			aria-labelledby="wpe-down-arrow"
			width="22"
			height="22"
			viewBox="0 0 22 22"
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			role="img"
		>
			<title id="wpe-down-arrow">
				{__("down arrow", "atlas-content-modeler")}
			</title>
			<path
				d="M12 7.5C12 7.22386 11.7761 7 11.5 7H10.5C10.2239 7 9.99999 7.22386 9.99999 7.5V13.75L7.99999 12.25C7.77908 12.0843 7.46568 12.1291 7.29999 12.35L6.69999 13.15C6.53431 13.3709 6.57908 13.6843 6.79999 13.85L11 17L15.2 13.85C15.4209 13.6843 15.4657 13.3709 15.3 13.15L14.7 12.35C14.5343 12.1291 14.2209 12.0843 14 12.25L12 13.75V7.5Z"
				fill="#002838"
			/>
			<path
				fillRule="evenodd"
				clipRule="evenodd"
				d="M0 4C0 1.79086 1.79086 0 4 0H18C20.2091 0 22 1.79086 22 4V18C22 20.2091 20.2091 22 18 22H4C1.79086 22 0 20.2091 0 18V4ZM4 2H18C19.1046 2 20 2.89543 20 4V18C20 19.1046 19.1046 20 18 20H4C2.89543 20 2 19.1046 2 18V4C2 2.89543 2.89543 2 4 2Z"
				fill="#002838"
			/>
		</svg>
	);
};

export default DownArrow;
