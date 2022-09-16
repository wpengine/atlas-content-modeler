/** @jsx jsx */
import { css, jsx } from "@emotion/react";

export function Card(props) {
	const cssAttributes = css`
		background: #fff;
		margin: 20px;
		padding: 40px;
		svg.add:focus circle,
		svg.add:hover circle {
			fill: #5c43ae;
		}
		svg.add-small:focus circle,
		svg.add-small:hover circle {
			fill: #fff;
			stroke: #7e5cef;
		}
		svg.add-small:focus path,
		svg.add-small:hover path {
			fill: #7e5cef;
		}
		h2 {
			color: #002838;
			font-size: 28px;
			font-weight: bold;
			line-height: 45px;
			margin-bottom: 0;
			margin-top: 0;

			a {
				color: #7e5cef;
				text-decoration: none;
				&:focus,
				&:hover {
					color: #5c43ae;
				}
			}
		}
		h3 {
			font-size: 21px;
			font-weight: bold;
		}
		.heading {
			display: flex;
			justify-content: space-between;
			margin-bottom: 28px;
			.options {
				min-height: 32px;
				min-width: 32px;
				padding: 5px 5px 5px 8px;
			}
		}
		.card-content ul {
			margin-top: 38px;
		}
		.card-content li {
			border-left: 5px solid #0ecad4;
			border-radius: 2px;
			box-shadow: 0 1px 5px rgba(0, 0, 0, 0.15);
			line-height: 20px;
		}
		.card-content li.has-conflict {
			border-left: 5px solid #d21b46;
		}
		.card-content .subfield-list li {
			border-left: 5px solid #cfdde9;
		}
		.card-content .subfield-list li.add-item {
			border-left: none;
		}
		.field-list .add-item {
			box-shadow: none;
		}
	`;

	return <div css={cssAttributes} {...props} />;
}
