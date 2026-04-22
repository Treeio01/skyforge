# Frontend Refactoring — Design Spec

**Date:** 2026-04-23
**Project:** Skyforge
**Status:** Approved
**Approach:** Layer-by-layer (Вариант Б)

## Context

Full frontend refactoring of the Skyforge CS:GO skin upgrade platform (Laravel + Inertia.js + React + TypeScript + Tailwind v4). Site is in development, no production constraints. Backend changes allowed where needed to support frontend improvements.

## Constraints

- Tailwind CSS v4 — config lives in CSS `@theme` block, no `tailwind.config.js`
- Custom breakpoints `550px` and `1155px` must be preserved (formalized as `xs` and `wide`)
- Inertia.js v2 — use deferred props, partial reloads, prefetching
- New npm packages allowed: `framer-motion`, `@tanstack/react-virtual`
- One branch, no phased PRs

## Goals

1. Maximum smoothness and visual quality
2. Clean component architecture (no monoliths)
3. Proper Inertia.js patterns throughout
4. Full TypeScript strictness (no `as any`)
5. Skeleton loaders everywhere data loads async
6. Mobile-responsive all pages
7. Proper error handling (no silent catches)

---

## Phase 1 — Foundation

### Delete empty files

Remove 5 unused empty pages:
- `resources/js/Pages/Welcome.tsx`
- `resources/js/Pages/Auth/Login.tsx`
- `resources/js/Pages/Deposit/Create.tsx`
- `resources/js/Pages/Profile/History.tsx`
- `resources/js/Pages/ProvablyFair/Verify.tsx`

### Remove debug code

- `DEBUG_SHOW = false` constant in `Components/Upgrade/UpgradeBlock.tsx`
- All `console.log` calls in `Components/Deposit/DepositModal.tsx`

### Tailwind v4 theme — breakpoints and colors

Add to the main CSS file (`resources/css/app.css`) inside `@theme`:

```css
@theme {
  /* Custom breakpoints */
  --breakpoint-xs: 550px;
  --breakpoint-wide: 1155px;

  /* Brand colors */
  --color-brand: #4E89FF;
  --color-brand-hover: #3a6fd4;

  /* Surface colors */
  --color-surface: #070A10;
  --color-surface-secondary: #0A0E17;
  --color-surface-elevated: #111827;

  /* Text colors */
  --color-text-muted: #BED4FF;
  --color-text-dim: #6B7FA3;

  /* State colors */
  --color-success: #22c55e;
  --color-danger: #ef4444;
  --color-warning: #f59e0b;
}
```

Usage in JSX: `bg-surface`, `text-brand`, `xs:flex`, `wide:grid-cols-4`.

### Shared component library

Create/update in `resources/js/Components/UI/`:

**`Button.tsx`** — replaces `GradientButton` and all inline styled buttons.
- Variants: `primary` (gradient blue), `ghost` (transparent border), `danger` (red)
- Props: `loading?: boolean` (shows spinner), `disabled`, `size: 'sm' | 'md' | 'lg'`
- Fix: gradient hover shadow via `style` prop, not template string in `className`

**`Input.tsx`** — base text input used across all forms.
- Props: `error?: string`, `label?: string`, `hint?: string`
- Error state shows red border + message below

**`NumberInput.tsx`** — extends `Input` with numeric formatting.
- Props: `min`, `max`, `step`, `prefix` (currency symbol), `suffix`

**`Skeleton.tsx`** — base skeleton block with `animate-pulse`.
- Props: `className`, `rounded`
- Composed into: `SkeletonSkinCard`, `SkeletonFeedItem`, `SkeletonDepositForm`

**`Badge.tsx`** — unified rarity badge replacing 3 different implementations.
- Props: `rarity: string` — maps to CSS variable `--rarity-*` color

**`Icons.tsx`** — single unified icon file.
- Merges: `Components/Layout/Header/icons.tsx`, `Components/Upgrade/icons.tsx`, inline icons from `DepositModal`
- Usage: `<Icons.Steam />`, `<Icons.Wallet />`, etc.

### TypeScript fixes

Extend `PageProps` in `resources/js/types/index.d.ts`:

```ts
interface PageProps {
  auth: { user: User | null };
  flash: { success?: string; error?: string };
  stats?: { online: number; upgrades_today: number };
  socials?: Record<string, string>;
}
```

Remove all `as any` casts (~6 locations):
- `Components/Layout/Header/index.tsx` — `(props as any).stats`
- `Components/Layout/Header/sections/SocialLinks.tsx` — `(props as any).socials`
- `Components/Upgrade/UpgradeBlock.tsx` — `(page.props as any).flash`

---

## Phase 2 — Component Decomposition

### DepositModal.tsx (658 lines → 7 files)

```
resources/js/Components/Deposit/
├── DepositModal.tsx          (~80 lines)  — orchestrator, opens/closes, passes state down
├── DepositMethodSelector.tsx (~60 lines)  — payment method tabs (Card, Crypto, Skins, SBP)
├── DepositCardForm.tsx       (~80 lines)  — card + SBP payment form
├── DepositCryptoForm.tsx     (~80 lines)  — crypto network selector + address display
├── DepositSkinsForm.tsx      (~60 lines)  — skins trade form
├── useDeposit.ts             (~80 lines)  — all state: method, amount, currency, processing
└── depositConstants.ts       (~40 lines)  — CURRENCIES, CRYPTO_NETWORKS, SBP_SYSTEMS, MIN_AMOUNTS
```

`useDeposit.ts` owns all state previously scattered across the monolith. Components receive only what they need via props.

### UpgradeBlock.tsx (602 lines) + UpgradeVideo.tsx (400 lines) → 8 files

```
resources/js/Components/Upgrade/
├── UpgradeBlock.tsx           (~80 lines)  — orchestrator
├── UpgradeInventoryPanel.tsx  (~80 lines)  — player's skin selection (left panel)
├── UpgradeTargetPanel.tsx     (~80 lines)  — target skin selection (right panel)
├── UpgradeMultiplierBar.tsx   (~50 lines)  — quick multiplier buttons (×2, ×3, etc.)
├── UpgradeVideo.tsx           (~80 lines)  — video orchestrator
├── VideoOverlays.tsx          (~80 lines)  — CrateSkinOverlay + DefuseOverlay
├── useUpgrade.ts              (~120 lines) — all upgrade state: selectedSkin, targetSkin, chance, phase
└── upgradeCalculations.ts     (~60 lines)  — pure functions: calculateChance(), deriveMultiplier(), canBeTarget()
```

`upgradeCalculations.ts` exports pure functions with no React — independently testable.

### Market/Index.tsx (312 lines → 4 files)

```
resources/js/Pages/Market/Index.tsx         (~60 lines)  — page, receives Inertia props
resources/js/Components/Market/
├── SkinGrid.tsx       (~80 lines)  — virtualized skin grid
├── MarketToolbar.tsx  (~80 lines)  — search, filters, sort, price range
└── useMarket.ts       (~80 lines)  — filter state, selection state, buy handler
```

---

## Phase 3 — Inertia Best Practices

### Deferred props (requires backend changes)

**DepositModal config:**
- Remove: `axios.get('/deposit/config')` in `DepositModal`
- Add: `DepositController::create()` passes config as `Inertia::defer(fn)` prop
- Frontend: `Deferred` component wraps the modal form, shows `SkeletonDepositForm` until ready

**LiveFeed initial data:**
- Remove: `axios.get('/api/live-feed')` initial fetch
- Add: `UpgradeController` passes last 20 feed items as deferred prop
- Frontend: initial data from prop, subsequent updates via Echo WebSocket (already working)

**Market filtering:**
- Remove: axios calls on filter change
- Replace: `router.reload({ only: ['skins'] })` — partial reload of only skins data
- Backend: `MarketController::index()` already returns skins, just needs `only` support

### useForm() adoption

Replace `router.post()` calls with `useForm()` in:
- `Components/Profile/ProfileSidebar.tsx` — trade URL form (partially done, complete it)
- `Components/Profile/SellModal.tsx` — sell form
- All forms inside `DepositCardForm`, `DepositCryptoForm`

Benefits: automatic `processing`, `errors`, `reset()`, `clearErrors()` without manual state.

### Link prefetching

Add `prefetch="hover"` to navigation links in `Components/Layout/Header/index.tsx`:
- Market → `/market`
- Upgrade → `/` (home)
- Profile → `/profile/{id}`

Pages begin loading on hover, transitions feel instant.

---

## Phase 4 — UX & Animations

### New package: framer-motion

Install: `npm install framer-motion`

### Page transitions

Replace `Components/UI/PageTransition.tsx`:
- `AnimatePresence` wraps page content in `app.tsx`
- Each page fades in + slight Y translate (0 → 8px → 0) on mount
- Inertia progress bar: `router.on('progress')` drives a smooth bar at top with easing
- Progress bar color: `--color-brand` (#4E89FF)

### Skeleton loaders

Displayed while deferred props are loading:

| Location | Skeleton |
|---|---|
| Market skin grid | `SkeletonSkinCard` — 12 placeholder cards matching SkinCard dimensions |
| Live Feed | `SkeletonFeedItem` — avatar circle + two lines |
| Profile inventory | `SkeletonSkinCard` same component |
| Deposit form | `SkeletonDepositForm` — method tabs + input placeholders |

All use `animate-pulse` from Tailwind, dark surface color matching site theme.

### Component animations (framer-motion)

**Modals** — `Modal.tsx`:
- Backdrop: fade in (opacity 0→1)
- Panel: scale 0.95→1 + fade, spring easing

**Toast** — `Toast.tsx`:
- Slide in from bottom-right
- `AnimatePresence` handles exit animation (slide out + fade)

**Live Feed** — `LiveFeed.tsx`:
- New item: `AnimatePresence` + slide down from top
- Stagger when initial list renders

**SkinCard** — `SkinCard.tsx`:
- Hover: scale 1→1.02 + subtle glow (box-shadow via motion)
- Replace CSS hover with Framer Motion `whileHover`

**Upgrade GO button**:
- While waiting for result: pulse animation
- On win: brief scale-up celebration
- On loss: shake animation

### Loading states on buttons

All buttons with `loading` prop show inline spinner (`animate-spin` circle, 16px) replacing button text. Applied to:
- Buy button in Market
- GO button in Upgrade (already has state, needs spinner)
- Deposit submit button
- Sell button in Profile
- Promo code apply button

---

## Phase 5 — Performance

### React.memo

Wrap with `React.memo`:
- `Components/Upgrade/SkinCard.tsx` — rendered in grids, no reason to re-render on parent state change
- `Components/Upgrade/LiveFeedItem.tsx` — list item
- `Components/Market/MarketToolbar.tsx` — doesn't depend on skin selection
- `Components/Market/SkinGrid.tsx` — only re-renders when skins array changes

### useMemo / useCallback gaps

- `ProfileStatCards.tsx` — `splitPriceKopecks()` wrapped in `useMemo`
- `useMarket.ts` — `onBuy`, `onSelect`, `onFilter` wrapped in `useCallback` to not break `React.memo` on children
- `mapRarityColor()` calls in render — result cached via `useMemo` in `SkinCard`

### Market virtualization

Add: `npm install @tanstack/react-virtual`

`SkinGrid.tsx` uses `useVirtualizer` for the skin grid. Only visible cards are rendered in DOM. Critical for smooth scroll when market has 100+ skins.

### useTargetSkins AbortController

In `hooks/useTargetSkins.ts`:
- Add `AbortController` per request
- Cancel previous request when new search term fires
- Complements existing 400ms debounce

---

## Phase 6 — Responsive Design & Bug Fixes

### Bug fixes

| Bug | File | Fix |
|---|---|---|
| Hover shadow broken | `GradientButton.tsx` | Move dynamic shadow to `style` prop |
| Skin image overflows card | `SkinCard.tsx` | Add `overflow-hidden` to card container |
| Absolute without relative parent | `UpgradeResult.tsx:66` | Add `relative` to parent div |
| `feedCounter` at module level | `LiveFeed.tsx` | Move into `useRef` inside component |
| Silent error swallow | `LiveFeed.tsx` | `.catch(() => {})` → Toast notification |
| Silent error swallow | `DepositModal` | `console.error` → Toast + retry button |
| Silent error swallow | `useTargetSkins.ts` | `console.error` → Toast |

### Responsive fixes

**Market (`Market/Index.tsx` + `SkinGrid.tsx`):**
- Mobile (< xs/550px): 2-column grid, toolbar collapses vertically
- Tablet (xs–wide): 3-column grid
- Desktop (wide+): 4–5 column grid

**Upgrade (`UpgradeBlock.tsx`):**
- Mobile: inventory panel and target panel stack vertically (column), video between them
- Desktop: existing side-by-side layout

**Header:**
- Audit all mobile states: balance chip, profile chip, nav links
- Ensure no horizontal overflow on small screens

**Profile:**
- `ProfileSidebar` + `ProfileTabs`: verify scroll behaviour and no overflow on mobile

---

## Files Changed Summary

### New files
- `resources/js/Components/UI/Button.tsx`
- `resources/js/Components/UI/Input.tsx`
- `resources/js/Components/UI/NumberInput.tsx`
- `resources/js/Components/UI/Skeleton.tsx`
- `resources/js/Components/UI/Badge.tsx`
- `resources/js/Components/UI/Icons.tsx`
- `resources/js/Components/Deposit/DepositMethodSelector.tsx`
- `resources/js/Components/Deposit/DepositCardForm.tsx`
- `resources/js/Components/Deposit/DepositCryptoForm.tsx`
- `resources/js/Components/Deposit/DepositSkinsForm.tsx`
- `resources/js/Components/Deposit/useDeposit.ts`
- `resources/js/Components/Deposit/depositConstants.ts`
- `resources/js/Components/Upgrade/UpgradeInventoryPanel.tsx`
- `resources/js/Components/Upgrade/UpgradeTargetPanel.tsx`
- `resources/js/Components/Upgrade/UpgradeMultiplierBar.tsx`
- `resources/js/Components/Upgrade/VideoOverlays.tsx`
- `resources/js/Components/Upgrade/useUpgrade.ts`
- `resources/js/Components/Upgrade/upgradeCalculations.ts`
- `resources/js/Components/Market/SkinGrid.tsx`
- `resources/js/Components/Market/MarketToolbar.tsx`
- `resources/js/Components/Market/useMarket.ts`
- `docs/superpowers/specs/2026-04-23-frontend-refactoring-design.md`

### Modified files
- `resources/css/app.css` — `@theme` block with colors + breakpoints
- `resources/js/types/index.d.ts` — extended `PageProps`
- `resources/js/app.tsx` — `AnimatePresence` wrapper, progress bar
- `resources/js/Components/UI/PageTransition.tsx` — replaced with Framer Motion
- `resources/js/Components/UI/Toast.tsx` — Framer Motion animations
- `resources/js/Components/UI/Modal.tsx` — Framer Motion animations
- `resources/js/Components/UI/GradientButton.tsx` — hover shadow bug fix (then deprecated in favour of Button.tsx)
- `resources/js/Components/Upgrade/UpgradeBlock.tsx` — decomposed
- `resources/js/Components/Upgrade/UpgradeVideo.tsx` — decomposed
- `resources/js/Components/Upgrade/SkinCard.tsx` — React.memo + Framer Motion hover
- `resources/js/Components/Upgrade/LiveFeed.tsx` — deferred prop, AnimatePresence, error handling
- `resources/js/Components/Upgrade/LiveFeedItem.tsx` — React.memo
- `resources/js/Components/Deposit/DepositModal.tsx` — decomposed
- `resources/js/Components/Profile/ProfileSidebar.tsx` — useForm() adoption
- `resources/js/Components/Profile/SellModal.tsx` — useForm() adoption
- `resources/js/Components/Profile/ProfileStatCards.tsx` — useMemo fix
- `resources/js/Components/Layout/Header/index.tsx` — Link prefetch, TypeScript fix
- `resources/js/Components/Layout/Header/sections/SocialLinks.tsx` — TypeScript fix
- `resources/js/hooks/useTargetSkins.ts` — AbortController, error handling
- `resources/js/Pages/Market/Index.tsx` — decomposed
- `app/Http/Controllers/DepositController.php` — deferred prop for config
- `app/Http/Controllers/UpgradeController.php` — deferred prop for live feed initial data

### Deleted files
- `resources/js/Pages/Welcome.tsx`
- `resources/js/Pages/Auth/Login.tsx`
- `resources/js/Pages/Deposit/Create.tsx`
- `resources/js/Pages/Profile/History.tsx`
- `resources/js/Pages/ProvablyFair/Verify.tsx`
- `resources/js/Components/Layout/Header/icons.tsx` — merged into Icons.tsx
- `resources/js/Components/Upgrade/icons.tsx` — merged into Icons.tsx

## New npm packages

```bash
npm install framer-motion @tanstack/react-virtual
```
