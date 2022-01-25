/** @jsx jsx */
import { css, jsx } from "@emotion/react";

export function Card(props) {
	const cssAttributes = css`
		background: #fff;
		margin: 20px;
		padding: 40px;

		svg.add:focus circle,
		svg.add:hover circle {
			fill: $color-primary-hover;
		}

		svg.add-small:focus circle,
		svg.add-small:hover circle {
			fill: #fff;
			stroke: $color-primary;
		}

		svg.add-small:focus path,
		svg.add-small:hover path {
			fill: $color-primary;
		}

		h2 {
			color: $color-text;
			font-size: 28px;
			font-weight: bold;
			line-height: 45px;
			margin-bottom: 0;
			margin-top: 0;

			a {
				color: $color-primary;
				text-decoration: none;
				&:focus,
				&:hover {
					color: $color-primary-hover;
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
			border-left: 5px solid $color-highlight;
			border-radius: $radius;
			box-shadow: 0 1px 5px rgba(0, 0, 0, 0.15);
			line-height: 20px;
		}

		.card-content .subfield-list li {
			border-left: 5px solid #cfdde9;
		}

		.card-content .subfield-list li.add-item {
			border-left: none;
		}
	`;

	return <div css={cssAttributes} {...props} />;
}
