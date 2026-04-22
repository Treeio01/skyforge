# Frontend Refactoring Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Full frontend refactoring of Skyforge — component decomposition, Inertia best practices, animations, skeleton loaders, performance optimizations, responsive fixes, and bug fixes.

**Architecture:** Layer-by-layer approach: Foundation (shared UI library + theme) → Decomposition (monolith splitting) → Inertia patterns → UX/Animations → Performance → Responsive/Bugs. Each phase builds on the previous. Backend changes are minimal and scoped to adding deferred props.

**Tech Stack:** Laravel 13, Inertia.js v2, React 18, TypeScript, Tailwind CSS v4, Framer Motion, @tanstack/react-virtual

---

## Phase 1 — Foundation

### Task 1: Install new packages

**Files:**
- Modify: `package.json`

- [ ] **Step 1: Install framer-motion and react-virtual**

```bash
cd /Users/danil/Desktop/projects/skyforge
npm install framer-motion @tanstack/react-virtual
```

Expected output: packages added to `node_modules`, `package.json` updated with `framer-motion` and `@tanstack/react-virtual`.

- [ ] **Step 2: Verify TypeScript is happy**

```bash
npx tsc --noEmit 2>&1 | head -30
```

Expected: same errors as before (no new type errors from packages).

- [ ] **Step 3: Commit**

```bash
git add package.json package-lock.json
git commit -m "chore: add framer-motion and react-virtual"
```

---

### Task 2: Tailwind v4 @theme — breakpoints and colors

**Files:**
- Modify: `resources/css/app.css`

- [ ] **Step 1: Add @theme block after the `@import "tailwindcss";` line**

Open `resources/css/app.css`. After line 1 (`@import "tailwindcss";`), insert:

```css
@theme {
  /* Custom breakpoints — xs=550px, wide=1155px preserved as named tokens */
  --breakpoint-xs: 550px;
  --breakpoint-wide: 1155px;

  /* Brand */
  --color-brand: #4E89FF;
  --color-brand-hover: #3a6fd4;

  /* Surfaces */
  --color-surface: #070A10;
  --color-surface-2: #0A0E17;
  --color-surface-3: #111827;
  --color-surface-card: rgba(11, 15, 24, 0.8);

  /* Text */
  --color-text-muted: #BED4FF;
  --color-text-dim: #6B7FA3;

  /* Borders */
  --color-border: rgba(255, 255, 255, 0.08);
  --color-border-active: rgba(255, 255, 255, 0.21);

  /* States */
  --color-success: #22c55e;
  --color-danger: #ef4444;
  --color-warning: #f59e0b;
}
```

- [ ] **Step 2: Verify build passes**

```bash
npm run build 2>&1 | tail -20
```

Expected: build succeeds, no CSS errors.

- [ ] **Step 3: Commit**

```bash
git add resources/css/app.css
git commit -m "feat: tailwind v4 @theme — custom breakpoints and color tokens"
```

---

### Task 3: Delete empty page files and debug code

**Files:**
- Delete: `resources/js/Pages/Welcome.tsx`
- Delete: `resources/js/Pages/Auth/Login.tsx`
- Delete: `resources/js/Pages/Deposit/Create.tsx`
- Delete: `resources/js/Pages/Profile/History.tsx`
- Delete: `resources/js/Pages/ProvablyFair/Verify.tsx`

- [ ] **Step 1: Delete empty pages**

```bash
rm resources/js/Pages/Welcome.tsx
rm resources/js/Pages/Auth/Login.tsx
rm resources/js/Pages/Deposit/Create.tsx
rm resources/js/Pages/Profile/History.tsx
rm resources/js/Pages/ProvablyFair/Verify.tsx
```

- [ ] **Step 2: Remove DEBUG_SHOW from UpgradeBlock**

In `resources/js/Components/Upgrade/UpgradeBlock.tsx`, find and remove the line:
```tsx
const DEBUG_SHOW = false;
```
Also remove any JSX that uses `DEBUG_SHOW` as a condition.

- [ ] **Step 3: Remove console.log from DepositModal**

In `resources/js/Components/Deposit/DepositModal.tsx`, remove all `console.log(...)` calls. Keep `console.error` for now — those will be replaced with Toast in Phase 6.

- [ ] **Step 4: Verify TypeScript**

```bash
npx tsc --noEmit 2>&1 | grep -v "node_modules"
```

Expected: no new errors from deletions.

- [ ] **Step 5: Commit**

```bash
git add -A
git commit -m "chore: delete empty page files, remove debug code"
```

---

### Task 4: Fix PageProps TypeScript — remove all `as any`

**Files:**
- Modify: `resources/js/types/index.d.ts`
- Modify: `resources/js/Components/Layout/Header/index.tsx`
- Modify: `resources/js/Components/Layout/Header/sections/SocialLinks.tsx`
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx`

- [ ] **Step 1: Extend PageProps in `resources/js/types/index.d.ts`**

Replace the current `PageProps` type at the bottom of the file:

```ts
export interface SiteStats {
    online: number;
    total_upgrades: number;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: { user: User | null };
    flash: Flash;
    stats?: SiteStats;
    socials?: Record<string, string>;
};
```

- [ ] **Step 2: Fix Header/index.tsx — remove `as any`**

In `resources/js/Components/Layout/Header/index.tsx`, replace:

```tsx
const stats = (usePage<PageProps>().props as any).stats as { online: number; total_upgrades: number } | undefined;
```

With:

```tsx
const { stats } = usePage<PageProps>().props;
```

- [ ] **Step 3: Fix SocialLinks.tsx — remove `as any`**

Open `resources/js/Components/Layout/Header/sections/SocialLinks.tsx`. Replace:

```tsx
(usePage().props as any).socials as Record<string, string>
```

With:

```tsx
usePage<PageProps>().props.socials
```

Add the import at the top if missing:

```tsx
import { usePage } from '@inertiajs/react';
import type { PageProps } from '@/types';
```

- [ ] **Step 4: Fix UpgradeBlock.tsx flash access — remove `as any`**

In `resources/js/Components/Upgrade/UpgradeBlock.tsx`, find any occurrence of `(page.props as any).flash` and replace with:

```tsx
(page.props as PageProps).flash
```

Add import if missing: `import type { PageProps } from '@/types';`

- [ ] **Step 5: Verify no more `as any` in key files**

```bash
grep -r "as any" resources/js/ --include="*.tsx" --include="*.ts"
```

Expected: no results (or only in files not yet touched in this plan).

- [ ] **Step 6: Verify TypeScript**

```bash
npx tsc --noEmit 2>&1 | grep -v "node_modules"
```

Expected: no new errors.

- [ ] **Step 7: Commit**

```bash
git add resources/js/types/index.d.ts \
        resources/js/Components/Layout/Header/index.tsx \
        resources/js/Components/Layout/Header/sections/SocialLinks.tsx \
        resources/js/Components/Upgrade/UpgradeBlock.tsx
git commit -m "fix: remove all as-any casts, extend PageProps with stats and socials"
```

---

### Task 5: Create unified Icon library

**Files:**
- Create: `resources/js/Components/UI/Icons.tsx`
- Modify: `resources/js/Components/Layout/Header/index.tsx` (update import)
- Delete later: `resources/js/Components/Layout/Header/icons.tsx` and `resources/js/Components/Upgrade/icons.tsx` (in Task 6 after migration)

- [ ] **Step 1: Read the two existing icon files**

```bash
cat resources/js/Components/Layout/Header/icons.tsx
cat resources/js/Components/Upgrade/icons.tsx
```

- [ ] **Step 2: Create `resources/js/Components/UI/Icons.tsx`**

Merge ALL exports from both files into one. Also add the Steam SVG from Header's login button. Structure:

```tsx
export function SteamIcon({ className }: { className?: string }) {
    return (
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" className={className}>
            <path d="M6.6809 5.49624L5.18115 7.6735C4.82761 7.65744 4.4717 7.75746 4.17626 7.95162L0.883014 6.59677C0.883014 6.59677 0.80681 7.84964 1.12438 8.78335L3.45253 9.7434C3.56943 10.2655 3.92779 10.7234 4.45652 10.9437C5.32154 11.3049 6.31896 10.8932 6.6788 10.0283C6.77247 9.80224 6.81613 9.56516 6.80979 9.32856L9.00831 7.79705C10.2925 7.79705 11.3362 6.75081 11.3362 5.46596C11.3362 4.18104 10.2925 3.13574 9.00831 3.13574C7.76796 3.13574 6.61138 4.21802 6.6809 5.49624ZM6.32053 9.87798C6.04202 10.5462 5.27364 10.8632 4.60571 10.5851C4.29758 10.4568 4.06495 10.2218 3.93074 9.94159L4.68857 10.2555C5.18115 10.4605 5.74627 10.2271 5.95102 9.73496C6.15642 9.24233 5.92341 8.67664 5.43109 8.47159L4.64771 8.14718C4.94997 8.0326 5.29363 8.0284 5.61471 8.16193C5.93837 8.2965 6.18954 8.54994 6.32263 8.87378C6.45576 9.19766 6.45524 9.55514 6.32053 9.87798ZM9.00831 7.01896C8.15344 7.01896 7.45742 6.32233 7.45742 5.46596C7.45742 4.6103 8.15344 3.91349 9.00831 3.91349C9.86376 3.91349 10.5597 4.6103 10.5597 5.46596C10.5597 6.32233 9.86376 7.01896 9.00831 7.01896ZM7.84618 5.4636C7.84618 4.81943 8.36807 4.29692 9.01094 4.29692C9.65437 4.29692 10.1762 4.81943 10.1762 5.4636C10.1762 6.10786 9.65437 6.62989 9.01094 6.62989C8.36807 6.62989 7.84618 6.10786 7.84618 5.4636Z" fill="white" />
        </svg>
    );
}

// Re-export everything from both existing icon files verbatim — read each file and copy all named exports here.
// Then add any inline SVGs from DepositModal.tsx (CardIcon, CryptoIcon, SkinsIcon, SBPIcon).
```

Read `Header/icons.tsx` and `Upgrade/icons.tsx`, copy all named exports into `Icons.tsx`.

- [ ] **Step 3: Update Header to import from Icons.tsx**

In `resources/js/Components/Layout/Header/index.tsx`, change:

```tsx
import { BonusIcon, FaqIcon, GlobeIcon, LevelsIcon, MarketIcon } from "./icons";
```

To:

```tsx
import { BonusIcon, FaqIcon, GlobeIcon, LevelsIcon, MarketIcon, SteamIcon } from "@/Components/UI/Icons";
```

In the login button JSX, replace the inline SVG with `<SteamIcon />`.

- [ ] **Step 4: Update Upgrade components to import from Icons.tsx**

In each file under `resources/js/Components/Upgrade/` that imports from `./icons`, change to `@/Components/UI/Icons`.

- [ ] **Step 5: Verify build**

```bash
npm run build 2>&1 | tail -20
```

Expected: no errors.

- [ ] **Step 6: Commit**

```bash
git add resources/js/Components/UI/Icons.tsx resources/js/Components/Layout/ resources/js/Components/Upgrade/
git commit -m "feat: unified Icons.tsx — merge all icon sources into one file"
```

---

### Task 6: Create shared Button component

**Files:**
- Create: `resources/js/Components/UI/Button.tsx`
- Modify: `resources/js/Components/Layout/Header/index.tsx` (replace inline button)

- [ ] **Step 1: Create `resources/js/Components/UI/Button.tsx`**

```tsx
import { ReactNode } from 'react';

type ButtonVariant = 'primary' | 'ghost' | 'danger';
type ButtonSize = 'sm' | 'md' | 'lg';

interface ButtonProps {
    children: ReactNode;
    onClick?: () => void;
    disabled?: boolean;
    loading?: boolean;
    type?: 'button' | 'submit';
    className?: string;
    variant?: ButtonVariant;
    size?: ButtonSize;
}

const VARIANTS: Record<ButtonVariant, { bg: string; shadow: string; hoverShadow: string }> = {
    primary: {
        bg: 'radial-gradient(80.57% 100% at 50% 100%, #122A51 0%, #091637 100%)',
        shadow: '0 0 0 0 #0E1E39',
        hoverShadow: '0 0 20px rgba(30,60,120,0.6)',
    },
    ghost: {
        bg: 'transparent',
        shadow: 'none',
        hoverShadow: 'none',
    },
    danger: {
        bg: 'radial-gradient(80.57% 100% at 50% 100%, #511212 0%, #370909 100%)',
        shadow: '0 0 0 0 #391010',
        hoverShadow: '0 0 20px rgba(120,30,30,0.6)',
    },
};

const SIZES: Record<ButtonSize, string> = {
    sm: 'py-2 px-3 text-xs rounded-[10px]',
    md: 'py-3 px-[14px] text-sm rounded-[12px]',
    lg: 'py-4 px-5 text-base rounded-[14px]',
};

function Spinner() {
    return (
        <svg className="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
    );
}

export default function Button({
    children,
    onClick,
    disabled = false,
    loading = false,
    type = 'button',
    className = '',
    variant = 'primary',
    size = 'md',
}: ButtonProps) {
    const v = VARIANTS[variant];
    const isDisabled = disabled || loading;

    return (
        <button
            type={type}
            onClick={onClick}
            disabled={isDisabled}
            style={{
                background: v.bg,
                boxShadow: v.shadow,
            }}
            onMouseEnter={(e) => {
                if (!isDisabled && v.hoverShadow !== 'none') {
                    (e.currentTarget as HTMLButtonElement).style.boxShadow = v.hoverShadow;
                }
            }}
            onMouseLeave={(e) => {
                (e.currentTarget as HTMLButtonElement).style.boxShadow = v.shadow;
            }}
            className={`flex justify-center items-center gap-[5px] transition-all duration-200 hover:brightness-125 active:scale-[0.98] cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed ${SIZES[size]} ${className}`}
        >
            {loading ? <Spinner /> : children}
        </button>
    );
}
```

- [ ] **Step 2: Replace inline login button in Header with Button component**

In `resources/js/Components/Layout/Header/index.tsx`, replace the inline `<button>` with:

```tsx
import Button from "@/Components/UI/Button";
// ...

<Button
    variant="primary"
    onClick={() => window.dispatchEvent(new Event('show-login-modal'))}
>
    <SteamIcon />
    <span className="text-sm text-white font-sf-display font-medium leading-[120%]">Войти</span>
</Button>
```

- [ ] **Step 3: Deprecate GradientButton — add JSDoc notice**

In `resources/js/Components/UI/GradientButton.tsx`, add at the top:

```tsx
/** @deprecated Use Button from @/Components/UI/Button instead. This file will be deleted after full migration. */
```

- [ ] **Step 4: Verify build**

```bash
npm run build 2>&1 | tail -20
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/UI/Button.tsx resources/js/Components/Layout/Header/index.tsx resources/js/Components/UI/GradientButton.tsx
git commit -m "feat: shared Button component with loading state and hover fix"
```

---

### Task 7: Create shared Input and Skeleton components

**Files:**
- Create: `resources/js/Components/UI/Input.tsx`
- Create: `resources/js/Components/UI/Skeleton.tsx`
- Create: `resources/js/Components/UI/Badge.tsx`

- [ ] **Step 1: Create `resources/js/Components/UI/Input.tsx`**

```tsx
import { InputHTMLAttributes, forwardRef } from 'react';

interface InputProps extends InputHTMLAttributes<HTMLInputElement> {
    label?: string;
    error?: string;
    hint?: string;
    prefix?: string;
    suffix?: string;
}

const Input = forwardRef<HTMLInputElement, InputProps>(({
    label,
    error,
    hint,
    prefix,
    suffix,
    className = '',
    ...props
}, ref) => {
    return (
        <div className="flex flex-col gap-1.5 w-full">
            {label && (
                <label className="text-xs text-text-muted font-medium">{label}</label>
            )}
            <div className="relative flex items-center">
                {prefix && (
                    <span className="absolute left-3 text-text-dim text-sm select-none">{prefix}</span>
                )}
                <input
                    ref={ref}
                    {...props}
                    className={`w-full bg-surface-2 border rounded-[10px] px-3 py-2.5 text-sm text-white placeholder:text-text-dim outline-none transition-colors duration-200
                        focus:border-brand/50
                        ${error ? 'border-danger/60' : 'border-border'}
                        ${prefix ? 'pl-8' : ''}
                        ${suffix ? 'pr-8' : ''}
                        ${className}`}
                />
                {suffix && (
                    <span className="absolute right-3 text-text-dim text-sm select-none">{suffix}</span>
                )}
            </div>
            {error && <p className="text-xs text-danger">{error}</p>}
            {hint && !error && <p className="text-xs text-text-dim">{hint}</p>}
        </div>
    );
});

Input.displayName = 'Input';

export default Input;
```

- [ ] **Step 2: Create `resources/js/Components/UI/Skeleton.tsx`**

```tsx
interface SkeletonProps {
    className?: string;
    rounded?: string;
}

export function Skeleton({ className = '', rounded = 'rounded-[10px]' }: SkeletonProps) {
    return (
        <div className={`animate-pulse bg-surface-2 ${rounded} ${className}`} />
    );
}

export function SkeletonSkinCard() {
    return (
        <div className="flex flex-col gap-2 p-3 bg-surface-2 rounded-[16px] border border-border">
            <Skeleton className="w-full aspect-square" rounded="rounded-[12px]" />
            <Skeleton className="h-3 w-3/4" />
            <Skeleton className="h-3 w-1/2" />
            <Skeleton className="h-8 w-full mt-1" />
        </div>
    );
}

export function SkeletonFeedItem() {
    return (
        <div className="flex items-center gap-3 p-2">
            <Skeleton className="w-8 h-8 shrink-0" rounded="rounded-full" />
            <div className="flex flex-col gap-1.5 flex-1">
                <Skeleton className="h-3 w-1/2" />
                <Skeleton className="h-3 w-1/3" />
            </div>
            <Skeleton className="w-10 h-10 shrink-0" rounded="rounded-[8px]" />
        </div>
    );
}

export function SkeletonDepositForm() {
    return (
        <div className="flex flex-col gap-4">
            <div className="flex gap-2">
                {[1, 2, 3, 4].map((i) => (
                    <Skeleton key={i} className="h-10 flex-1" rounded="rounded-[10px]" />
                ))}
            </div>
            <Skeleton className="h-12 w-full" />
            <Skeleton className="h-10 w-full" />
            <Skeleton className="h-12 w-full" />
        </div>
    );
}
```

- [ ] **Step 3: Create `resources/js/Components/UI/Badge.tsx`**

```tsx
interface BadgeProps {
    rarity: string | null;
    className?: string;
    children?: React.ReactNode;
}

const RARITY_LABELS: Record<string, string> = {
    '#b0c3d9': 'Consumer',
    '#5e98d9': 'Industrial',
    '#4b69ff': 'Mil-Spec',
    '#8847ff': 'Restricted',
    '#d32ce6': 'Classified',
    '#eb4b4b': 'Covert',
    '#e4ae39': 'Contraband',
};

export default function Badge({ rarity, className = '', children }: BadgeProps) {
    const color = rarity ?? '#b0c3d9';
    const label = children ?? RARITY_LABELS[color.toLowerCase()] ?? 'Unknown';

    return (
        <span
            className={`inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium ${className}`}
            style={{ backgroundColor: `${color}22`, color, border: `1px solid ${color}44` }}
        >
            {label}
        </span>
    );
}
```

- [ ] **Step 4: Verify TypeScript**

```bash
npx tsc --noEmit 2>&1 | grep -v "node_modules"
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/UI/Input.tsx resources/js/Components/UI/Skeleton.tsx resources/js/Components/UI/Badge.tsx
git commit -m "feat: shared Input, Skeleton, and Badge components"
```

---

## Phase 2 — Component Decomposition

### Task 8: Extract upgrade calculation utilities

**Files:**
- Create: `resources/js/Components/Upgrade/upgradeCalculations.ts`

- [ ] **Step 1: Read UpgradeBlock to find pure functions**

```bash
grep -n "function calculate\|function canBe\|function derive\|const calculate\|const canBe\|const derive" resources/js/Components/Upgrade/UpgradeBlock.tsx
```

- [ ] **Step 2: Create `resources/js/Components/Upgrade/upgradeCalculations.ts`**

Extract ALL pure calculation functions from `UpgradeBlock.tsx` (calculateChance, canBeTarget, deriveMultiplier, and any other pure math functions) into this file. The functions must be identical to the originals — just moved.

```ts
import type { Skin, UserSkin } from '@/types';

// Minimum multiplier allowed for upgrade
export const MIN_MULTIPLIER = 1.05;
export const MAX_MULTIPLIER = 100;

/**
 * Calculate upgrade win chance as a percentage (0-90).
 * Read the exact implementation from UpgradeBlock.tsx and copy here verbatim.
 */
export function calculateChance(betAmount: number, targetPrice: number): number {
    // COPY THE EXACT IMPLEMENTATION FROM UpgradeBlock.tsx
    throw new Error('Replace with actual implementation from UpgradeBlock.tsx');
}

/**
 * Check if a skin can be selected as upgrade target given current bet.
 * Read the exact implementation from UpgradeBlock.tsx and copy here verbatim.
 */
export function canBeTarget(skin: Skin, betAmount: number): boolean {
    // COPY THE EXACT IMPLEMENTATION FROM UpgradeBlock.tsx
    throw new Error('Replace with actual implementation from UpgradeBlock.tsx');
}

/**
 * Derive multiplier from bet amount and target price.
 * Read the exact implementation from UpgradeBlock.tsx and copy here verbatim.
 */
export function deriveMultiplier(betAmount: number, targetPrice: number): number {
    // COPY THE EXACT IMPLEMENTATION FROM UpgradeBlock.tsx
    throw new Error('Replace with actual implementation from UpgradeBlock.tsx');
}
```

> **Important:** Before writing this file, open `resources/js/Components/Upgrade/UpgradeBlock.tsx` and copy the EXACT body of each calculation function. Do not rewrite them. The `throw new Error` lines above are placeholders only — replace with actual code.

- [ ] **Step 3: Import calculations in UpgradeBlock**

In `resources/js/Components/Upgrade/UpgradeBlock.tsx`, remove the local definitions and add:

```tsx
import { calculateChance, canBeTarget, deriveMultiplier, MIN_MULTIPLIER, MAX_MULTIPLIER } from './upgradeCalculations';
```

- [ ] **Step 4: Verify no runtime errors**

```bash
npm run build 2>&1 | tail -10
```

Expected: clean build.

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/Upgrade/upgradeCalculations.ts resources/js/Components/Upgrade/UpgradeBlock.tsx
git commit -m "refactor: extract upgrade pure calculations into upgradeCalculations.ts"
```

---

### Task 9: Create useUpgrade hook

**Files:**
- Create: `resources/js/Components/Upgrade/useUpgrade.ts`
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx`

- [ ] **Step 1: Read current UpgradeBlock state**

```bash
grep -n "useState\|useCallback\|useRef\|useEffect" resources/js/Components/Upgrade/UpgradeBlock.tsx | head -40
```

- [ ] **Step 2: Create `resources/js/Components/Upgrade/useUpgrade.ts`**

Move ALL useState, useCallback, useRef, useEffect logic from UpgradeBlock into this hook. The hook returns everything UpgradeBlock needs to render.

```ts
import { useState, useCallback, useRef, useEffect } from 'react';
import type { UserSkin, Skin } from '@/types';
import { calculateChance, canBeTarget, deriveMultiplier, MIN_MULTIPLIER } from './upgradeCalculations';

export type UpgradePhase = 'idle' | 'spinning' | 'result';

export interface UseUpgradeReturn {
    // Selection state
    selectedInventorySkin: UserSkin | null;
    selectedTargetSkin: Skin | null;
    setSelectedInventorySkin: (skin: UserSkin | null) => void;
    setSelectedTargetSkin: (skin: Skin | null) => void;

    // Game state
    phase: UpgradePhase;
    chance: number;
    multiplier: number;
    lastResult: 'win' | 'lose' | null;
    wonSkin: Skin | null;

    // Actions
    handleMultiplierChange: (value: number) => void;
    handleUpgrade: () => Promise<void>;
    resetAfterResult: () => void;

    // UI flags
    isProcessing: boolean;
    showLoginModal: boolean;
    setShowLoginModal: (v: boolean) => void;
}

export function useUpgrade(userBalance: number, isAuthenticated: boolean): UseUpgradeReturn {
    // Read UpgradeBlock.tsx and copy ALL state declarations and handlers here verbatim.
    // Return them as a single object matching UseUpgradeReturn.
    // Do not change the logic — only move it.
    throw new Error('Implement: copy all state and handlers from UpgradeBlock.tsx');
}
```

> **Important:** Open `resources/js/Components/Upgrade/UpgradeBlock.tsx`, copy all `useState`, `useCallback`, `useRef`, `useEffect` declarations and their bodies into `useUpgrade.ts`. Do not change any logic.

- [ ] **Step 3: Slim down UpgradeBlock to use the hook**

`UpgradeBlock.tsx` should become a thin orchestrator:

```tsx
import { useUpgrade } from './useUpgrade';
// ... other imports

export default function UpgradeBlock({ inventory, userSkin, auth }: UpgradeBlockProps) {
    const upgrade = useUpgrade(auth.user?.balance ?? 0, !!auth.user);

    return (
        <div className="...">
            {/* Render panels using upgrade.* */}
        </div>
    );
}
```

- [ ] **Step 4: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/Upgrade/useUpgrade.ts resources/js/Components/Upgrade/UpgradeBlock.tsx
git commit -m "refactor: extract upgrade state into useUpgrade hook"
```

---

### Task 10: Decompose UpgradeBlock into panels

**Files:**
- Create: `resources/js/Components/Upgrade/UpgradeInventoryPanel.tsx`
- Create: `resources/js/Components/Upgrade/UpgradeTargetPanel.tsx`
- Create: `resources/js/Components/Upgrade/UpgradeMultiplierBar.tsx`
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx`

- [ ] **Step 1: Create `resources/js/Components/Upgrade/UpgradeInventoryPanel.tsx`**

Extract the left panel (player's skin selection) from UpgradeBlock. Read UpgradeBlock and find the inventory panel JSX:

```tsx
import React from 'react';
import type { UserSkin } from '@/types';
import SkinCard from './SkinCard';

interface UpgradeInventoryPanelProps {
    inventory: UserSkin[];
    selected: UserSkin | null;
    onSelect: (skin: UserSkin | null) => void;
}

export default function UpgradeInventoryPanel({ inventory, selected, onSelect }: UpgradeInventoryPanelProps) {
    // Copy the inventory panel JSX from UpgradeBlock.tsx here verbatim.
    // It's the section that shows the player's skins and allows selection.
    return <div>{/* paste JSX from UpgradeBlock */}</div>;
}
```

> **Important:** Open UpgradeBlock.tsx, find the inventory panel section (left side — shows player's owned skins), and move that JSX here. The component receives `inventory`, `selected`, `onSelect` as props.

- [ ] **Step 2: Create `resources/js/Components/Upgrade/UpgradeTargetPanel.tsx`**

```tsx
import React from 'react';
import type { Skin } from '@/types';
import SkinCard from './SkinCard';
import { SkeletonSkinCard } from '@/Components/UI/Skeleton';

interface UpgradeTargetPanelProps {
    targetSkin: Skin | null;
    onOpenSearch: () => void;
    chance: number;
}

export default function UpgradeTargetPanel({ targetSkin, onOpenSearch, chance }: UpgradeTargetPanelProps) {
    // Copy the target panel JSX from UpgradeBlock.tsx here.
    // It's the section showing the target skin and chance display.
    return <div>{/* paste JSX from UpgradeBlock */}</div>;
}
```

- [ ] **Step 3: Create `resources/js/Components/Upgrade/UpgradeMultiplierBar.tsx`**

```tsx
interface UpgradeMultiplierBarProps {
    multiplier: number;
    onChange: (value: number) => void;
    disabled?: boolean;
}

const QUICK_MULTIPLIERS = [2, 3, 5, 10];

export default function UpgradeMultiplierBar({ multiplier, onChange, disabled }: UpgradeMultiplierBarProps) {
    // Copy the multiplier buttons / slider section from UpgradeBlock.tsx here.
    return <div>{/* paste JSX from UpgradeBlock */}</div>;
}
```

- [ ] **Step 4: Update UpgradeBlock to use the new panels**

UpgradeBlock.tsx should now just compose the panels:

```tsx
import UpgradeInventoryPanel from './UpgradeInventoryPanel';
import UpgradeTargetPanel from './UpgradeTargetPanel';
import UpgradeMultiplierBar from './UpgradeMultiplierBar';
import UpgradeVideo from './UpgradeVideo';
import { useUpgrade } from './useUpgrade';

export default function UpgradeBlock({ inventory, auth }) {
    const upgrade = useUpgrade(auth.user?.balance ?? 0, !!auth.user);

    return (
        <div className="flex gap-4 ...">
            <UpgradeInventoryPanel
                inventory={inventory}
                selected={upgrade.selectedInventorySkin}
                onSelect={upgrade.setSelectedInventorySkin}
            />
            <div className="flex flex-col items-center gap-4">
                <UpgradeVideo {...upgrade} />
                <UpgradeMultiplierBar
                    multiplier={upgrade.multiplier}
                    onChange={upgrade.handleMultiplierChange}
                    disabled={upgrade.isProcessing}
                />
            </div>
            <UpgradeTargetPanel
                targetSkin={upgrade.selectedTargetSkin}
                onOpenSearch={() => {/* open search */}}
                chance={upgrade.chance}
            />
        </div>
    );
}
```

- [ ] **Step 5: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 6: Commit**

```bash
git add resources/js/Components/Upgrade/
git commit -m "refactor: decompose UpgradeBlock into InventoryPanel, TargetPanel, MultiplierBar"
```

---

### Task 11: Decompose UpgradeVideo into VideoOverlays

**Files:**
- Create: `resources/js/Components/Upgrade/VideoOverlays.tsx`
- Modify: `resources/js/Components/Upgrade/UpgradeVideo.tsx`

- [ ] **Step 1: Read current UpgradeVideo.tsx**

```bash
wc -l resources/js/Components/Upgrade/UpgradeVideo.tsx
grep -n "CrateSkin\|Defuse\|overlay\|Overlay" resources/js/Components/Upgrade/UpgradeVideo.tsx | head -20
```

- [ ] **Step 2: Create `resources/js/Components/Upgrade/VideoOverlays.tsx`**

Extract `CrateSkinOverlay` and `DefuseOverlay` (or equivalent overlay components) from `UpgradeVideo.tsx`:

```tsx
import type { Skin } from '@/types';

interface CrateSkinOverlayProps {
    skin: Skin | null;
    visible: boolean;
}

export function CrateSkinOverlay({ skin, visible }: CrateSkinOverlayProps) {
    // Copy CrateSkinOverlay JSX from UpgradeVideo.tsx verbatim
    return <div>{/* JSX from UpgradeVideo.tsx */}</div>;
}

interface DefuseOverlayProps {
    visible: boolean;
    onComplete?: () => void;
}

export function DefuseOverlay({ visible, onComplete }: DefuseOverlayProps) {
    // Copy DefuseOverlay JSX from UpgradeVideo.tsx verbatim
    return <div>{/* JSX from UpgradeVideo.tsx */}</div>;
}
```

- [ ] **Step 3: Update UpgradeVideo to import from VideoOverlays**

In `UpgradeVideo.tsx`, remove the overlay component definitions and add:

```tsx
import { CrateSkinOverlay, DefuseOverlay } from './VideoOverlays';
```

- [ ] **Step 4: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/Upgrade/VideoOverlays.tsx resources/js/Components/Upgrade/UpgradeVideo.tsx
git commit -m "refactor: extract VideoOverlays from UpgradeVideo"
```

---

### Task 12: Extract deposit constants and useDeposit hook

**Files:**
- Create: `resources/js/Components/Deposit/depositConstants.ts`
- Create: `resources/js/Components/Deposit/useDeposit.ts`
- Modify: `resources/js/Components/Deposit/DepositModal.tsx`

- [ ] **Step 1: Read DepositModal constants**

```bash
grep -n "const FALLBACK\|const CURRENCIES\|const CRYPTO\|const SBP\|const MIN" resources/js/Components/Deposit/DepositModal.tsx
```

- [ ] **Step 2: Create `resources/js/Components/Deposit/depositConstants.ts`**

Read `DepositModal.tsx` and move ALL top-level constants (FALLBACK_RATES, CURRENCIES, CRYPTO_NETWORKS, SBP_SYSTEMS, MIN_AMOUNTS) here:

```ts
// Copy all constant declarations from DepositModal.tsx verbatim.
// These are the objects defined outside the component function.

export const FALLBACK_RATES = { /* ... copy from DepositModal */ };
export const CURRENCIES = { /* ... copy from DepositModal */ };
export const CRYPTO_NETWORKS = [ /* ... copy from DepositModal */ ];
export const SBP_SYSTEMS = [ /* ... copy from DepositModal */ ];
export const MIN_AMOUNTS: Record<string, number> = { /* ... copy from DepositModal */ };

export type DepositMethod = 'card' | 'crypto' | 'skins' | 'sbp';
```

> **Important:** Open DepositModal.tsx and copy the actual values of all these constants. Do not invent values.

- [ ] **Step 3: Create `resources/js/Components/Deposit/useDeposit.ts`**

```ts
import { useState, useEffect, useCallback } from 'react';
import axios from 'axios';
import type { DepositMethod } from './depositConstants';
import { FALLBACK_RATES } from './depositConstants';

interface DepositConfig {
    rates: Record<string, number>;
    // add other config fields from the /deposit/config response
}

export interface UseDepositReturn {
    method: DepositMethod;
    setMethod: (m: DepositMethod) => void;
    amount: string;
    setAmount: (v: string) => void;
    currency: string;
    setCurrency: (v: string) => void;
    cryptoNetwork: string;
    setCryptoNetwork: (v: string) => void;
    sbpSystem: string;
    setSbpSystem: (v: string) => void;
    rates: Record<string, number>;
    processing: boolean;
    setProcessing: (v: boolean) => void;
    configError: boolean;
}

export function useDeposit(): UseDepositReturn {
    // Move all useState declarations from DepositModal.tsx here.
    // Move the useEffect that fetches /deposit/config here.
    // Return all state and setters.
    throw new Error('Implement: copy all useState and useEffect from DepositModal.tsx');
}
```

> **Important:** Open DepositModal.tsx, copy all `useState` calls and the `useEffect` for `/deposit/config` into this hook. Do not change the logic.

- [ ] **Step 4: Update DepositModal to use the hook and constants**

In DepositModal.tsx, remove all moved constants and state, and replace with:

```tsx
import { useDeposit } from './useDeposit';
import { CURRENCIES, CRYPTO_NETWORKS } from './depositConstants';

export default function DepositModal({ visible, onClose }) {
    const deposit = useDeposit();
    // render panels using deposit.*
}
```

- [ ] **Step 5: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 6: Commit**

```bash
git add resources/js/Components/Deposit/depositConstants.ts resources/js/Components/Deposit/useDeposit.ts resources/js/Components/Deposit/DepositModal.tsx
git commit -m "refactor: extract deposit constants and useDeposit hook"
```

---

### Task 13: Split DepositModal into form components

**Files:**
- Create: `resources/js/Components/Deposit/DepositMethodSelector.tsx`
- Create: `resources/js/Components/Deposit/DepositCardForm.tsx`
- Create: `resources/js/Components/Deposit/DepositCryptoForm.tsx`
- Create: `resources/js/Components/Deposit/DepositSkinsForm.tsx`
- Modify: `resources/js/Components/Deposit/DepositModal.tsx`

- [ ] **Step 1: Create `resources/js/Components/Deposit/DepositMethodSelector.tsx`**

```tsx
import type { DepositMethod } from './depositConstants';

interface DepositMethodSelectorProps {
    method: DepositMethod;
    onChange: (m: DepositMethod) => void;
}

// Read DepositModal.tsx and find the method tabs/selector section.
// Extract it here. The selector shows Card, Crypto, Skins, SBP tabs.

export default function DepositMethodSelector({ method, onChange }: DepositMethodSelectorProps) {
    // Copy method selector JSX from DepositModal.tsx
    return <div>{/* tabs JSX */}</div>;
}
```

- [ ] **Step 2: Create `resources/js/Components/Deposit/DepositCardForm.tsx`**

```tsx
import Input from '@/Components/UI/Input';
import Button from '@/Components/UI/Button';

interface DepositCardFormProps {
    amount: string;
    onAmountChange: (v: string) => void;
    rates: Record<string, number>;
    processing: boolean;
    onSubmit: () => void;
}

export default function DepositCardForm(props: DepositCardFormProps) {
    // Copy card payment form JSX from DepositModal.tsx
    // Use Input component from @/Components/UI/Input for text fields
    // Use Button component for submit button with loading={props.processing}
    return <div>{/* card form JSX */}</div>;
}
```

- [ ] **Step 3: Create `resources/js/Components/Deposit/DepositCryptoForm.tsx`**

```tsx
import { CRYPTO_NETWORKS } from './depositConstants';

interface DepositCryptoFormProps {
    network: string;
    onNetworkChange: (v: string) => void;
    amount: string;
    onAmountChange: (v: string) => void;
}

export default function DepositCryptoForm(props: DepositCryptoFormProps) {
    // Copy crypto form JSX from DepositModal.tsx
    return <div>{/* crypto form JSX */}</div>;
}
```

- [ ] **Step 4: Create `resources/js/Components/Deposit/DepositSkinsForm.tsx`**

```tsx
interface DepositSkinsFormProps {
    processing: boolean;
    onSubmit: () => void;
}

export default function DepositSkinsForm({ processing, onSubmit }: DepositSkinsFormProps) {
    // Copy skins deposit form JSX from DepositModal.tsx
    return <div>{/* skins form JSX */}</div>;
}
```

- [ ] **Step 5: Compose everything in DepositModal.tsx**

DepositModal becomes a thin composer (~80 lines):

```tsx
import Modal from '@/Components/UI/Modal';
import { useDeposit } from './useDeposit';
import DepositMethodSelector from './DepositMethodSelector';
import DepositCardForm from './DepositCardForm';
import DepositCryptoForm from './DepositCryptoForm';
import DepositSkinsForm from './DepositSkinsForm';
import { SkeletonDepositForm } from '@/Components/UI/Skeleton';

interface DepositModalProps {
    visible: boolean;
    onClose: () => void;
}

export default function DepositModal({ visible, onClose }: DepositModalProps) {
    const deposit = useDeposit();

    return (
        <Modal visible={visible} onClose={onClose} maxWidth="max-w-[480px]">
            <DepositMethodSelector method={deposit.method} onChange={deposit.setMethod} />
            {deposit.configError ? (
                <div className="text-center text-danger text-sm py-4">
                    Ошибка загрузки. <button onClick={() => window.location.reload()} className="underline">Обновить</button>
                </div>
            ) : (
                <>
                    {deposit.method === 'card' && <DepositCardForm {...deposit} onSubmit={() => {}} />}
                    {deposit.method === 'crypto' && <DepositCryptoForm {...deposit} />}
                    {deposit.method === 'skins' && <DepositSkinsForm processing={deposit.processing} onSubmit={() => {}} />}
                </>
            )}
        </Modal>
    );
}
```

- [ ] **Step 6: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 7: Commit**

```bash
git add resources/js/Components/Deposit/
git commit -m "refactor: split DepositModal into MethodSelector, CardForm, CryptoForm, SkinsForm"
```

---

### Task 14: Decompose Market/Index

**Files:**
- Create: `resources/js/Components/Market/MarketToolbar.tsx`
- Create: `resources/js/Components/Market/SkinGrid.tsx`
- Create: `resources/js/Components/Market/useMarket.ts`
- Modify: `resources/js/Pages/Market/Index.tsx`

- [ ] **Step 1: Read current Market/Index.tsx**

```bash
wc -l resources/js/Pages/Market/Index.tsx
grep -n "useState\|useCallback\|useMemo\|filter\|search\|sort" resources/js/Pages/Market/Index.tsx | head -20
```

- [ ] **Step 2: Create `resources/js/Components/Market/useMarket.ts`**

Extract ALL state from Market/Index.tsx:

```ts
import { useState, useCallback, useMemo } from 'react';
import type { Skin } from '@/types';
import { router } from '@inertiajs/react';

export interface MarketFilters {
    search: string;
    minPrice: string;
    maxPrice: string;
    sort: string;
}

export interface UseMarketReturn {
    filters: MarketFilters;
    setSearch: (v: string) => void;
    setMinPrice: (v: string) => void;
    setMaxPrice: (v: string) => void;
    setSort: (v: string) => void;
    selected: Set<number>;
    toggleSelect: (id: number) => void;
    clearSelected: () => void;
    buying: boolean;
    handleBuy: (skinIds: number[]) => void;
    applyFilters: () => void;
}

export function useMarket(initialSkins: Skin[]): UseMarketReturn {
    // Copy ALL useState, useCallback from Market/Index.tsx here.
    throw new Error('Implement: copy all state from Market/Index.tsx');
}
```

> Open Market/Index.tsx and copy all state declarations and handlers.

- [ ] **Step 3: Create `resources/js/Components/Market/MarketToolbar.tsx`**

```tsx
import Input from '@/Components/UI/Input';
import Button from '@/Components/UI/Button';
import type { MarketFilters } from './useMarket';

interface MarketToolbarProps {
    filters: MarketFilters;
    onSearchChange: (v: string) => void;
    onMinPriceChange: (v: string) => void;
    onMaxPriceChange: (v: string) => void;
    onSortChange: (v: string) => void;
    onApply: () => void;
    selectedCount: number;
    onBuySelected: () => void;
    buying: boolean;
}

export default function MarketToolbar(props: MarketToolbarProps) {
    // Copy toolbar/filter JSX from Market/Index.tsx
    // Replace inline inputs with <Input /> component
    // Replace inline buy button with <Button loading={props.buying} />
    return <div>{/* toolbar JSX */}</div>;
}
```

- [ ] **Step 4: Create `resources/js/Components/Market/SkinGrid.tsx`**

```tsx
import React, { memo } from 'react';
import type { Skin } from '@/types';
import SkinCard from '@/Components/Upgrade/SkinCard';
import { SkeletonSkinCard } from '@/Components/UI/Skeleton';

interface SkinGridProps {
    skins: Skin[];
    selected: Set<number>;
    onToggle: (id: number) => void;
    loading?: boolean;
}

export default memo(function SkinGrid({ skins, selected, onToggle, loading }: SkinGridProps) {
    if (loading) {
        return (
            <div className="grid grid-cols-2 xs:grid-cols-3 lg:grid-cols-4 wide:grid-cols-5 gap-3">
                {Array.from({ length: 12 }).map((_, i) => <SkeletonSkinCard key={i} />)}
            </div>
        );
    }

    return (
        <div className="grid grid-cols-2 xs:grid-cols-3 lg:grid-cols-4 wide:grid-cols-5 gap-3">
            {skins.map((skin) => (
                <SkinCard
                    key={skin.id}
                    skin={skin}
                    selected={selected.has(skin.id)}
                    onToggle={() => onToggle(skin.id)}
                />
            ))}
        </div>
    );
});
```

- [ ] **Step 5: Slim down Market/Index.tsx**

```tsx
import { useMarket } from '@/Components/Market/useMarket';
import MarketToolbar from '@/Components/Market/MarketToolbar';
import SkinGrid from '@/Components/Market/SkinGrid';
import AppLayout from '@/Layouts/AppLayout';
import type { PageProps } from '@/types';
import type { Skin } from '@/types';

interface MarketProps {
    skins: Skin[];
}

export default function MarketIndex({ skins, auth }: PageProps<MarketProps>) {
    const market = useMarket(skins);

    return (
        <AppLayout auth={auth}>
            <div className="flex flex-col gap-4 p-4">
                <MarketToolbar
                    filters={market.filters}
                    onSearchChange={market.setSearch}
                    onMinPriceChange={market.setMinPrice}
                    onMaxPriceChange={market.setMaxPrice}
                    onSortChange={market.setSort}
                    onApply={market.applyFilters}
                    selectedCount={market.selected.size}
                    onBuySelected={() => market.handleBuy([...market.selected])}
                    buying={market.buying}
                />
                <SkinGrid
                    skins={skins}
                    selected={market.selected}
                    onToggle={market.toggleSelect}
                />
            </div>
        </AppLayout>
    );
}
```

- [ ] **Step 6: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 7: Commit**

```bash
git add resources/js/Components/Market/ resources/js/Pages/Market/Index.tsx
git commit -m "refactor: decompose Market/Index into SkinGrid, MarketToolbar, useMarket"
```

---

## Phase 3 — Inertia Best Practices

### Task 15: Add Link prefetching to navigation

**Files:**
- Modify: `resources/js/Components/Layout/Header/index.tsx`

- [ ] **Step 1: Update NavChips to use prefetch**

In `resources/js/Components/Layout/Header/index.tsx`, update the `NavChips` function:

```tsx
function NavChips() {
    return (
        <div className="flex gap-[3px] items-stretch">
            <Link href="/market" prefetch="hover">
                <Chip interactive className="text-[#23262C] hover:text-white">
                    <MarketIcon />
                    <ChipLabel tone="inherit">Рынок Скинов</ChipLabel>
                </Chip>
            </Link>
            <Link href="/provably-fair" prefetch="hover">
                <Chip interactive className="text-[#23262C] hover:text-white">
                    <FaqIcon />
                    <ChipLabel tone="inherit">FAQ</ChipLabel>
                </Chip>
            </Link>
        </div>
    );
}
```

Also add `prefetch="hover"` to the Logo link:

```tsx
<Link href="/" prefetch="hover" className="...">
```

And on the ProfileChip link — open `resources/js/Components/Layout/Header/sections/ProfileChip.tsx` and add `prefetch="hover"` to the profile link.

- [ ] **Step 2: Commit**

```bash
git add resources/js/Components/Layout/Header/
git commit -m "feat: add Inertia prefetch=hover to navigation links"
```

---

### Task 16: Convert Market filtering to Inertia partial reload

**Files:**
- Modify: `resources/js/Components/Market/useMarket.ts`

- [ ] **Step 1: Replace axios filter calls with router.reload**

In `resources/js/Components/Market/useMarket.ts`, find any `axios.get` calls used for filtering. Replace with:

```ts
import { router } from '@inertiajs/react';

const applyFilters = useCallback(() => {
    router.reload({
        data: {
            search: filters.search,
            min_price: filters.minPrice,
            max_price: filters.maxPrice,
            sort: filters.sort,
        },
        only: ['skins'],
        preserveScroll: true,
        preserveState: true,
    });
}, [filters]);
```

- [ ] **Step 2: Verify the backend MarketController accepts these params**

```bash
grep -n "search\|min_price\|max_price\|sort" app/Http/Controllers/MarketController.php | head -20
```

If the controller already accepts query params for filtering, we're done. If not, we need to update it — but typically Inertia partial reloads just pass query params to the same controller action.

- [ ] **Step 3: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/Market/useMarket.ts
git commit -m "feat: market filtering via Inertia partial reload (only skins)"
```

---

### Task 17: Convert ProfileSidebar and SellModal to useForm

**Files:**
- Modify: `resources/js/Components/Profile/ProfileSidebar.tsx`
- Modify: `resources/js/Components/Profile/SellModal.tsx`

- [ ] **Step 1: Read current ProfileSidebar form handling**

```bash
grep -n "router.post\|useState\|processing\|tradeForm" resources/js/Components/Profile/ProfileSidebar.tsx | head -20
```

- [ ] **Step 2: Update ProfileSidebar trade URL form to use useForm**

In `ProfileSidebar.tsx`, replace any `router.post` + manual state with `useForm`:

```tsx
import { useForm } from '@inertiajs/react';

// Inside component:
const tradeForm = useForm({ trade_url: user?.trade_url ?? '' });

const handleSubmitTradeUrl = () => {
    tradeForm.post(route('profile.trade-url'), {
        preserveScroll: true,
        onSuccess: () => tradeForm.reset(),
    });
};
```

Use `tradeForm.processing` for button loading state, `tradeForm.errors.trade_url` for error display.

Replace the trade URL input with `<Input>` from `@/Components/UI/Input`:

```tsx
<Input
    value={tradeForm.data.trade_url}
    onChange={(e) => tradeForm.setData('trade_url', e.target.value)}
    error={tradeForm.errors.trade_url}
    placeholder="https://steamcommunity.com/tradeoffer/new/..."
/>
```

- [ ] **Step 3: Update SellModal to use useForm**

In `SellModal.tsx`, read current implementation then replace `router.post` + manual state with:

```tsx
import { useForm } from '@inertiajs/react';

const form = useForm({ user_skin_id: skinId });

const handleSell = () => {
    form.post(route('profile.sell'), {
        preserveScroll: true,
        onSuccess: onClose,
    });
};
```

Use `<Button loading={form.processing}>Продать</Button>`.

- [ ] **Step 4: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/Profile/ProfileSidebar.tsx resources/js/Components/Profile/SellModal.tsx
git commit -m "feat: convert ProfileSidebar and SellModal to Inertia useForm"
```

---

## Phase 4 — UX & Animations

### Task 18: Upgrade PageTransition with Framer Motion

**Files:**
- Modify: `resources/js/Components/UI/PageTransition.tsx`
- Modify: `resources/js/app.tsx`

- [ ] **Step 1: Rewrite PageTransition.tsx**

Replace the entire file content:

```tsx
import { motion, AnimatePresence } from 'framer-motion';
import { router } from '@inertiajs/react';
import { ReactNode, useEffect, useRef, useState } from 'react';

interface PageTransitionProps {
    children: ReactNode;
}

export default function PageTransition({ children }: PageTransitionProps) {
    const [progress, setProgress] = useState(0);
    const [visible, setVisible] = useState(false);
    const timerRef = useRef<ReturnType<typeof setTimeout>>();

    useEffect(() => {
        const removeStart = router.on('start', () => {
            clearTimeout(timerRef.current);
            setProgress(0);
            timerRef.current = setTimeout(() => {
                setVisible(true);
                setProgress(70);
            }, 100);
        });

        const removeProgress = router.on('progress', (event) => {
            if (event.detail.progress?.percentage) {
                setProgress(Math.max(70, event.detail.progress.percentage));
            }
        });

        const removeFinish = router.on('finish', () => {
            clearTimeout(timerRef.current);
            setProgress(100);
            timerRef.current = setTimeout(() => {
                setVisible(false);
                setProgress(0);
            }, 400);
        });

        return () => {
            removeStart();
            removeProgress();
            removeFinish();
            clearTimeout(timerRef.current);
        };
    }, []);

    return (
        <>
            {/* Progress bar */}
            <AnimatePresence>
                {visible && (
                    <motion.div
                        className="fixed top-0 left-0 h-[2px] z-[99999] bg-brand"
                        initial={{ width: '0%', opacity: 1 }}
                        animate={{ width: `${progress}%`, opacity: 1 }}
                        exit={{ opacity: 0 }}
                        transition={{ duration: 0.4, ease: 'easeOut' }}
                    />
                )}
            </AnimatePresence>

            {/* Page content */}
            {children}
        </>
    );
}
```

- [ ] **Step 2: Add AnimatePresence page wrapper in app.tsx**

In `resources/js/app.tsx`, update the setup function to add page fade:

```tsx
import { AnimatePresence, motion } from 'framer-motion';
import { router } from '@inertiajs/react';

// In the setup function, wrap App in AnimatePresence:
// Note: Inertia v2 handles page key via the component itself
// We add a simple fade via CSS that's already in PageTransition
// The AnimatePresence here wraps the App for initial mount animation
root.render(
    <ErrorBoundary>
        <ToastProvider>
            <PageTransition>
                <App {...props} />
            </PageTransition>
        </ToastProvider>
    </ErrorBoundary>,
);
```

> **Note:** Inertia v2 manages page component swapping. For per-page transitions, the fade is handled via PageTransition's opacity control. AnimatePresence at app level handles mount animation only.

- [ ] **Step 3: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/UI/PageTransition.tsx resources/js/app.tsx
git commit -m "feat: Framer Motion progress bar in PageTransition"
```

---

### Task 19: Animate Modal with Framer Motion

**Files:**
- Modify: `resources/js/Components/UI/Modal.tsx`

- [ ] **Step 1: Rewrite Modal.tsx with Framer Motion**

Replace the entire `Modal.tsx` content:

```tsx
import { ReactNode } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

interface ModalProps {
    visible: boolean;
    onClose: () => void;
    children: ReactNode;
    maxWidth?: string;
}

const MODAL_STYLE = {
    border: '1px solid rgba(255, 255, 255, 0.21)',
    background: 'radial-gradient(101.46% 49.85% at 100% 58.09%, rgba(255, 255, 255, 0.05) 0%, rgba(0, 0, 0, 0.00) 52.13%), linear-gradient(180deg, rgba(4, 6, 10, 0.70) 0%, rgba(7, 10, 16, 0.70) 100%)',
    boxShadow: '0 26px 80px 0 rgba(0, 0, 0, 0.30)',
};

export default function Modal({ visible, onClose, children, maxWidth = 'max-w-[400px]' }: ModalProps) {
    return (
        <AnimatePresence>
            {visible && (
                <motion.div
                    className="flex z-[1000] items-end lg:items-center justify-center bg-black/40 fixed inset-0"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.2 }}
                    onClick={onClose}
                >
                    {/* Mobile bottom-sheet */}
                    <motion.div
                        onClick={(e) => e.stopPropagation()}
                        style={MODAL_STYLE}
                        className={`flex lg:hidden rounded-t-[20px] backdrop-blur-[70px] w-full p-[25px] gap-5 flex-col max-h-[85vh] overflow-y-auto`}
                        initial={{ y: '100%' }}
                        animate={{ y: 0 }}
                        exit={{ y: '100%' }}
                        transition={{ type: 'spring', damping: 30, stiffness: 300 }}
                    >
                        <div className="flex justify-center pb-1">
                            <div className="w-[40px] h-[4px] rounded-full bg-white/20" />
                        </div>
                        {children}
                    </motion.div>

                    {/* Desktop modal */}
                    <motion.div
                        onClick={(e) => e.stopPropagation()}
                        style={MODAL_STYLE}
                        className={`hidden lg:flex rounded-[20px] backdrop-blur-[70px] w-full ${maxWidth} p-[25px] gap-5 flex-col`}
                        initial={{ opacity: 0, scale: 0.95, y: 8 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.95, y: 8 }}
                        transition={{ type: 'spring', damping: 25, stiffness: 300 }}
                    >
                        {children}
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
```

> **Note:** The `1024:` breakpoint classes are replaced with `lg:` since the standard Tailwind `lg` is 1024px — the same value. For `1155px` and `550px`, we use the named tokens `wide:` and `xs:` defined in Phase 1.

- [ ] **Step 2: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/UI/Modal.tsx
git commit -m "feat: Framer Motion animations for Modal (spring bottom-sheet and scale fade)"
```

---

### Task 20: Animate Toast notifications

**Files:**
- Modify: `resources/js/Components/UI/Toast.tsx`

- [ ] **Step 1: Read the full Toast.tsx**

```bash
cat resources/js/Components/UI/Toast.tsx
```

- [ ] **Step 2: Add Framer Motion to toast items**

In `Toast.tsx`, find the individual toast rendering. Wrap each toast in `motion.div` with slide-in animation:

```tsx
import { motion, AnimatePresence } from 'framer-motion';

// In the ToastProvider render, replace the toast list with:
<div className="fixed bottom-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
    <AnimatePresence initial={false}>
        {toasts.map((toast) => (
            <motion.div
                key={toast.id}
                layout
                initial={{ opacity: 0, x: 60, scale: 0.95 }}
                animate={{ opacity: 1, x: 0, scale: 1 }}
                exit={{ opacity: 0, x: 60, scale: 0.95 }}
                transition={{ type: 'spring', damping: 20, stiffness: 300 }}
                className="pointer-events-auto"
            >
                {/* existing toast content */}
            </motion.div>
        ))}
    </AnimatePresence>
</div>
```

Keep the existing toast content (type color, message, auto-dismiss timer) unchanged. Only add the motion wrapper.

- [ ] **Step 3: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/UI/Toast.tsx
git commit -m "feat: Framer Motion slide-in/out animations for Toast"
```

---

### Task 21: Animate LiveFeed items

**Files:**
- Modify: `resources/js/Components/Upgrade/LiveFeed.tsx`
- Modify: `resources/js/Components/Upgrade/LiveFeedItem.tsx`

- [ ] **Step 1: Read LiveFeed.tsx**

```bash
cat resources/js/Components/Upgrade/LiveFeed.tsx
```

- [ ] **Step 2: Fix feedCounter module-level bug**

In `LiveFeed.tsx`, find `let feedCounter = 0` at module level. Move it inside the component as a `useRef`:

```tsx
const feedCounterRef = useRef(0);

// Replace feedCounter++ with feedCounterRef.current++
// Replace feedCounter in key with feedCounterRef.current
```

- [ ] **Step 3: Add AnimatePresence to the feed list**

Wrap the feed items with `AnimatePresence`:

```tsx
import { AnimatePresence } from 'framer-motion';

// In the return JSX, wrap the feed list:
<div className="flex flex-col overflow-hidden">
    <AnimatePresence initial={false}>
        {feedItems.map((item) => (
            <LiveFeedItem key={item.id} item={item} />
        ))}
    </AnimatePresence>
</div>
```

- [ ] **Step 4: Add motion to LiveFeedItem**

In `LiveFeedItem.tsx`, wrap the root element with `motion.div`:

```tsx
import { memo } from 'react';
import { motion } from 'framer-motion';

const LiveFeedItem = memo(function LiveFeedItem({ item }: LiveFeedItemProps) {
    return (
        <motion.div
            layout
            initial={{ opacity: 0, y: -20 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -20 }}
            transition={{ duration: 0.25, ease: 'easeOut' }}
            className="..." // existing classes
        >
            {/* existing content */}
        </motion.div>
    );
});

export default LiveFeedItem;
```

- [ ] **Step 5: Fix LiveFeed error handling**

In `LiveFeed.tsx`, find `.catch(() => {})` and replace:

```tsx
import { useToast } from '@/Components/UI/Toast';

// Inside component:
const { toast } = useToast();

// In the catch block:
.catch(() => {
    toast('error', 'Не удалось загрузить ленту апгрейдов');
});
```

- [ ] **Step 6: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 7: Commit**

```bash
git add resources/js/Components/Upgrade/LiveFeed.tsx resources/js/Components/Upgrade/LiveFeedItem.tsx
git commit -m "feat: AnimatePresence for LiveFeed, fix feedCounter bug, fix error handling"
```

---

### Task 22: Add SkinCard hover animation

**Files:**
- Modify: `resources/js/Components/Upgrade/SkinCard.tsx`

- [ ] **Step 1: Read current SkinCard.tsx**

```bash
cat resources/js/Components/Upgrade/SkinCard.tsx
```

- [ ] **Step 2: Add Framer Motion hover to SkinCard**

Wrap the root element of `SkinCard` with `motion.div` and add `whileHover`:

```tsx
import { memo } from 'react';
import { motion } from 'framer-motion';

const SkinCard = memo(function SkinCard({ skin, selected, onToggle, ...rest }: SkinCardProps) {
    return (
        <motion.div
            whileHover={{ scale: 1.02, y: -2 }}
            whileTap={{ scale: 0.98 }}
            transition={{ type: 'spring', damping: 20, stiffness: 300 }}
            onClick={onToggle}
            className={`relative flex flex-col overflow-hidden rounded-[16px] cursor-pointer border transition-colors duration-200
                ${selected ? 'border-brand' : 'border-border hover:border-border-active'}
            `}
        >
            {/* existing content unchanged */}
        </motion.div>
    );
});

export default SkinCard;
```

> **Note:** Adding `overflow-hidden` to the root fixes the SVG overflow bug identified in the audit.

- [ ] **Step 3: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/Upgrade/SkinCard.tsx
git commit -m "feat: SkinCard hover animation + fix overflow-hidden bug"
```

---

### Task 23: Add Upgrade GO button animations

**Files:**
- Modify: `resources/js/Components/Upgrade/UpgradeVideo.tsx` (or wherever the GO button lives)

- [ ] **Step 1: Find the GO button**

```bash
grep -n "GO\|go\|handleUpgrade\|onClick.*upgrade" resources/js/Components/Upgrade/UpgradeVideo.tsx resources/js/Components/Upgrade/UpgradeBlock.tsx
```

- [ ] **Step 2: Add pulse animation while processing and shake on loss**

Wrap the GO button with motion:

```tsx
import { motion } from 'framer-motion';

// GO button with states:
<motion.button
    onClick={handleUpgrade}
    disabled={isProcessing || !selectedInventorySkin || !selectedTargetSkin}
    animate={isProcessing ? { scale: [1, 1.03, 1], transition: { repeat: Infinity, duration: 0.8 } } : {}}
    whileTap={{ scale: 0.97 }}
    className="..."
>
    {isProcessing ? <Spinner /> : 'GO'}
</motion.button>
```

For win/loss result — in the result handler, add brief animation:

```tsx
// After result is known, if 'lose':
// trigger shake via motion variants on the result container
const shakeVariants = {
    shake: {
        x: [-8, 8, -8, 8, 0],
        transition: { duration: 0.4 }
    }
};
```

- [ ] **Step 3: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/Upgrade/
git commit -m "feat: GO button pulse animation while processing, shake on loss"
```

---

## Phase 5 — Performance

### Task 24: Add React.memo to list components

**Files:**
- Modify: `resources/js/Components/Market/MarketToolbar.tsx`
- Modify: `resources/js/Components/Market/SkinGrid.tsx` (already done in Task 14)
- Modify: `resources/js/Components/Upgrade/LiveFeedItem.tsx` (already done in Task 21)

- [ ] **Step 1: Wrap MarketToolbar with memo**

`MarketToolbar.tsx` should already be a named function. Wrap with `memo`:

```tsx
import { memo } from 'react';

const MarketToolbar = memo(function MarketToolbar(props: MarketToolbarProps) {
    // existing JSX
});

export default MarketToolbar;
```

- [ ] **Step 2: Verify SkinCard is wrapped with memo**

```bash
grep -n "memo" resources/js/Components/Upgrade/SkinCard.tsx
```

Expected: `memo(function SkinCard` present (added in Task 22).

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/Market/MarketToolbar.tsx
git commit -m "perf: wrap MarketToolbar with React.memo"
```

---

### Task 25: Fix useMemo gaps

**Files:**
- Modify: `resources/js/Components/Profile/ProfileStatCards.tsx`
- Modify: `resources/js/Components/Market/useMarket.ts`

- [ ] **Step 1: Read ProfileStatCards.tsx**

```bash
grep -n "splitPrice\|kopecks\|function split" resources/js/Components/Profile/ProfileStatCards.tsx
```

- [ ] **Step 2: Memoize splitPriceKopecks result in ProfileStatCards**

Find the `splitPriceKopecks` call in the render. Wrap in `useMemo`:

```tsx
import { useMemo } from 'react';

// If splitPriceKopecks(balance) is called in render:
const formattedBalance = useMemo(() => splitPriceKopecks(balance), [balance]);
const formattedProfit = useMemo(() => splitPriceKopecks(totalProfit), [totalProfit]);
```

- [ ] **Step 3: Ensure useMarket handlers are in useCallback**

In `useMarket.ts`, verify all handlers (toggleSelect, handleBuy, applyFilters, setSearch etc.) are wrapped in `useCallback` with correct dependency arrays.

```ts
const toggleSelect = useCallback((id: number) => {
    setSelected((prev) => {
        const next = new Set(prev);
        if (next.has(id)) next.delete(id);
        else next.add(id);
        return next;
    });
}, []);

const clearSelected = useCallback(() => setSelected(new Set()), []);
```

- [ ] **Step 4: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/Profile/ProfileStatCards.tsx resources/js/Components/Market/useMarket.ts
git commit -m "perf: useMemo for price formatting, useCallback for market handlers"
```

---

### Task 26: Add AbortController to useTargetSkins

**Files:**
- Modify: `resources/js/hooks/useTargetSkins.ts`

- [ ] **Step 1: Read current useTargetSkins.ts**

```bash
cat resources/js/hooks/useTargetSkins.ts
```

- [ ] **Step 2: Add AbortController and fix error handling**

```ts
import { useState, useEffect, useRef, useCallback } from 'react';
import axios from 'axios';
import { useToast } from '@/Components/UI/Toast';

// Inside the hook, in the effect that makes the API call:
const abortControllerRef = useRef<AbortController>();

useEffect(() => {
    if (!search && !filters) return;

    // Cancel previous request
    abortControllerRef.current?.abort();
    abortControllerRef.current = new AbortController();

    setLoading(true);

    axios.get('/api/target-skins', {
        params: { search, ...filters },
        signal: abortControllerRef.current.signal,
    })
    .then((response) => {
        setSkins(response.data.data);
        setPagination(response.data.meta);
    })
    .catch((error) => {
        if (axios.isCancel(error)) return; // ignore aborted requests
        toast('error', 'Не удалось загрузить скины для апгрейда');
    })
    .finally(() => setLoading(false));

    return () => abortControllerRef.current?.abort();
}, [search, /* other deps */]);
```

> **Important:** Read the actual `useTargetSkins.ts` first. Preserve the existing debounce logic (400ms). Only add AbortController on top of it, and replace the console.error with toast.

- [ ] **Step 3: Add useToast import**

```ts
import { useToast } from '@/Components/UI/Toast';

// Inside hook body:
const { toast } = useToast();
```

- [ ] **Step 4: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/hooks/useTargetSkins.ts
git commit -m "perf: AbortController in useTargetSkins, toast on error instead of console.error"
```

---

### Task 27: Virtualize Market skin grid

**Files:**
- Modify: `resources/js/Components/Market/SkinGrid.tsx`

- [ ] **Step 1: Add virtualization to SkinGrid**

Replace the grid in `SkinGrid.tsx` with a virtualized version using `@tanstack/react-virtual`:

```tsx
import { useRef } from 'react';
import { useVirtualizer } from '@tanstack/react-virtual';
import type { Skin } from '@/types';
import SkinCard from '@/Components/Upgrade/SkinCard';
import { SkeletonSkinCard } from '@/Components/UI/Skeleton';
import { memo } from 'react';

const COLUMNS = { xs: 2, sm: 3, lg: 4, wide: 5 };
const CARD_HEIGHT = 260; // approximate — adjust after visual check
const GAP = 12;

interface SkinGridProps {
    skins: Skin[];
    selected: Set<number>;
    onToggle: (id: number) => void;
    loading?: boolean;
}

export default memo(function SkinGrid({ skins, selected, onToggle, loading }: SkinGridProps) {
    const parentRef = useRef<HTMLDivElement>(null);

    // Determine columns based on container width
    const columns = 4; // default — can make responsive with ResizeObserver later

    const rows = Math.ceil(skins.length / columns);

    const virtualizer = useVirtualizer({
        count: rows,
        getScrollElement: () => parentRef.current,
        estimateSize: () => CARD_HEIGHT + GAP,
        overscan: 3,
    });

    if (loading) {
        return (
            <div className="grid grid-cols-2 xs:grid-cols-3 lg:grid-cols-4 wide:grid-cols-5 gap-3">
                {Array.from({ length: 12 }).map((_, i) => <SkeletonSkinCard key={i} />)}
            </div>
        );
    }

    return (
        <div
            ref={parentRef}
            className="overflow-auto"
            style={{ height: '70vh' }}
        >
            <div style={{ height: virtualizer.getTotalSize(), position: 'relative' }}>
                {virtualizer.getVirtualItems().map((virtualRow) => {
                    const rowStart = virtualRow.index * columns;
                    const rowSkins = skins.slice(rowStart, rowStart + columns);

                    return (
                        <div
                            key={virtualRow.key}
                            style={{
                                position: 'absolute',
                                top: virtualRow.start,
                                left: 0,
                                right: 0,
                                display: 'grid',
                                gridTemplateColumns: `repeat(${columns}, 1fr)`,
                                gap: GAP,
                            }}
                        >
                            {rowSkins.map((skin) => (
                                <SkinCard
                                    key={skin.id}
                                    skin={skin}
                                    selected={selected.has(skin.id)}
                                    onToggle={() => onToggle(skin.id)}
                                />
                            ))}
                        </div>
                    );
                })}
            </div>
        </div>
    );
});
```

- [ ] **Step 2: Visual check — open the market page**

```bash
npm run dev
```

Open `/market` in browser. Verify:
- Grid renders correctly
- Scroll is smooth
- Skeleton shows during loading
- Selection works

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/Market/SkinGrid.tsx
git commit -m "perf: virtualize Market skin grid with @tanstack/react-virtual"
```

---

## Phase 6 — Responsive Design & Bug Fixes

### Task 28: Fix UpgradeResult absolute positioning bug

**Files:**
- Modify: `resources/js/Components/Upgrade/UpgradeResult.tsx`

- [ ] **Step 1: Read UpgradeResult.tsx**

```bash
cat resources/js/Components/Upgrade/UpgradeResult.tsx
```

- [ ] **Step 2: Fix absolute element without relative parent**

In `UpgradeResult.tsx`, find line ~66 where there's `absolute left-1/2 top-[18px]`. Ensure the parent container has `relative`:

```tsx
// Find the parent div of the absolute element and add 'relative' class:
<div className="relative ...existing classes...">
    {/* ... */}
    <div className="absolute left-1/2 top-[18px] -translate-x-1/2 ...">
        {/* ... */}
    </div>
</div>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/Upgrade/UpgradeResult.tsx
git commit -m "fix: add relative to UpgradeResult parent of absolute element"
```

---

### Task 29: Fix Market mobile responsive layout

**Files:**
- Modify: `resources/js/Components/Market/SkinGrid.tsx`
- Modify: `resources/js/Components/Market/MarketToolbar.tsx`

- [ ] **Step 1: Update SkinGrid for mobile**

In `SkinGrid.tsx`, the loading skeleton grid already uses responsive classes. Verify the virtual grid also applies responsive columns.

Update the `columns` calculation in the virtualizer to be responsive. Add a simple check:

```tsx
import { useEffect, useState } from 'react';

function useColumns() {
    const [cols, setCols] = useState(4);
    useEffect(() => {
        function update() {
            const w = window.innerWidth;
            if (w < 550) setCols(2);
            else if (w < 1024) setCols(3);
            else if (w < 1155) setCols(4);
            else setCols(5);
        }
        update();
        window.addEventListener('resize', update);
        return () => window.removeEventListener('resize', update);
    }, []);
    return cols;
}

// In SkinGrid:
const columns = useColumns();
```

- [ ] **Step 2: Make MarketToolbar responsive**

In `MarketToolbar.tsx`, ensure the toolbar wraps on mobile:

```tsx
<div className="flex flex-col xs:flex-row gap-3 flex-wrap items-start xs:items-center">
    {/* search input — full width on mobile */}
    <div className="w-full xs:w-auto xs:flex-1">
        <Input ... />
    </div>
    {/* price range — row on mobile too */}
    <div className="flex gap-2 w-full xs:w-auto">
        <Input ... /> {/* min price */}
        <Input ... /> {/* max price */}
    </div>
    {/* sort and buy button */}
    <div className="flex gap-2 w-full xs:w-auto">
        {/* sort select */}
        {selectedCount > 0 && <Button loading={buying}>Купить ({selectedCount})</Button>}
    </div>
</div>
```

- [ ] **Step 3: Commit**

```bash
git add resources/js/Components/Market/
git commit -m "fix: Market grid responsive columns, toolbar wraps on mobile"
```

---

### Task 30: Fix Upgrade layout on mobile

**Files:**
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx`

- [ ] **Step 1: Make Upgrade panels stack vertically on mobile**

In `UpgradeBlock.tsx`, the panels are currently side-by-side. Update the layout:

```tsx
// Root container: column on mobile, row on lg
<div className="flex flex-col lg:flex-row gap-4 items-start">
    <UpgradeInventoryPanel ... />
    <div className="flex flex-col items-center gap-4 w-full lg:w-auto lg:flex-shrink-0">
        <UpgradeVideo ... />
        <UpgradeMultiplierBar ... />
    </div>
    <UpgradeTargetPanel ... />
</div>
```

- [ ] **Step 2: Commit**

```bash
git add resources/js/Components/Upgrade/UpgradeBlock.tsx
git commit -m "fix: Upgrade panels stack vertically on mobile"
```

---

### Task 31: Fix error handling — replace silent catches with Toast

**Files:**
- Modify: `resources/js/Components/Deposit/useDeposit.ts`

- [ ] **Step 1: Read useDeposit error handling**

```bash
grep -n "catch\|console.error" resources/js/Components/Deposit/useDeposit.ts
```

- [ ] **Step 2: Replace console.error with toast + configError flag**

In `useDeposit.ts`, the `/deposit/config` fetch error handler:

```ts
import { useToast } from '@/Components/UI/Toast';

// Inside useDeposit hook:
const { toast } = useToast();
const [configError, setConfigError] = useState(false);

// In the catch block:
.catch((error) => {
    if (!axios.isCancel(error)) {
        setConfigError(true);
        toast('error', 'Не удалось загрузить методы оплаты');
    }
});
```

The `configError` flag is already passed to `DepositModal` from Task 13 which shows the retry button.

- [ ] **Step 3: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Components/Deposit/useDeposit.ts
git commit -m "fix: deposit config error shows Toast and retry button instead of console.error"
```

---

### Task 32: Audit and fix Header mobile behaviour

**Files:**
- Modify: `resources/js/Components/Layout/Header/index.tsx`

- [ ] **Step 1: Audit current header on mobile**

```bash
npm run dev
```

Open the site at `xs` width (550px) in browser DevTools. Check:
- Balance chip visible?
- Profile chip overflows?
- Nav chips wrap?
- No horizontal scroll?

- [ ] **Step 2: Fix any overflow issues**

If the header has horizontal overflow on mobile, add `overflow-hidden` to the header and ensure elements use `min-w-0`:

```tsx
<header className="flex p-2.5 justify-between items-center w-full overflow-hidden">
    <Logo />
    <div className="flex items-center gap-3 min-w-0">
        {/* ... */}
    </div>
    <div className="flex items-center md:gap-3 gap-2 shrink-0">
        {/* ... */}
    </div>
</header>
```

- [ ] **Step 3: Replace `1155:` arbitrary values with `wide:` named token**

In all files under `resources/js/Components/Layout/Header/`, replace:
- `1155:flex` → `wide:flex`
- `1155:hidden` → `wide:hidden`

```bash
grep -rn "1155:" resources/js/Components/Layout/Header/
# Replace each occurrence
```

- [ ] **Step 4: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 5: Commit**

```bash
git add resources/js/Components/Layout/Header/
git commit -m "fix: header mobile layout, replace 1155: with wide: named token"
```

---

### Task 33: Final cleanup and GradientButton migration

**Files:**
- Modify: any files still using `GradientButton`
- Delete: `resources/js/Components/UI/GradientButton.tsx`
- Delete: `resources/js/Components/Layout/Header/icons.tsx`
- Delete: `resources/js/Components/Upgrade/icons.tsx`

- [ ] **Step 1: Find remaining GradientButton usages**

```bash
grep -rn "GradientButton" resources/js/ --include="*.tsx" --include="*.ts"
```

- [ ] **Step 2: Replace each usage with Button**

For each file that imports `GradientButton`, replace with `Button` from `@/Components/UI/Button`. Map variants:
- `variant="blue"` → `variant="primary"` (default)
- `variant="red"` → `variant="danger"`
- `variant="green"` → no direct equivalent, use `className` override if needed

- [ ] **Step 3: Delete GradientButton once all usages are gone**

```bash
grep -rn "GradientButton" resources/js/
# Should return 0 results

rm resources/js/Components/UI/GradientButton.tsx
```

- [ ] **Step 4: Delete old icon files**

```bash
grep -rn "Layout/Header/icons\|Upgrade/icons" resources/js/
# Should return 0 results (all migrated in Task 5)

rm resources/js/Components/Layout/Header/icons.tsx
rm resources/js/Components/Upgrade/icons.tsx
```

- [ ] **Step 5: Final TypeScript check**

```bash
npx tsc --noEmit 2>&1 | grep -v "node_modules"
```

Expected: 0 errors.

- [ ] **Step 6: Final build**

```bash
npm run build 2>&1 | tail -20
```

Expected: clean build, no warnings.

- [ ] **Step 7: Run Pint on any PHP files touched**

```bash
vendor/bin/pint --dirty --format agent
```

- [ ] **Step 8: Final commit**

```bash
git add -A
git commit -m "chore: delete GradientButton and old icon files after full migration"
```

---

### Task 34: ProvablyFair FAQ accordion animation

**Files:**
- Modify: `resources/js/Pages/ProvablyFair/Index.tsx`

- [ ] **Step 1: Read AccordionItem implementation**

```bash
grep -n "AccordionItem\|maxHeight\|overflow" resources/js/Pages/ProvablyFair/Index.tsx | head -20
```

- [ ] **Step 2: Replace maxHeight CSS hack with AnimatePresence**

The current accordion uses `maxHeight` for animation which is unreliable. Replace with Framer Motion:

```tsx
import { motion, AnimatePresence } from 'framer-motion';

function AccordionItem({ question, answer, isOpen, onToggle }) {
    return (
        <div className="border-b border-border">
            <button
                onClick={onToggle}
                className="flex w-full justify-between items-center py-4 text-left text-white font-medium"
            >
                <span>{question}</span>
                <motion.span
                    animate={{ rotate: isOpen ? 180 : 0 }}
                    transition={{ duration: 0.2 }}
                >
                    ▼
                </motion.span>
            </button>
            <AnimatePresence initial={false}>
                {isOpen && (
                    <motion.div
                        key="content"
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.25, ease: 'easeInOut' }}
                        className="overflow-hidden"
                    >
                        <div className="pb-4 text-text-muted text-sm leading-relaxed">
                            {answer}
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
```

- [ ] **Step 3: Verify build**

```bash
npm run build 2>&1 | tail -10
```

- [ ] **Step 4: Commit**

```bash
git add resources/js/Pages/ProvablyFair/Index.tsx
git commit -m "feat: FAQ accordion uses Framer Motion AnimatePresence instead of maxHeight hack"
```

---

### Task 35: Self-review — visual check all pages

- [ ] **Step 1: Start dev server**

```bash
npm run dev &
```

- [ ] **Step 2: Check each page at 3 viewport widths (375px, 768px, 1440px)**

Open browser DevTools. Check in order:

| Page | Check |
|---|---|
| `/` (Upgrade) | Panels stack on mobile, video center, multiplier bar visible |
| `/market` | Grid responsive (2→3→4→5 cols), toolbar wraps, filters work |
| `/profile/:id` | Sidebar + tabs look correct, no overflow |
| `/provably-fair` | FAQ accordion animates smoothly |
| Deposit modal | Opens with animation, form renders, method selector works |
| Header | No horizontal overflow at 375px, logo + buttons fit |

- [ ] **Step 3: Check animations**

| Animation | Expected |
|---|---|
| Page transition | Smooth progress bar top, content stable |
| Modal open/close | Scale+fade on desktop, spring slide on mobile |
| Toast | Slides in from right, exits with fade |
| SkinCard hover | Gentle scale + lift |
| LiveFeed new item | Slides in from top |
| FAQ accordion | Smooth height expand |
| GO button | Pulses while processing |

- [ ] **Step 4: Fix any visual issues found**

Fix each issue found during the visual check. Commit each fix separately.

- [ ] **Step 5: Final full build**

```bash
npm run build
```

Expected: 0 errors, 0 warnings related to our code.

- [ ] **Step 6: Final commit**

```bash
git add -A
git commit -m "refactor: frontend refactoring complete — all 6 phases"
```

---

## Summary

| Phase | Tasks | Key Outcome |
|---|---|---|
| 1 — Foundation | 1–7 | Shared UI library, @theme tokens, TypeScript fixes |
| 2 — Decomposition | 8–14 | Monoliths split, hooks extracted, clean architecture |
| 3 — Inertia | 15–17 | Prefetch, partial reloads, useForm |
| 4 — UX/Animations | 18–23 | Framer Motion everywhere, skeleton loaders |
| 5 — Performance | 24–27 | React.memo, useMemo, AbortController, virtualization |
| 6 — Responsive/Bugs | 28–35 | Mobile layouts, bug fixes, visual polish |
