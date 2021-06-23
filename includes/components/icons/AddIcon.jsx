import React from "react";

function AddIcon({ size = "large", noCircle }) {
	if (size === "small") {
		return (
			<svg
				className="add add-small"
				width="24"
				height="24"
				viewBox="0 0 24 24"
				xmlns="http://www.w3.org/2000/svg"
				fillRule="evenodd"
				clipRule="evenodd"
			>
				<g transform="translate(-20 -15)">
					<circle
						cx="32"
						cy="27"
						r="11.5"
						fill="#fff"
						stroke="#002838"
					/>
					<path
						d="M33.333 33.333a.334.334 0 01-.333.334h-2a.334.334 0 01-.333-.334v-5h-5a.334.334 0 01-.334-.333v-2c0-.184.15-.333.334-.333h5v-5c0-.184.149-.334.333-.334h2c.184 0 .333.15.333.334v5h5c.184 0 .334.149.334.333v2c0 .184-.15.333-.334.333h-5v5z"
						fill="#002838"
						fillRule="nonzero"
					/>
				</g>
			</svg>
		);
	}
	if (noCircle) {
		return (
			<svg
				className="add add-small add-small-button"
				width="24"
				height="24"
				viewBox="0 0 24 24"
				xmlns="http://www.w3.org/2000/svg"
				fillRule="evenodd"
				clipRule="evenodd"
			>
				<g transform="translate(-20 -15)">
					<path
						d="M33.333 33.333a.334.334 0 01-.333.334h-2a.334.334 0 01-.333-.334v-5h-5a.334.334 0 01-.334-.333v-2c0-.184.15-.333.334-.333h5v-5c0-.184.149-.334.333-.334h2c.184 0 .333.15.333.334v5h5c.184 0 .334.149.334.333v2c0 .184-.15.333-.334.333h-5v5z"
						fill="#7e5cef"
						fillRule="nonzero"
					/>
				</g>
			</svg>
		);
	}

	return (
		<svg
			className="add"
			width="32"
			height="32"
			xmlns="http://www.w3.org/2000/svg"
			fillRule="evenodd"
			clipRule="evenodd"
			strokeLinejoin="round"
			strokeMiterlimit="2"
		>
			<g transform="translate(-20 -15)">
				<circle cx="36" cy="31" r="16" fill="#7e5cef" />
				<path
					d="M37.333 37.333a.334.334 0 01-.333.334h-2a.334.334 0 01-.333-.334v-5h-5a.334.334 0 01-.334-.333v-2c0-.184.15-.333.334-.333h5v-5c0-.184.149-.334.333-.334h2c.184 0 .333.15.333.334v5h5c.184 0 .334.149.334.333v2c0 .184-.15.333-.334.333h-5v5z"
					fill="#fff"
					fillRule="nonzero"
				/>
			</g>
		</svg>
	);
}

export default AddIcon;
