/** @jsx jsx */
import { jsx, css } from "@emotion/react";

export function Button(props) {
	const cssAttributes = css`
		background: #7e5cef;
		border-radius: 2px;
		border: 1px solid #7e5cef;
		color: #fff;
		cursor: pointer;
		font-size: 14px;
		font-weight: 600;
		height: 52px;
		line-height: 20px;
		padding: 14px 24px;
		&:hover,
		&:focus {
			background: #5c43ae;
			border: 1px solid #5c43ae;
		}
		,
		&:disabled {
			background: #f4f7fa !important;
			border-color: #cfdde9 !important;
			color: #59767f !important;
			&:hover {
				background: #f4f7fa !important;
				border-color: #cfdde9 !important;
				color: #59767f !important;
			}
		}
	`;

	return (
		<button css={cssAttributes} {...props}>
			{props.children}
		</button>
	);
}

export function TertiaryButton(props) {
	const cssAttributes = css`
		background: #fff;
		border: 1px solid #002838;
		color: #002838;
		&:hover,
		&:focus {
			background: #fff;
			border: 1px solid #7e5cef;
			color: #7e5cef;
		}
	`;

	return (
		<Button css={cssAttributes} {...props}>
			{props.children}
		</Button>
	);
}

export function FieldButton({ className = "", active = false, ...props }) {
	let cssAttributes = css`
		align-items: center;
		display: flex;
		margin-right: 8px;
		padding: 10px 16px;

		&:hover svg path,
		&:focus svg path {
			fill: #7e5cef;
		}

		svg {
			margin-right: 9px;
		}
	`;

	if (active) {
		cssAttributes = css`
			${cssAttributes}
			background: #002838;
			color: #fff;

			&:hover,
			&:focus {
				background: #002838;
				border: 1px solid #002838;
				color: #fff;
			}

			svg path,
			&:focus svg path,
			&:hover svg path {
				fill: #fff;
			}
		`;
		className += " active";
	}

	return (
		<TertiaryButton css={cssAttributes} className={className} {...props}>
			{props.children}
		</TertiaryButton>
	);
}

export function LinkButton(props) {
	const cssAttributes = css`
		background: transparent;
		border-color: transparent;
		color: #7e5cef;
		margin-left: 20px;
		padding: 14px 0;

		&:active,
		&:hover,
		&:focus {
			background: transparent;
			border-color: transparent;
			color: #5c43ae;

			svg path {
				fill: #5c43ae;
			}
		}
		&:disabled {
			background: transparent !important;
			border-color: transparent !important;
			color: #9db7d1 !important;
			&:hover {
				background: transparent !important;
				border-color: transparent !important;
				color: #9db7d1 !important;
			}
		}

		svg {
			margin-right: 10px;
		}
	`;

	return (
		<Button css={cssAttributes} {...props}>
			{props.children}
		</Button>
	);
}

export function WarningButton(props) {
	const cssAttributes = css`
		background: #d21b46;
		border: 1px solid #d21b46;
		color: #fff;
		&:hover,
		&:focus {
			background: #991433;
			border: 1px solid #991433;
		}
	`;

	return (
		<Button css={cssAttributes} {...props}>
			{props.children}
		</Button>
	);
}

export function DarkButton(props) {
	const cssAttributes = css`
		background: #002838;
		border: 1px solid #002838;
		color: #fff;
		&:hover,
		&:focus {
			background: #004c6b;
			border: 1px solid #004c6b;
		}
	`;

	return (
		<Button css={cssAttributes} {...props}>
			{props.children}
		</Button>
	);
}
