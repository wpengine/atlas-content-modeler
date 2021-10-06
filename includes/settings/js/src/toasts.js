import React from "react";
import { toast } from "react-toastify";
import Icon from "../../../components/icons";

export function showSuccess(message) {
	toast(
		() => (
			<>
				<Icon type="tick" />
				<span>{message}</span>
			</>
		),
		{
			closeButton: false,
			toastId: "success",
			type: "success",
		}
	);
}

export function showError(message) {
	toast(
		({ closeToast }) => (
			<>
				<Icon type="error" size="large" />
				<span
					dangerouslySetInnerHTML={{
						__html: message,
					}}
				></span>
				<button className="close" onClick={closeToast}>
					<Icon type="close" />
				</button>
			</>
		),
		{
			autoClose: false,
			closeButton: false,
			toastId: "error",
			type: "error",
		}
	);
}
