import React, { useContext, useState } from "react";
import { Link, useHistory } from "react-router-dom";
import { ModelsContext } from "../ModelsContext";
import { getRootFields } from "../queries";
import { ContentModelDropdown } from "./ContentModelDropdown";

function Header({ showButton = true }) {
	let history = useHistory();
	return (
		<section className="heading flex-wrap d-flex flex-column d-sm-flex flex-sm-row">
			<h2>Content Models</h2>
			{showButton && (
				<button
					onClick={() =>
						history.push(
							"/wp-admin/admin.php?page=wpe-content-model&view=create-model"
						)
					}
				>
					Add New
				</button>
			)}
		</section>
	);
}

export default function ViewContentModelsList() {
	const { models } = useContext(ModelsContext);
	const hasModels = Object.keys(models || {}).length > 0;
	const history = useHistory();

	return (
		<div className="app-card">
			<Header showButton={hasModels} />
			<section className="card-content">
				{hasModels ? (
					<ul className="model-list">
						<ContentModels models={models} />
					</ul>
				) : (
					<div className="model-list-empty">
						<p>You currently have no Content Models.</p>
						<button
							onClick={() =>
								history.push(
									"/wp-admin/admin.php?page=wpe-content-model&view=create-model"
								)
							}
						>
							Get Started
						</button>
					</div>
				)}
			</section>
		</div>
	);
}

function ContentModels({ models }) {
	return Object.keys(models).map((slug) => {
		const { name, description, fields = {} } = models[slug];
		return (
			<li key={slug}>
				<Link
					to={`/wp-admin/admin.php?page=wpe-content-model&view=edit-model&id=${slug}`}
					aria-label={`Edit ${name} content model`}
					className="flex-wrap d-flex flex-column d-sm-flex flex-sm-row"
				>
					<span className="flex-item mb-3 mb-sm-0 pr-1">
						<p className="label">Name</p>
						<p className="value">
							<strong>{name}</strong>
						</p>
					</span>
					<span className="flex-item mb-3 mb-sm-0 pr-1">
						<p className="label">Description</p>
						<p className="value">{description}</p>
					</span>
					<span className="flex-item">
						<p className="label">Fields</p>
						<p className="value">
							{Object.keys(getRootFields(fields)).length}
						</p>
					</span>
				</Link>
				<div className="neg-margin-wrapper">
					<ContentModelDropdown model={models[slug]} />
				</div>
			</li>
		);
	});
}
