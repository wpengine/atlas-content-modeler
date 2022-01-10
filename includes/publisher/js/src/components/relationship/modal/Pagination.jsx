import React from "react";
import { sprintf, __ } from "@wordpress/i18n";

export default function Pagination({ totalPages, page, setPage }) {
	return (
		<div className="d-flex flex-row-reverse">
			{totalPages > 1 && (
				<div className="d-flex flex-row">
					<button
						href="#"
						className="tertiary relationship-modal-nav"
						disabled={page === 1}
						aria-label={__("First page", "atlas-content-modeler")}
						onClick={(event) => {
							event.preventDefault();
							setPage(1);
						}}
					>
						{"<<"}
					</button>
					<button
						href="#"
						className="tertiary relationship-modal-nav"
						disabled={page === 1}
						aria-label={__(
							"Previous page",
							"atlas-content-modeler"
						)}
						onClick={(event) => {
							event.preventDefault();
							setPage(page - 1);
						}}
					>
						{"<"}
					</button>
					<button
						href="#"
						className="tertiary relationship-modal-nav"
						disabled={page === totalPages}
						aria-label={__("Next page", "atlas-content-modeler")}
						onClick={(event) => {
							event.preventDefault();
							setPage(page + 1);
						}}
					>
						{">"}
					</button>
					<button
						href="#"
						className="tertiary relationship-modal-nav"
						disabled={page === totalPages}
						aria-label={__("Last page", "atlas-content-modeler")}
						onClick={(event) => {
							event.preventDefault();
							setPage(totalPages);
						}}
					>
						{">>"}
					</button>
				</div>
			)}
			{totalPages > 0 && (
				<div className="mx-3">
					<span
						className="align-middle"
						style={{ lineHeight: "55px" }}
					>
						{sprintf(
							__("Page %d of %d", "atlas-content-modeler"),
							page,
							totalPages
						)}
					</span>
				</div>
			)}
		</div>
	);
}
