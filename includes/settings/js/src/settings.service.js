import { __ } from "@wordpress/i18n";
import { dispatch } from "@wordpress/data";

export function saveUsageTrackingSetting(event) {
	// @todo catch save errors
	dispatch("core").saveSite({
		atlas_content_modeler_usage_tracking: event.target.value,
	});
}
