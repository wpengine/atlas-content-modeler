import React from 'react';

function AddIcon() {
	return (
		<svg className="add" width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
			<g filter="url(#filter0_d)">
				<circle cx="32" cy="27" r="12" fill="#7E5CEF"/>
				<path d="M33.3333 33.3333C33.3333 33.5173 33.1841 33.6666 33 33.6666H31C30.8159 33.6666 30.6666 33.5173 30.6666 33.3333V28.3333H25.6666C25.4826 28.3333 25.3333 28.184 25.3333 27.9999V25.9999C25.3333 25.8158 25.4826 25.6666 25.6666 25.6666H30.6666V20.6666C30.6666 20.4825 30.8159 20.3333 31 20.3333H33C33.1841 20.3333 33.3333 20.4825 33.3333 20.6666V25.6666H38.3333C38.5174 25.6666 38.6666 25.8158 38.6666 25.9999V27.9999C38.6666 28.184 38.5174 28.3333 38.3333 28.3333H33.3333V33.3333Z" fill="white"/>
			</g>
			<defs>
				<filter id="filter0_d" x="0" y="0" width="64" height="64" filterUnits="userSpaceOnUse" colorInterpolationFilters="sRGB">
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
