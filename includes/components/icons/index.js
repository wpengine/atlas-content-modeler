import React from "react";
import AddIcon from "./AddIcon";
import BooleanIcon from "./BooleanIcon";
import CloseIcon from "./CloseIcon";
import DateIcon from "./DateIcon";
import DownArrow from "./DownArrow";
import ErrorIcon from "./ErrorIcon";
import MediaIcon from "./MediaIcon";
import MultipleChoiceIcon from "./MultipleChoiceIcon";
import NumberIcon from "./NumberIcon";
import OptionsIcon from "./OptionsIcon";
import RelationshipIcon from "./RelationshipIcon";
import ReorderIcon from "./ReorderIcon";
import RichTextIcon from "./RichTextIcon";
import SettingsIcon from "./SettingsIcon";
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
		case "multipleChoice":
			return <MultipleChoiceIcon />;
		case "number":
			return <NumberIcon />;
		case "options":
			return <OptionsIcon />;
		case "relationship":
			return <RelationshipIcon />;
		case "reorder":
			return <ReorderIcon />;
		case "richtext":
			return <RichTextIcon />;
		case "settings":
			return <SettingsIcon />;
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
