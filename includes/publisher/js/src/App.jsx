import React, { useState } from "react";
import Fields from "./components/Fields";
import { __ } from "@wordpress/i18n";
import TrashPostModal from "./components/TrashPostModal";

export default function App({ model, mode }) {
	const isEditMode = mode === "edit";
	const [trashPostModalIsOpen, setTrashPostModalIsOpen] = useState(false);

	// GA config
	let gaConfig = {
		measurement_id: "G-S056CLLZ34",
		events: [
			{
				name: "some_event",
				params: {
					anonymize_ip: true,
				},
			},
		],
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
	function ga(formData) {
		// add anonymize ip to every single event param object
		const defaultEventParams = { anonymize_ip: true };
		// map defaults to all events
		formData.events.map((event) => {
			return { ...event.params, ...defaultEventParams };
		});
		// call ga api
		return apiFetch({
			path: "/wpe/atlas/ga_analytics",
			method: "POST",
			_wpnonce: wpApiSettings.nonce,
			formData,
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
		const formData = {
			t: "event", // pageview?
			ec: "Console",
			ea: "Submit",
			el: "Test Form Submission",
		};

		ga(formData);
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
