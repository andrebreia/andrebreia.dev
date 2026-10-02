---
id: e42ec0de-90ff-498a-a923-0b0c797cfaf6
blueprint: project
title: VerifyWall
tags: 'SaaS, API, Security'
year: '2026'
icon: 'solar:shield-check-linear'
logo: projects/thumbs/verifywall.webp
excerpt: 'An API service that protects online platforms from fake sign-ups, disposable emails, VPN abuse, and bot registrations.'
external_url: 'https://verifywall.com?ref=andrebreia.dev'
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
        text: 'VerifyWall is an API service that protects online platforms from fake sign-ups, disposable emails, VPN abuse, and bot registrations. A single endpoint cross-checks email addresses, domains, and IP addresses against threat intelligence to return a clear risk assessment.'
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
        text: 'Many SaaS products only discover account abuse after it has already created support, billing, moderation, or deliverability problems. The product needed to make that risk visible at sign-up time without adding friction for legitimate users.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'The API had to be simple enough for developers to adopt quickly, but reliable enough to sit inside a critical onboarding flow.'
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
        text: 'I designed and built the core product as a full-stack SaaS application. That included the public API, authentication, dashboard flows, risk checks, billing foundations, documentation, deployment, and the surrounding operational pieces needed to keep the service useful.'
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
        text: 'Laravel, PHP, React paired with Inertia, TailwindCSS, external data providers, background jobs, API authentication, and deployment automation.'
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
        text: 'The main work was turning multiple risk signals into a predictable API response. That meant normalizing provider data, handling failures carefully, and keeping the response shape clear enough that customers can make product decisions from it.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'The application also needed the usual SaaS foundations: account management, usage tracking, API keys, documentation, and internal visibility into how checks are being used.'
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
        text: 'VerifyWall is a focused SaaS product with a clear developer-facing API and a practical integration path for platforms that need stronger sign-up protection.'
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
        text: ', '
      -
        type: text
        text: 'MVP Development'
        marks:
          -
            type: link
            attrs:
              href: /services/mvp-development/
      -
        type: text
        text: ', and '
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
