# Constraints — House of IRORI Website

Last reviewed: 2026-10-21 by Victor Hoang

## Floor (always enforced, no setup required)

- No new suppression comments: `@ts-ignore`, `eslint-disable`, `# noqa`, `# type: ignore`
- No unimplemented stubs: `throw new Error("Not implemented")`, empty `catch {}`
- No skipped or deleted tests without a reason in the commit message
- No secrets in source (Figma tokens and `.figma-cache/` must stay in `.gitignore`)
- This file does not get weakened to make a change pass

## Enforced with numbers

| Dimension | Rule | Checked by | Runs at |
|-----------|------|-----------|---------|
| JS Syntax | Zero syntax errors in JS files | `node --check js/main.js` | every edit |
| DOM & Structure | All 10 Home v3B sections, interactive sliders, and responsive breakpoints pass automated assertions | `node tests/verify-home.mjs` | task end |
| Secrets | No Figma tokens or credentials in tracked files | `git grep -E "figd_[A-Za-z0-9_-]{20,}" && exit 1 || exit 0` | every edit |
| Runtime Console | Zero `console.error` or uncaught page exceptions | Chrome DevTools MCP (`list_console_messages`) | preview verify |
| Performance | LCP ≤ 2500ms, CLS ≤ 0.1 on local preview | Chrome DevTools MCP (`lighthouse_audit`) | preview verify |
| Accessibility | Zero critical/serious accessibility errors on interactive controls | Chrome DevTools MCP (`lighthouse_audit`) | preview verify |

## Measured, not yet enforced

| Metric | Today | Direction |
|--------|-------|-----------|
| Total JS bundle size (`js/main.js`) | ≤ 15 KB (unminified) | must not exceed 25 KB |
| Total CSS size (`css/style.css`) | ≤ 35 KB (unminified) | must not exceed 50 KB |

## Exceptions

| ID | Rule | Path | Reason | Owner | Expires |
|----|------|------|--------|-------|---------|
| — | — | — | None | — | — |
