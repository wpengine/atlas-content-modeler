/* global atlasContentModelerFormEditingExperience */
import React, { useContext, useState } from "react";
import Icon from "../../../../components/icons";
import { sprintf, __ } from "@wordpress/i18n";
import { ModelsContext } from "../../../../settings/js/src/ModelsContext";
import { useForm } from "react-hook-form";
import Modal from "react-modal";
import apiFetch from "@wordpress/api-fetch";
const { wp } = window;

/**
 * The modal component for editing a relationship.
 *
 * @param {Object} field The relationship field.
 * @param {Boolean} isOpen Whether or not the model is open.
 * @param {Function} setIsOpen - Callback for opening and closing modal.
 * @returns {JSX.Element} Modal
 */
export default function RelationshipModal({ field, isOpen, setIsOpen }) {
	const [singularCount, setSingularCount] = useState(0);
	const [pluralCount, setPluralCount] = useState(0);
	const [descriptionCount, setDescriptionCount] = useState(0);
	// const { dispatch } = useContext(ModelsContext);
	const {
		register,
		handleSubmit,
		errors,
		setValue,
		formState: { isSubmitting },
	} = useForm();
	const perPage = 5;
	const { models } = atlasContentModelerFormEditingExperience;
	const endpoint = "/wp/v2/" + models[field.slug].wp_rest_base;

	const customStyles = {
		overlay: {
			backgroundColor: "rgba(0, 40, 56, 0.7)",
		},
		content: {
			top: "50%",
			left: "50%",
			right: "auto",
			bottom: "auto",
			transform: "translate(-50%, -50%)",
			border: "none",
			padding: "32px",
			boxSizing: "border-box",
		},
	};

	/**
	 * Retrieves the total number of pages for the relationship field.
	 *
	 * @param {number} perPage
	 * @returns {number} Total number of pages
	 */
	function totalPages(perPage) {
		let params = {
			path: endpoint,
			page: 1,
			per_page: perPage,
			parse: false,
		};

		return apiFetch(params).then((response) => {
			return response.headers.get("X-WP-TotalPages");
		});
	}

	/**
	 * Retrieves relationship posts for a given page.
	 *
	 * @param {Number} page
	 * @param {Number} perPage
	 * @returns {object} Array of model data
	 */
	function retrieveModels(page, perPage) {
		let params = {
			path: endpoint,
			page: page,
			per_page: perPage,
		};

		return apiFetch(params).then((postData) => {
			return postData;
		});
	}

	retrieveModels(1, perPage).then((postData) => {
		console.log(postData);
	});

	totalPages(perPage).then((totalPages) => {
		console.log(totalPages);
	});

	return (
		<Modal
			isOpen={isOpen}
			contentLabel={`Creating relationship with ${field.name}`}
			parentSelector={() => {
				return document.getElementById(
					"atlas-content-modeler-fields-app"
				);
			}}
			portalClassName="atlas-content-modeler-edit-model-modal-container atlas-content-modeler"
			onRequestClose={() => {
				setIsOpen(false);
			}}
			field={field}
			style={customStyles}
		>
			<h2>{__("Select Reference", "atlas-content-modeler")}</h2>
			<p>
				{__(
					"Can only use published references",
					"atlas-content-modeler"
				)}
			</p>

			<form
				onSubmit={handleSubmit(async (data) => {
					const mergedData = { ...field, ...data };
					await updateModel(data.slug, mergedData);
					dispatch({ type: "updateModel", data: mergedData });
					updateSidebarMenuItem(field, data);
					setIsOpen(false);
				})}
			>
				<div className="row">
					<div
						className={
							errors.singular
								? "field has-error col-sm"
								: "field col-sm"
						}
					>
						<p className="field-messages">
							{errors.singular &&
								errors.singular.type === "required" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"This field is required",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
							{errors.singular &&
								errors.singular.type === "maxLength" && (
									<span className="error">
										<Icon type="error" />
										<span role="alert">
											{__(
												"Exceeds max length.",
												"atlas-content-modeler"
											)}
										</span>
									</span>
								)}
						</p>
					</div>
				</div>
				<button
					href="#"
					className="tertiary"
					disabled={isSubmitting}
					onClick={(event) => {
						event.preventDefault();
						setIsOpen(false);
					}}
				>
					{__("Cancel", "atlas-content-modeler")}
				</button>

				<button
					type="submit"
					disabled={isSubmitting}
					className="primary"
				>
					{__("Save", "atlas-content-modeler")}
				</button>
			</form>
		</Modal>
	);
}
