---
id: 20f2b511-e7ac-409a-9d35-fda3e580fc4e
blueprint: project
title: 'Raycast: Statamic Docs'
tags: 'Developer Tools, Open Source'
year: '2023'
icon: 'solar:document-text-linear'
logo: projects/thumbs/raycast-statamic-docs.webp
excerpt: 'A Raycast extension that lets Statamic developers search the official documentation quickly from their launcher without switching context.'
external_url: 'https://www.raycast.com/andrebreia/statamic-docs'
template: project
layout: false
body:
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'A Raycast extension that allows developers to search through the Statamic documentation directly from their launcher.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'View on Raycast Store'
        marks:
          -
            type: link
            attrs:
              href: 'https://www.raycast.com/andrebreia/statamic-docs'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: Problem
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'When working with Statamic, I often needed to jump into the docs quickly without switching context, opening a browser tab, and manually searching. Raycast is already central to my day-to-day workflow, so a dedicated extension was a natural fit.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: Role
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'I built and published the extension as an open-source developer tool. The work included structuring the command, wiring it into Statamic documentation search, shaping the UI for quick scanning, and preparing it for the Raycast Store.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: Stack
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Raycast Extensions, TypeScript, React, API requests, and the Statamic documentation source.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: 'Implementation highlights'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'The extension needed to be fast and predictable. Search results had to be useful at a glance, with enough context to choose the right documentation page without opening several results.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'I kept the interface intentionally small: search, scan, open. For developer tools, the best UI is often the one that disappears after it gets you to the right answer.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: Outcome
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'The extension gives Statamic developers a quicker way to reach the docs from their keyboard. It also reflects the kind of small, practical tooling I like building around real workflow friction.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Related services: '
      -
        type: text
        text: 'Full-Stack Development'
        marks:
          -
            type: link
            attrs:
              href: /services/full-stack-development/
      -
        type: text
        text: ' and '
      -
        type: text
        text: 'API & Integrations'
        marks:
          -
            type: link
            attrs:
              href: /services/api-integrations/
      -
        type: text
        text: .
---
