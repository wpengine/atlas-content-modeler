import analytics from "../../../includes/shared-assets/js/analytics";

beforeEach(() => {
	jest.resetAllMocks();
});

describe("telemetry", () => {
	test("telemetry should return true", () => {
		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: true,
		};
		window.atlasContentModeler = { usageTrackingEnabled: true };

		expect(analytics.telemetryEnabled()).toBeTruthy();
	});

	test("telemetry should return false", () => {
		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: false,
		};
		window.atlasContentModeler = { usageTrackingEnabled: false };

		expect(analytics.telemetryEnabled()).toBeFalsy();
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
	});

	test("isExcluded should return true", () => {
		window.location.href = "http://www.wpengine.com";
		expect(analytics.isExcluded()).toBeTruthy();
	});
	test("isExcluded should return false", () => {
		window.location.href = "http://www.google.com";
		expect(analytics.isExcluded()).toBeFalsy();
	});

	test("shouldTrack should return true if the url does not contain wpengine.com", () => {
		window.location.href = "http://www.google.com";

		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: true,
		};
		window.atlasContentModeler = { usageTrackingEnabled: true };

		expect(analytics.shouldTrack()).toBeTruthy();
	});

	test("shouldTrack should return false if the url contains wpengine.com", () => {
		window.location.href = "http://www.wpengine.com";

		window.atlasContentModelerFormEditingExperience = {
			usageTrackingEnabled: true,
		};
		window.atlasContentModeler = { usageTrackingEnabled: true };

		expect(analytics.shouldTrack()).toBeFalsy();
	});
});
