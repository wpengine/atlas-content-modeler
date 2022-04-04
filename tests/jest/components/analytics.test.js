import {
	isExcluded,
	sendEvent,
	sendPageView,
	shouldTrack,
	telemetryEnabled,
} from "../../../includes/shared-assets/js/analytics";

describe("telemetry", () => {
	test("telemetry should return true", () => {
		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: true,
		};
		window.atlasContentModeler = { usageTrackingEnabled: true };

		expect(telemetryEnabled()).toBeTruthy();
	});

	test("telemetry should return false", () => {
		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: false,
		};
		window.atlasContentModeler = { usageTrackingEnabled: false };

		expect(telemetryEnabled()).toBeFalsy();
	});
});

describe("analytics", () => {
	beforeAll(() => {
		global.window = Object.create(window);

		Object.defineProperty(window, "location", {
			value: {
				href: "",
			},
		});

		jest.resetAllMocks();
	});

	test("isExcluded should return true", () => {
		window.location.href = "http://www.wpengine.com";
		expect(isExcluded()).toBeTruthy();
	});
	test("isExcluded should return false", () => {
		window.location.href = "http://www.google.com";
		expect(isExcluded()).toBeFalsy();
	});

	test("shouldTrack should return true if the url does not contain wpengine.com", () => {
		window.location.href = "http://www.google.com";

		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: true,
		};
		window.atlasContentModeler = { usageTrackingEnabled: true };

		expect(shouldTrack()).toBeTruthy();
	});

	test("shouldTrack should return false if the url contains wpengine.com", () => {
		window.location.href = "http://www.wpengine.com";

		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: true,
		};
		window.atlasContentModeler = { usageTrackingEnabled: true };

		expect(shouldTrack()).toBeFalsy();
	});

	test.skip("sendEvent should call shouldTrack()", () => {
		const shouldTrackSpy = jest.spyOn(window, "shouldTrack");
		sendEvent({});
		expect(shouldTrackSpy).toHaveBeenCalled();
	});

	test.skip("sendPageView should call shouldTrack()", () => {
		const shouldTrackSpy = jest.spyOn(window, "shouldTrack");
		sendPageView({});
		expect(shouldTrackSpy).toHaveBeenCalled();
	});
});
