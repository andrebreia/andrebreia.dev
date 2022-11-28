/** @type {import('tailwindcss').Config} */

const plugin = require("tailwindcss/plugin");

module.exports = {
    plugins: [
        require("@tailwindcss/aspect-ratio"),
        require("@tailwindcss/forms")({
            strategy: "base",
        }),
        plugin(function ({ addBase, theme }) {
            addBase({
                // ":root": {
                //     // Fluid typography from 1 rem to 1.2 rem with fallback to 16px.
                //     fontSize: "100%",
                //     "font-size": "clamp(1rem, 1.6vw, 1.2rem)",
                //     // Safari resize fix.
                //     minHeight: "0vw",
                // },
                // Used to hide alpine elements before being rendered.
                "[x-cloak]": {
                    display: "none !important",
                },
                // Implement the focus-visible polyfill: https://github.com/WICG/focus-visible
                ".js-focus-visible :focus:not(.focus-visible)": {
                    outline: "none",
                },
                // Display screen breakpoints in debug environment.
                ".breakpoint:before": {
                    display: "block",
                    color: theme("colors.notice.900"),
                    textTransform: "uppercase",
                    content: '"-"',
                },
                // Sizing utilities for sets in our bard (long form content).
                // On small devices they're full width.
                ".size-md, .size-lg, .size-xl": {
                    gridColumn: "span 12 / span 12",
                },
                "@media screen(md)": {
                    // Sizing utilities for sets in our bard (long form content).
                    // On larger devices they go from medium to extra large.
                    // (E.g. an image wider then text in an article.)
                    ".size-md": {
                        gridColumn: "span 8 / span 8",
                        gridColumnStart: "3",
                    },
                    ".size-lg": {
                        gridColumn: "span 8 / span 8",
                        gridColumnStart: "3",
                    },
                    ".size-xl": {
                        gridColumn: "span 10 / span 10",
                        gridColumnStart: "2",
                    },
                },
                "@media screen(lg)": {
                    // Sizing utilities for sets in our bard (long form content).
                    // On larger devices they go from medium to extra large.
                    ".size-md": {
                        gridColumn: "span 6 / span 6",
                        gridColumnStart: "4",
                    },
                    ".size-lg": {
                        gridColumn: "span 8 / span 8",
                        gridColumnStart: "3",
                    },
                    ".size-xl": {
                        gridColumn: "span 10 / span 10",
                        gridColumnStart: "2",
                    },
                },
            });
        }),
    ],
    theme: {
        // fontSize: {
        //     xs: ["0.8125rem", { lineHeight: "1.5rem" }],
        //     sm: ["0.875rem", { lineHeight: "1.5rem" }],
        //     base: ["1rem", { lineHeight: "1.75rem" }],
        //     lg: ["1.125rem", { lineHeight: "1.75rem" }],
        //     xl: ["1.25rem", { lineHeight: "2rem" }],
        //     "2xl": ["1.5rem", { lineHeight: "2rem" }],
        //     "3xl": ["1.875rem", { lineHeight: "2.25rem" }],
        //     "4xl": ["2rem", { lineHeight: "2.5rem" }],
        //     "5xl": ["3rem", { lineHeight: "3.5rem" }],
        //     "6xl": ["3.75rem", { lineHeight: "1" }],
        //     "7xl": ["4.5rem", { lineHeight: "1" }],
        //     "8xl": ["6rem", { lineHeight: "1" }],
        //     "9xl": ["8rem", { lineHeight: "1" }],
        // },
    },
};
