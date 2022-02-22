/* global atlasContentModelerFormEditingExperience */
import React from "react";
import { PluginSidebar } from "@wordpress/edit-post";
import { Panel, PanelBody } from "@wordpress/components";
import { __ } from "@wordpress/i18n";
import ACMIcon from "./ACMIcon";
import Relationship from "../relationship";

const BlockEditorSidebar = () => {
	const { models, postType } = atlasContentModelerFormEditingExperience;
	const fields = Object.values(models[postType]?.fields) ?? [];

	return (
		<>
			<PluginSidebar
				name="acm-sidebar"
				title={__("Atlas Content Modeler", "atlas-content-modeler")}
				icon={<ACMIcon />}
			>
				<Panel>
					<PanelBody
						title={__("Relationships", "atlas-content-modeler")}
					>
						{fields.length === 0 && (
							<p>
								{__(
									"No fields for this post type.",
									"atlas-content-modeler"
								)}
							</p>
						)}

						{fields.map((field) => {
							return (
								<Relationship
									key={field.id}
									field={field}
									modelSlug={field.reference}
									required={field.required}
								/>
							);
						})}
					</PanelBody>
				</Panel>
			</PluginSidebar>
		</>
	);
};

export default BlockEditorSidebar;
