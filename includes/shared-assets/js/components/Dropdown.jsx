/** @jsx jsx */
import { css, jsx } from "@emotion/react";

export function Dropdown(props) {
	const cssAttributes = css`
		position: relative;
		min-height: 20px;

		@media (max-width: 850px) {
			margin-left: -80px;
		}

		button.options {
			cursor: pointer;
		}

		.dropdown-content:not(.hidden) {
			background: #fff;
			border-radius: 2px;
			box-shadow: 0px 5px 20px rgba(68, 68, 68, 0.15);
			display: flex;
			flex-direction: column;
			height: auto;
			justify-content: center;
			padding: 10px 0;
			position: absolute;
			right: 0;
			z-index: 100;

			a {
				color: #7e5cef;
				display: block;
				font-size: 15px;
				line-height: 25px;
				padding: 5px 20px;
				text-align: center;
				text-decoration: none;
				white-space: nowrap;
			}

			a:hover {
				color: #5c43ae;
			}

			a.delete {
				color: #d21b46;
			}

			a.delete:hover {
				color: #991433;
			}
		}
	`;

	return <span css={cssAttributes} {...props} />;
}
