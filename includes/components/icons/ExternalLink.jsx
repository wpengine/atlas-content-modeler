import React from "react";

const ExternalLinkIcon = ({ width = 16, height = 16, color = "#002838" }) => {
	return (
		<svg
			width={width}
			height={height}
			fill="none"
			xmlns="http://www.w3.org/2000/svg"
			className="icon-external-link"
			viewBox="0 0 16 16"
		>
			<path
				d="M3 0a3 3 0 00-3 3v5a1 1 0 102 0V3a1 1 0 011-1h10a1 1 0 011 1v10a1 1 0 01-1 1H5.828c-.89 0-1.337-1.077-.707-1.707L9 8.414V10a1 1 0 102 0V6v-.005a.995.995 0 00-.29-.699l-.006-.006A.996.996 0 0010 5H6a1 1 0 000 2h1.586l-3.879 3.879C1.817 12.769 3.156 16 5.828 16H13a3 3 0 003-3V3a3 3 0 00-3-3H3z"
				fill={color}
			/>
		</svg>
	);
};

export default ExternalLinkIcon;
