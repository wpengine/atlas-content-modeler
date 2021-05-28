1: Add react-beautiful-dnd to enable field reordering
=====================================================

Date: 2021-04-05

Context
-------

- A model's fields need to be reorderable via drag-and-drop, keyboard controls with screen reader output, and touch controls.

Decision
--------

We will use [react-beautiful-dnd](https://github.com/atlassian/react-beautiful-dnd) to enable field reordering.

The library is actively maintained by the Atlassian team, supports all needed features, performs well, has good documentation, and can be added with minimal impact on existing markup and styling.

Consequences
------------

- Production dependency on `react-beautiful-dnd` (which currently uses 7 other packages).
- Existing logic for keyboard-accessible drag-and-drop and aria reordering announcements have been removed. `react-beautiful-dnd` provides the same functionality.

Alternatives
------------

These alternatives were evaluated and rejected:

- Building drag-and-drop from scratch. Existing solutions handle touch, mouse, keyboard, and accessibility concerns. Recreating this manually exceeds a sprint's worth of work.
- [dnd-kit](https://dndkit.com/): Attractive because it has no dependencies. Currently marked beta. I encountered bugs with layout position during “keyboard sensor” reordering, which are captured in [#54](https://github.com/clauderic/dnd-kit/pull/54) and [#137](https://github.com/clauderic/dnd-kit/issues/137). This seems like the most promising alternative if we need to switch to another package in the future.
- [react-dnd](https://react-dnd.github.io/react-dnd/about): Requires either the [HTML5 backend](https://react-dnd.github.io/react-dnd/docs/backends/html5) (which does not support touch events) or the [touch backend](https://react-dnd.github.io/react-dnd/docs/backends/touch) which claims to support mouse events, but gives this warning about enabling them: "This is buggy due to the difference in touchstart/touchend event propagation compared to mousedown/mouseup/click."
- [sortable](https://github.com/SortableJS/Sortable): Requires one of two additional dependencies for React support. The [first](https://github.com/SortableJS/react-sortablejs) mentions, "Please note that this is not considered ready for production, as there are still a number of bugs being sent through." The [second](https://github.com/SortableJS/react-mixin-sortablejs) says, “this project needs a maintainer”.
- [draggable](https://shopify.github.io/draggable/): Attractive and well-documented, but no longer maintained by Shopify and is seeking additional maintainers. The [latest release](https://github.com/Shopify/draggable/releases/tag/v1.0.0-beta.12) in November 2020 is labelled beta and tagged as a pre-release. The [npm page](https://www.npmjs.com/package/@shopify/draggable) includes beta warnings and says the API is in flux.
