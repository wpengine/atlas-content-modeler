fs = require("fs");
const util = require("util");
const readFile = (fileName) => util.promisify(fs.readFile)(fileName, "utf8");

async function buildReadme() {
	let changelog = "";

	changelog = await readFile("../CHANGELOG.md");

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
		let writeLine = true;

		if (line.startsWith("## ")) {
			line = line.replace("## ", "\n= ") + " =\n";
		}

		if (line.startsWith("### ")) {
			changeType = "**" + line.replace("### ", "") + ":**";
			writeLine = false;
		}

		if (line == "") {
			changeType = "";
		}

		if (line.startsWith("- ")) {
			line = "* " + changeType + line.substring(1);
		}

		if (line != "" && writeLine) {
			processedLines.push(line);
		}
	});

	changelog = processedLines.join("\n");

	console.log(changelog);
}

buildReadme();
