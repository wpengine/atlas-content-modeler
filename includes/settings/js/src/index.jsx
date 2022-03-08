import React from "react";
import ReactDOM from "react-dom";
import App from "./App";
import "../../scss/index.scss";
import { ThemeProvider } from "@emotion/react";
import theme from "../../../shared-assets/js/theme";

ReactDOM.render(
	<ThemeProvider theme={theme}>
		<App />
	</ThemeProvider>,
	document.getElementById("root")
);
