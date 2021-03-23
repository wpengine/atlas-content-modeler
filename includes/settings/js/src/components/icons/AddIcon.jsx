import React from 'react';

function AddIcon() {
	return (
		<svg className="add" width="72" height="72" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg">
			<g filter="url(#filter0_d)">
				<circle cx="36" cy="31" r="16" fill="#7E5CEF"/>
				<path d="M37.3335 37.3335C37.3335 37.5176 37.1843 37.6668 37.0002 37.6668H35.0002C34.8161 37.6668 34.6668 37.5176 34.6668 37.3335V32.3335H29.6668C29.4827 32.3335 29.3335 32.1843 29.3335 32.0002V30.0002C29.3335 29.8161 29.4827 29.6668 29.6668 29.6668H34.6668V24.6668C34.6668 24.4827 34.8161 24.3335 35.0002 24.3335H37.0002C37.1843 24.3335 37.3335 24.4827 37.3335 24.6668V29.6668H42.3335C42.5176 29.6668 42.6668 29.8161 42.6668 30.0002V32.0002C42.6668 32.1843 42.5176 32.3335 42.3335 32.3335H37.3335V37.3335Z" fill="white"/>
			</g>
			<defs>
				<filter id="filter0_d" x="0" y="0" width="72" height="72" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
					<feFlood floodOpacity="0" result="BackgroundImageFix"/>
					<feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/>
					<feOffset dy="5"/>
					<feGaussianBlur stdDeviation="10"/>
					<feColorMatrix type="matrix" values="0 0 0 0 0.266667 0 0 0 0 0.266667 0 0 0 0 0.266667 0 0 0 0.15 0"/>
					<feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
					<feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
				</filter>
			</defs>
		</svg>
	);
}

export default AddIcon;
