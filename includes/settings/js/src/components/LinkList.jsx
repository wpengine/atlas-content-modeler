import React from "react";

/**
 *
 * @param {string} classes - Classes for the list ul.
 * @param {array} links - Links for the list { index, classNames, styles, title, url, target, icon, hideTitle }.
 * @returns html
 */
export default function LinkList({ classes, linkOptions }) {
	const { options, links } = linkOptions;
	const defaultOptions = {
		liClasses: "",
		aClasses: "",
		liStyles: {},
		aStyles: [],
	};

	options = { ...defaultOptions, ...options };

	function getLinks() {
		return links.map((link, index) => (
			<li
				key={link.index || index}
				styles={link.styles || options.liStyles}
				className={link.classNames || options.liClasses}
				style={{
					borderLeft: "none",
					boxShadow: "none",
				}}
			>
				<a
					styles={link.styles || options.aStyles}
					className={link.classNames || options.aClasses}
					title={link.title}
					href={link.url}
					target={link.target || "_blank"}
					rel="noreferrer"
				>
					{link.icon && <span className={link.icon}></span>}
					{link.title && !link.hideTitle && <span>{link.title}</span>}
				</a>
			</li>
		));
	}

	return <ul className={classes}>{getLinks()}</ul>;
}
