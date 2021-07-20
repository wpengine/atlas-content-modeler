/**
 * This script combines the readme.txt and CHANGELOG.md files in order to
 * ensure that a changelog is included for viewing in the plugin update service.
 */

const fs = require("fs");
const path = require("path");
const util = require("util");
const readFile = (fileName) => util.promisify(fs.readFile)(fileName, "utf8");

async function buildReadme() {
	let changelog = "";

	changelog = await readFile(path.join(__dirname, "../", "CHANGELOG.md"));

	changelog = changelog.replace(
		"# Atlas Content Modeler Changelog",
		"== Changelog =="
	);

	// split the contents by new line
	const origLines = changelog.split(/\r?\n/);
	const processedLines = [];
	let changeType = "";

	// print all lines
	origLines.forEach((line) => {
		let writeLine = true; // Allows us to skip a line if needed

		// Convert version number to proper format
		if (line.startsWith("## ")) {
			line = line.replace("## ", "\n= ") + " =\n";
		}

		// Convert the change type to a prefix
		if (line.startsWith("### ")) {
			changeType = "**" + line.replace("### ", "") + ":**";
			writeLine = false;
		}

		// Blank line resets the change type
		if (line == "") {
			changeType = "";
		}

		// Convert list items to proper WordPress format
		if (line.startsWith("- ")) {
			line = "* " + changeType + line.substring(1);
		}

		// Add the line back if we need to
		if (line != "" && writeLine) {
			processedLines.push(line);
		}
	});

	changelog = processedLines.join("\n");

	fs.appendFile(
		path.join(__dirname, "../", "readme.txt"),
		changelog,
		function (err) {
			if (err) throw err;
		}
	);
}

buildReadme();
