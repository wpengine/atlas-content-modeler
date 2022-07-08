import React from "react";
import AddIcon from "./AddIcon";
import BooleanIcon from "./BooleanIcon";
import CloseIcon from "./CloseIcon";
import DateIcon from "./DateIcon";
import DownArrow from "./DownArrow";
import EmailIcon from "./EmailIcon";
import ErrorIcon from "./ErrorIcon";
import ExternalLinkIcon from "./ExternalLink";
import InfoIcon from "./InfoIcon";
import LinkIcon from "./LinkIcon";
import MediaIcon from "./MediaIcon";
import MultipleChoiceIcon from "./MultipleChoiceIcon";
import NumberIcon from "./NumberIcon";
import OptionsIcon from "./OptionsIcon";
import RelationshipIcon from "./RelationshipIcon";
import ReorderIcon from "./ReorderIcon";
import RichTextIcon from "./RichTextIcon";
import SettingsIcon from "./SettingsIcon";
import TextIcon from "./TextIcon";
import TrashIcon from "./TrashIcon";
import TickIcon from "./TickIcon";
import UpArrow from "./UpArrow";
import FileIcon from "./FileIcon";
import ImgIcon from "./ImgIcon";
import AudioIcon from "./AudioIcon";

export default function Icon({ type, size, noCircle, width, height, color }) {
	switch (type) {
		case "add":
			return <AddIcon noCircle={noCircle} size={size} />;
		case "boolean":
			return <BooleanIcon />;
		case "close":
			return <CloseIcon />;
		case "date":
			return <DateIcon />;
		case "downarrow":
			return <DownArrow />;
		case "email":
			return <EmailIcon />;
		case "error":
			return <ErrorIcon size={size} />;
		case "external-link":
			return (
				<ExternalLinkIcon width={width} height={height} color={color} />
			);
		case "info":
			return <InfoIcon />;
		case "link":
			return <LinkIcon />;
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
		case "trash":
			return <TrashIcon />;
		case "uparrow":
			return <UpArrow />;
		case "audio":
			return <AudioIcon />;
		case "file":
			return <FileIcon />;
		case "multimedia":
			return <ImgIcon />;
		default:
			return "";
	}
}
