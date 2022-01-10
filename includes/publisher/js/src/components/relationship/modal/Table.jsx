import React from "react";
import { sprintf, __ } from "@wordpress/i18n";
import Icon from "acm-icons";
const { wp } = window;
const { date } = wp;

export default function Table({
	pagedEntries,
	page,
	field,
	chosenEntries,
	handleSelect,
}) {
	const {
		models,
		adminUrl,
		postType,
	} = atlasContentModelerFormEditingExperience;

	const entryName =
		models[postType]?.singular ?? __("Entry", "atlas-content-modeler");

	const createNewEntryLabel = sprintf(
		__(
			// translators: the singular name of the model, or "entry" if no singular name known.
			"Create a new %s.",
			"atlas-content-modeler"
		),
		models[field.reference]?.singular ??
			__("entry", "atlas-content-modeler")
	);

	/**
	 * Gets related posts of the given entry, excluding the current post
	 * that is being edited.
	 *
	 * @param {object} entry Post data for the current table row entry.
	 * @returns {array} Post IDs related to the entry.
	 */
	const relatedPosts = (entry) => {
		const urlParams = new URLSearchParams(window.location.search);
		const editedPostId = urlParams.get("post");

		return (
			entry?.acm_related_posts?.filter((id) => id != editedPostId) ?? []
		);
	};

	/**
	 * Checks if the passed `entry` is already connected to posts other than
	 * the one being edited.
	 *
	 * Allows posts with existing relationships to be made unselectable for
	 * one-to-one and one-to-many relationship fields.
	 *
	 * @param {object} entry Post data for the current table row entry.
	 * @returns {boolean} True if there are related posts.
	 */
	const entryHasRelatedPosts = (entry) => {
		const related = relatedPosts(entry);

		return related?.length > 0;
	};

	/**
	 * Displays a link to edit the post that is related to the passed `entry`.
	 *
	 * @param {object} entry Post data for the current row.
	 * @returns ReactElement
	 */
	const RelatedPostLink = ({ entry }) => {
		const related = relatedPosts(entry);

		if (related?.length < 1) {
			return "";
		}

		/**
		 * There should only be one link for the one-to-one and one-to-many
		 * cardinality types where we're checking for related links, so just
		 * use the first array item as the related post id.
		 */
		const [id] = related;

		return (
			<p>
				<a
					href={`${adminUrl}post.php?post=${id}&action=edit`}
					target="_blank"
					rel="noopener noreferrer"
					aria-label={sprintf(
						__(
							// translators: the entry name, such as “Cat”.
							"View the linked %s in a new tab.",
							"atlas-content-modeler"
						),
						entryName
					)}
				>
					{sprintf(
						// translators: the entry name, such as “Cat”.
						__("View the linked %s", "atlas-content-modeler"),
						entryName
					)}
				</a>
				.
			</p>
		);
	};

	return (
		<table className="table table-striped mt-2">
			<thead>
				<tr>
					<th></th>
					<th>{__("Title", "atlas-content-modeler")}</th>
					<th>{__("Last modified", "atlas-content-modeler")}</th>
				</tr>
			</thead>
			<tbody>
				{pagedEntries[page]?.length === 0 && (
					<tr className="empty">
						<td>&nbsp;</td>
						<td colSpan="2">
							<span>
								{__(
									"No published entries.",
									"atlas-content-modeler"
								)}{" "}
								<a
									href={`${adminUrl}post-new.php?post_type=${field.reference}`}
									target="_blank"
									rel="noopener noreferrer"
									className="d-inline-flex"
									aria-label={
										createNewEntryLabel +
										" " +
										__(
											"Opens in a new window.",
											"atlas-content-modeler"
										)
									}
								>
									{createNewEntryLabel}
									<Icon type="external-link" />
								</a>
							</span>
						</td>
					</tr>
				)}
				{pagedEntries[page].map((entry) => {
					const { modified, id, title } = entry;
					const selectEntryLabel = sprintf(
						__(
							/* translators: %s The name of the entry title. */
							"Link the entry titled “%s” to this entry.",
							"atlas-content-modeler"
						),
						title?.rendered
					);
					const notSelectable =
						(field?.cardinality === "one-to-one" ||
							field?.cardinality === "one-to-many") &&
						entryHasRelatedPosts(entry);

					return (
						<tr
							key={id}
							className={notSelectable ? "unselectable" : ""}
						>
							{notSelectable ? (
								<td className="info tooltip">
									<button
										type="button"
										aria-label={sprintf(
											__(
												// translators:
												// 1: entry title, such as “John Smith”.
												// 2: entry type, such as “Cat”.
												"“%s” is not selectable. It is already linked to a %2$s.",
												"atlas-content-modeler"
											),
											title?.rendered,
											entryName
										)}
									>
										<Icon type="info" />
									</button>
									<span className="tooltip-text">
										{sprintf(
											__(
												// translators:
												// 1: entry title, such as “John Smith”.
												// 2: entry type, such as “Cat”.
												"“%1$s” is already linked to a %2$s.",
												"atlas-content-modeler"
											),
											title?.rendered,
											entryName
										)}
										<RelatedPostLink entry={entry} />
									</span>
								</td>
							) : (
								<td className="checkbox">
									<input
										type={
											field.cardinality ===
												"one-to-many" ||
											field.cardinality === "many-to-many"
												? "checkbox"
												: "radio"
										}
										name="selected-entry"
										id={`entry-${id}`}
										value={id}
										aria-label={selectEntryLabel}
										defaultChecked={chosenEntries.includes(
											id.toString()
										)}
										onChange={handleSelect}
									/>
								</td>
							)}
							<td>
								<label
									htmlFor={`entry-${id}`}
									aria-label={selectEntryLabel}
									aria-disabled={notSelectable}
								>
									{title?.rendered}
								</label>
							</td>
							<td>
								<label
									htmlFor={`entry-${id}`}
									aria-label={selectEntryLabel}
									aria-disabled={notSelectable}
								>
									{date.dateI18n("F j, Y", modified)}
								</label>
							</td>
						</tr>
					);
				})}
			</tbody>
		</table>
	);
}
