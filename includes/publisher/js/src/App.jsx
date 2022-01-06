import React, { useState, useEffect } from "react";
import Fields from "./components/Fields";
import { __ } from "@wordpress/i18n";
import TrashPostModal from "./components/TrashPostModal";
const { wp } = window;
const { apiFetch } = wp;

export default function App({ model, mode }) {
	const isEditMode = mode === "edit";
	const [trashPostModalIsOpen, setTrashPostModalIsOpen] = useState(false);

	// GA config
	let gaConfig = {
		measurement_id: "G-S056CLLZ34",
		secretKey: "",
	};

	/**
	 * Sends call to GA endpoint
	 * @param {*} formData - All data for the GA call
	 *
	 * formData = [
	 * 'v' => '1',  # API Version.
	 * 'tid' => $trackingId,  # Tracking ID / Property ID.
	 * # Anonymous Client Identifier. Ideally, this should be a UUID that
	 * # is associated with particular user, device, or browser instance.
	 * 'cid' => '555',
	 * 't' => 'event',  # Event hit type.
	 * 'ec' => 'Poker',  # Event category.
	 * 'ea' => 'Royal Flush',  # Event action.
	 * 'el' => 'Hearts',  # Event label.
	 * 'ev' => 0,  # Event value, must be an integer
	 * ];
	 */
	function ga(eventData) {
		// add anonymize ip to every single event param object
		const defaultEventParams = { anonymize_ip: true };
		// map defaults to all events
		eventData.events.map((event) => {
			return { ...event.params, ...defaultEventParams };
		});

		const gaData = {
			ga_event: eventData,
			secret_key: gaConfig.secretKey,
			measurement_id: gaConfig.measurement_id,
		};

		// call ga api
		return apiFetch({
			path: "/wpe/atlas/ga-analytics",
			method: "POST",
			_wpnonce: wpApiSettings.nonce,
			gaData,
		}).then((res) => {
			if (res.success) {
				alert("Dispatched - " + formData);
			} else {
				alert("GA Failed");
			}
		});
	}

	useEffect(() => {
		// test
		const eventData = {
			client_id: "aaa.bbb", // need to generate random client id - domain?
			events: [
				{
					name: "publish_post",
					params: {},
				},
			],
		};

		ga(eventData);
	}, []);

	return (
		<div className="app classic-form" style={{ marginTop: "20px" }}>
			<div className="flex-parent">
				<div>
					<h3 className="main-title">
						{isEditMode ? (
							<span>{__("Edit", "atlas-content-modeler")} </span>
						) : (
							<span>{__("Add", "atlas-content-modeler")} </span>
						)}
						{model.singular}
					</h3>
				</div>

				{isEditMode && (
					<div
						style={{ marginLeft: "20px" }}
						className="flex-align-v"
					>
						<a
							className="page-title-action"
							href={
								"/wp-admin/post-new.php?post_type=" + model.slug
							}
						>
							Add New
						</a>
					</div>
				)}
			</div>
			<div className="d-flex flex-column">
				<Fields model={model} />
			</div>
			<TrashPostModal
				isOpen={trashPostModalIsOpen}
				setIsOpen={setTrashPostModalIsOpen}
			/>
		</div>
	);
}
