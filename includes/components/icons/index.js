import React from "react";
import AddIcon from "./AddIcon";
import BooleanIcon from "./BooleanIcon";
import CloseIcon from "./CloseIcon";
import DateIcon from "./DateIcon";
import DownArrow from "./DownArrow";
import ErrorIcon from "./ErrorIcon";
import MediaIcon from "./MediaIcon";
import MultiChoiceIcon from "./MultiChoiceIcon";
import NumberIcon from "./NumberIcon";
import OptionsIcon from "./OptionsIcon";
import ReorderIcon from "./ReorderIcon";
import RichTextIcon from "./RichTextIcon";
import TextIcon from "./TextIcon";
import TickIcon from "./TickIcon";
import UpArrow from "./UpArrow";

export default function Icon({ type, size }) {
	switch (type) {
		case "add":
			return <AddIcon size={size} />;
		case "boolean":
			return <BooleanIcon />;
		case "close":
			return <CloseIcon />;
		case "date":
			return <DateIcon />;
		case "downarrow":
			return <DownArrow />;
		case "error":
			return <ErrorIcon size={size} />;
		case "media":
			return <MediaIcon />;
		case "multiOption":
			return <MultiChoiceIcon />;
		case "number":
			return <NumberIcon />;
		case "options":
			return <OptionsIcon />;
		case "reorder":
			return <ReorderIcon />;
		case "richtext":
			return <RichTextIcon />;
		case "text":
			return <TextIcon />;
		case "tick":
			return <TickIcon />;
		case "uparrow":
			return <UpArrow />;
		default:
			return "";
	}
}
