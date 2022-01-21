/** @jsx jsx */
import { styled, jsx } from "@emotion/react";
import Modal from "react-modal";

export const ModalDecorator = ({ className, ...props }) => {
	const [name] = (className && className.split(" ")) || [""];
	const styles = name
		? {
				portalClassName: name,
				overlayClassName: `${name}__Overlay`,
				className: `${name}__Content`,
		  }
		: {};

	return <Modal {...styles} {...props} />;
};

export const StyledModal = styled(ModalDecorator)`
	h2 {
		font-size: 24px;
		margin-top: 0;
	}

	.ReactModal__Content--after-open p:last-of-type {
		margin-bottom: 40px;
	}
`;

export const StyledDeleteModal = styled(ModelDecorator)`
	h2 {
		font-size: 24px;
		margin-top: 0;
	}

	.ReactModal__Content--after-open p:last-of-type {
		margin-bottom: 40px;
	}

	ul {
		list-style-type: disc;
		padding-left: 2rem;

		li.warning {
			color: #d21b46;
			font-weight: 700;
		}
	}
`;

export const StyledEditModal = styled(ModalDecorator)`
	overflow: hidden;

	.ReactModal__Overlay--after-open {
		width: 100%;
		height: 100%;
		overflow: scroll;
		padding-bottom: 17px;
		padding-right: 17px;
		box-sizing: content-box;
		z-index: 99999; /* renders modal above WP Admin bar */
	}

	.ReactModal__Content--after-open {
		width: 100%;
		max-width: 600px;
		max-height: 100%;

		input,
		textarea,
		.field-messages {
			width: 100%;
			max-width: 500px;
		}

		input[type="radio"] {
			width: 1rem;
		}
	}

	form div.field {
		margin-top: 0;
	}

	.help {
		margin-top: 5px;
		margin-bottom: 5px;
	}

	input {
		margin: 0;
	}
`;
