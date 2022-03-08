/** @jsx jsx */
import { jsx, css } from "@emotion/react";
import styled from "@emotion/styled";

// TODO-abotz: Add radius variable(s) to theme?
const borderRadius = "2px";

const addImportantToStyle = (style) => {
	return `${style} !important`;
};

export const Button = styled.button((props) => {
	const {
		primary,
		primaryHover,
		secondary,
		secondaryLight,
		grayDisabledText,
		white,
	} = props.theme.colors;

	return {
		fontSize: "14px",
		fontWeight: "600",
		lineHeight: "20px",
		background: primary,
		borderRadius,
		border: `1px solid ${primary}`,
		color: white,
		cursor: "pointer",
		height: "52px",
		padding: "14px 20px",
		"&:hover, &:focus": {
			background: primaryHover,
			border: `1px solid ${primaryHover}`,
		},
		"&:disabled": {
			background: addImportantToStyle(secondaryLight),
			borderColor: addImportantToStyle(secondary),
			color: addImportantToStyle(grayDisabledText),
			"&:hover": {
				background: addImportantToStyle(secondaryLight),
				borderColor: addImportantToStyle(secondary),
				color: addImportantToStyle(grayDisabledText),
			},
		},
	};
});

export const TertiaryButton = styled(Button)((props) => {
	const { white, blueDark, primary } = props.theme.colors;

	return {
		background: white,
		border: `1px solid ${blueDark}`,
		color: blueDark,
		"&:hover, &:focus": {
			background: white,
			border: `1px solid ${primary}`,
			color: primary,
		},
	};
});

// TODO-abotz: Need to figure out how to add className of active when "active" prop is true
export const FieldButton = styled(Button)((props) => {
	const { active = false } = props;
	const { primary, text, white } = props.theme.colors;

	if (active) {
		return {
			background: text,
			color: white,
			"&:hover, &:focus": {
				background: text,
				border: `1px solid ${text}`,
				color: white,
			},
			"svg path, &:focus svg path, &:hover svg path": {
				fill: white,
			},
		};
	}

	return {
		alignItems: "center",
		display: "flex",
		marginRight: "8px",
		padding: "10px 16px",
		"&:hover svg path, &:focus svg path": {
			fill: primary,
		},
		svg: {
			marginRight: "9px",
		},
	};
});

export const LinkButton = styled(Button)((props) => {
	const { primary, primaryHover, grayLight } = props.theme.colors;
	const transparent = "transparent";

	return {
		background: transparent,
		borderColor: transparent,
		color: primary,
		marginLeft: "20px",
		padding: "14px 0",
		"&:active, &:hover, &:focus": {
			background: transparent,
			borderColor: transparent,
			color: primaryHover,
			"svg path": {
				fill: primaryHover,
			},
		},
		"&:disabled": {
			background: addImportantToStyle(transparent),
			borderColor: addImportantToStyle(transparent),
			color: addImportantToStyle(grayLight),
			"&:hover": {
				background: addImportantToStyle(transparent),
				borderColor: addImportantToStyle(transparent),
				color: addImportantToStyle(grayLight),
			},
		},

		svg: {
			marginRight: "10px",
		},
	};
});

export const WarningButton = styled(Button)((props) => {
	const { warning, warningHover, white } = props.theme.colors;

	return {
		background: warning,
		border: `1px solid ${warning}`,
		color: white,
		"&:hover, &:focus": {
			background: warningHover,
			border: `1px solid ${warningHover}`,
		},
	};
});

export const DarkButton = styled(Button)((props) => {
	const { blueDark, blueDarkHover, white } = props.theme.colors;
	return {
		background: blueDark,
		border: `1px solid ${blueDark}`,
		color: white,
		"&:hover, &:focus": {
			background: blueDarkHover,
			border: `1px solid ${blueDarkHover}`,
		},
	};
});
