/** @jsx jsx */
import React, { useContext, useEffect } from "react";
import { Link, useHistory } from "react-router-dom";
import { ModelsContext } from "../ModelsContext";
import { sanitizeFields } from "../queries";
import { sendEvent } from "acm-analytics";
import { ContentModelDropdown } from "./ContentModelDropdown";
import { __ } from "@wordpress/i18n";
import { jsx, css } from "@emotion/react";
import { Button } from "../../../../shared-assets/js/components/Buttons";
import { Card } from "../../../../shared-assets/js/components/card";

function Header({ showButtons = true }) {
	let history = useHistory();
	return (
		<section
			css={css`
				justify-content: space-between;
				margin-bottom: 28px;
			`}
			className="flex-wrap d-flex flex-column d-sm-flex flex-sm-row"
		>
			<h2>Content Models</h2>
			{showButtons && (
				<Button
					data-testid="new-model-button"
					onClick={() =>
						history.push(
							atlasContentModeler.appPath + "&view=create-model"
						)
					}
				>
					{__("New Model", "atlas-content-modeler")}
				</Button>
			)}
		</section>
	);
}

export default function ViewContentModelsList() {
	const { models } = useContext(ModelsContext);
	const hasModels = Object.keys(models || {}).length > 0;
	const history = useHistory();

	useEffect(() => {
		sendEvent({
			category: "Models",
			action: "View Models List",
		});
	}, []);

	return (
		<Card>
			<Header showButtons={hasModels} />
			<section className="card-content">
				{hasModels ? (
					<ul className="model-list">
						<ContentModels models={models} />
					</ul>
				) : (
					<div className="model-list-empty">
						<p>
							{__(
								"You currently have no Content Models.",
								"atlas-content-modeler"
							)}
						</p>
						<Button
							onClick={() =>
								history.push(
									atlasContentModeler.appPath +
										"&view=create-model"
								)
							}
						>
							{__("Get Started", "atlas-content-modeler")}
						</Button>
					</div>
				)}
			</section>
		</Card>
	);
}

function ContentModels({ models }) {
	return Object.keys(models).map((slug) => {
		const { plural, description, fields = {} } = models[slug];
		return (
			<li key={slug}>
				<Link
					to={`/wp-admin/admin.php?page=atlas-content-modeler&view=edit-model&id=${slug}`}
					aria-label={`Edit ${plural} content model`}
					className="flex-wrap d-flex flex-column d-sm-flex flex-sm-row"
				>
					<span className="flex-item mb-3 mb-sm-0 pr-1">
						<p className="label">
							{__("Name", "atlas-content-modeler")}
						</p>
						<p className="value">
							<strong>{plural}</strong>
						</p>
					</span>
					<span className="flex-item mb-3 mb-sm-0 pr-1">
						<p className="label">
							{__("Description", "atlas-content-modeler")}
						</p>
						<p className="value">{description}</p>
					</span>
					<span className="flex-item">
						<p className="label">
							{__("Fields", "atlas-content-modeler")}
						</p>
						<p className="value">
							{Object.keys(sanitizeFields(fields)).length}
						</p>
					</span>
				</Link>
				<div>
					<ContentModelDropdown model={models[slug]} />
				</div>
			</li>
		);
	});
}
