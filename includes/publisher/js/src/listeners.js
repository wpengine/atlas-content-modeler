/**
 * Event listeners to adapt default WordPress publish page behavior.
 */

/**
 * Adds a 'submitted' class when the post form is submitted.
 *
 * Allows styling of :invalid input fields only when the form was submitted.
 * Prevents an issue where error messages appear for required fields when the
 * form is first loaded.
 */
export function postSubmittedListener() {
	const form = document.querySelector("form#post");
	const publishButton = document.querySelector("input#publish");
	const addSubmitted = () => form.classList.add("submitted");

	publishButton.addEventListener("click", addSubmitted);
	form.addEventListener("submit", addSubmitted);
}

/**
 * Calls the callback if the “Move To Trash” link is clicked.
 */
export function moveToTrashListener() {
	const moveToTrashLink = document.querySelector("#delete-action a");

	moveToTrashLink.addEventListener("click", (e) => {
		if (atlasContentModelerFormEditingExperience?.postHasReferences) {
			e.preventDefault();

			if (confirm("Are you sure you want to delete this?")) {
				window.location = moveToTrashLink.getAttribute("href");
			}
		}
	});
}
