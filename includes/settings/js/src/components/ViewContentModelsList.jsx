import React, { useContext, useState } from "react";
import { Link, useHistory } from "react-router-dom";
import { ModelsContext } from "../ModelsContext";
import { getRootFields } from "../queries";
import { ContentModelDropdown } from "./ContentModelDropdown";

function HeaderWithAddNewButton() {
	let history = useHistory();
	return (
		<section className="heading">
			<h2>Content Models</h2>
			<button onClick={() => history.push("/wp-admin/admin.php?page=wpe-content-model&view=create-model")}>
				Add New
			</button>
		</section>
	);
}

export default function ViewContentModelsList() {
	const { models } = useContext(ModelsContext);
	const hasModels = Object.keys(models || {}).length > 0;

	return (
		<div className="app-card">
			<HeaderWithAddNewButton />
			<section className="card-content">
				{hasModels ? (
					<ul className="model-list">
						<ContentModels models={models} />
					</ul>
				) : (
					<>
						<p>
							You have no Content Models. It might be a good idea to create one now.
						</p>
						<ul aria-hidden="true">
							<li className="empty">
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
								<span>&nbsp;</span>
							</li>
						</ul>
					</>
				)}
			</section>
		</div>
	);
}

function ContentModels({ models }) {
	return Object.keys(models).map((slug) => {
		const { name, description, fields={} } = models[slug];
		return (
			<li key={slug}>
				<Link
					to={`/wp-admin/admin.php?page=wpe-content-model&view=edit-model&id=${slug}`}
					aria-label={`Edit ${name} content model`}
				>
					<span className="wide">
						<p className="label">Name</p>
						<p className="value">
							<strong>{name}</strong>
						</p>
					</span>
					<span className="widest">
						<p className="label">Description</p>
						<p className="value">{description}</p>
					</span>
					<span>
						<p className="label">Fields</p>
						<p className="value">{Object.keys(getRootFields(fields)).length}</p>
					</span>
					<span>
						<p className="label">Created</p>
						<p className="value">Jan 24, 2021</p>
					</span>
				</Link>
				<ContentModelDropdown model={models[slug]} />
			</li>
		);
	});
}
