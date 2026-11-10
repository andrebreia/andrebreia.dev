---
id: 3659e73e-c4d2-45e6-a0b9-fd497b3787f5
blueprint: service
title: 'Prototype to Production'
seoTitle: 'Make Your Vibe-Coded App Production-Ready'
headline: 'Making Vibe-Coded Apps Ready for Production'
icon: 'solar:rocket-2-linear'
description: 'Taking apps built with AI tools like Lovable, Bolt, or Cursor, and getting them ready for real users.'
excerpt: 'Fixing or rewriting vibe-coded apps built with Lovable, Bolt, Cursor, or Claude Code, so they’re secure and ready for production.'
order: 2
template: service
layout: false
body:
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Tools like Lovable, Bolt, Replit, Cursor, and Claude Code make it possible to go from an idea to a working app really quickly. I use AI coding agents every day myself, so I get the appeal. For getting something in front of people and seeing if the idea works, they’re great.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Where it gets harder is the step after that. Real users, real data, payments. That’s usually when the problems start to show up, and when it’s worth having someone experienced look at the code and get it production-ready.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: 'What I usually find in vibe-coded apps'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Most of the issues are the same, regardless of the tool used to build the app. API keys sitting in the front-end code. Supabase tables without row-level security, or Firebase rules that let one user read another user’s data. Permissions that are only checked in the UI. Payments and subscriptions that get out of sync.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Then there’s the stuff that makes an app hard to keep running. No tests, no error tracking, no backups, and a codebase where every new prompt seems to break something else.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: 'Fix or rewrite?'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'I start by reading through the code and following the flows that matter — sign-up, login, permissions, payments, and anything that touches personal data. From there, I’ll tell you what I think makes sense.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Most of the time, that means keeping what works and fixing the risky parts, starting with security. Sometimes the foundation isn’t worth saving. The data model doesn’t fit the product, or fixing it would mean touching almost every file. In that case a rewrite ends up being cheaper in the long run, and I’ll usually '
      -
        type: text
        marks:
          -
            type: link
            attrs:
              href: /services/full-stack-development/
        text: 'rebuild it in Laravel'
      -
        type: text
        text: ' using the prototype as the spec. A working version of what you want is a better starting point than most projects get.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'I’ll only recommend a rewrite when it actually makes sense.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: 'How I get an app ready for production'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'First, a review. I go through the codebase and write up what I found, what needs fixing first, and whether I think a rewrite is worth it. You get a clear scope and a quote before any work starts.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Then I work through the fixes, or the rebuild. Security first, then anything that could lose data or money, then tests around the parts that matter most.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'The code stays in your repository the whole time. It’s yours from day one.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'If you’d like me to host it, I’ll also set up the deployment process and a CI/CD pipeline. Error tracking can be part of an ongoing maintenance contract, or I can set it up for you using your own account on a tool like Sentry.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: Payments
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Payments are something I pay extra attention to. I’m a Stripe Certified Architect, and payment issues are one of the most common problems in AI-built apps — Stripe webhooks that aren’t verified, subscriptions that don’t match what users actually have access to, that kind of thing. If your app takes money, it’s one of the first things I’ll look at. There’s more about that side of my work on the '
      -
        type: text
        marks:
          -
            type: link
            attrs:
              href: /services/api-integrations/
        text: 'API & Integrations'
      -
        type: text
        text: ' page.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: 'Who it’s for'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Founders who built an MVP with Lovable, Bolt, Replit, v0, Cursor, or Claude Code and want to put real customers through it. Teams with an internal tool that grew faster than anyone expected. Agencies that inherited an AI-built project and want to know what they’re dealing with.'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'If that sounds like you, send me what you’ve built and I’ll take a look.'
  -
    type: heading
    attrs:
      level: 2
      textAlign: left
    content:
      -
        type: text
        text: 'Questions I can help answer'
  -
    type: heading
    attrs:
      level: 3
      textAlign: left
    content:
      -
        type: text
        text: 'Is my vibe-coded app safe to launch?'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'If it handles user data or payments, I wouldn’t launch it without someone reviewing the code first. AI tools are good at making things work, but they often skip the parts you can’t see, like access rules and server-side checks.'
  -
    type: heading
    attrs:
      level: 3
      textAlign: left
    content:
      -
        type: text
        text: 'Do I need to rewrite my app?'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Not usually. Most AI-built apps have plenty worth keeping. The review is there to work out which parts can be fixed and which would cost more to patch than to rebuild.'
  -
    type: heading
    attrs:
      level: 3
      textAlign: left
    content:
      -
        type: text
        text: 'What tools and stacks do you work with?'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Apps built with Lovable, Bolt, Replit, v0, Cursor, Claude Code, and similar tools. That’s usually React or Next.js with Supabase or Firebase, or Laravel. If it needs a rewrite, Laravel is where I’m strongest.'
  -
    type: heading
    attrs:
      level: 3
      textAlign: left
    content:
      -
        type: text
        text: 'Can I keep using AI tools afterwards?'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'Yes. I’ll leave tests and guidelines for AI agents in the project, so you can keep building with them without breaking what’s already working.'
  -
    type: heading
    attrs:
      level: 3
      textAlign: left
    content:
      -
        type: text
        text: 'How long does it take?'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'The review usually takes a few days. Fixes are normally a matter of weeks. A rewrite takes longer, but you’ll get an estimate before committing to anything.'
  -
    type: heading
    attrs:
      level: 3
      textAlign: left
    content:
      -
        type: text
        text: 'How much does it cost?'
  -
    type: paragraph
    attrs:
      textAlign: left
    content:
      -
        type: text
        text: 'It depends on the size of the app and what the review finds. You’ll get a quote before any fixes or rebuild work starts, so there are no surprises.'
---
