# Upgrade Page Backend Integration Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Connect the existing React upgrade page to Laravel backend — Steam auth, real user inventory, paginated target skins with infinite scroll, POST /upgrade for real gameplay, balance/nick from auth.user.

**Architecture:** The backend is 70% ready (UpgradeController, SkinController, UpgradeService, Steam Socialite). Frontend needs to consume Inertia page props (`inventory`, `balance`) for user skins, fetch target skins via `/api/skins` with cursor pagination + infinite scroll, and POST to `/upgrade` for real games. Auth state comes from `usePage().props.auth.user`.

**Tech Stack:** Laravel 13, Inertia.js v2 + React 18, TypeScript, Ziggy for route generation, Axios for API calls.

---

### Task 1: Steam Auth — Wire Login Button to Backend

**Files:**
- Modify: `resources/js/Components/Layout/Header/index.tsx`
- Modify: `resources/js/Components/Layout/Header/sections/ProfileChip.tsx`
- Modify: `resources/js/Components/Layout/Header/sections/DepositBlock.tsx`

- [ ] **Step 1: Update Header to show Login/Profile based on auth state**

In `resources/js/Components/Layout/Header/index.tsx`, use `usePage()` from Inertia to read `auth.user`. If user is null, show Login button. If logged in, show ProfileChip + DepositBlock.

```tsx
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function Header() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;
    // ...
    // In right section: conditionally render DepositBlock + ProfileChip only if user
    // If !user: show only the Login button (already added by user in the file)
}
```

- [ ] **Step 2: Update Login button href to Steam route**

The existing Login button in Header should link to `/auth/steam`:

```tsx
<a href="/auth/steam" className="...">
    {/* Steam icon SVG */}
    <span>Войти</span>
</a>
```

Change `<button>` to `<a href="/auth/steam">` (full page redirect, not Inertia Link).

- [ ] **Step 3: Update ProfileChip to show real user data**

```tsx
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function ProfileChip() {
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;
    if (!user) return null;

    return (
        <Chip interactive className="bg-accent px-2.5">
            <img src={user.avatar_url || ''} className="w-[24px] h-[24px] rounded-full" alt="" />
            <ChipLabel className="hidden 1155:inline">{user.username}</ChipLabel>
        </Chip>
    );
}
```

- [ ] **Step 4: Update DepositBlock to show real balance**

```tsx
import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function DepositBlock() {
    const { auth } = usePage<PageProps>().props;
    const balance = auth.user?.balance ?? 0;
    const formatted = (balance / 100).toLocaleString('ru-RU');
    // Use `formatted` instead of hardcoded "12 464"
}
```

- [ ] **Step 5: Add Logout action**

Add `<Link href="/auth/logout" method="post" as="button">` somewhere in profile dropdown or ProfileChip click menu (future task).

- [ ] **Step 6: Commit**
```bash
git add resources/js/Components/Layout/Header/
git commit -m "feat: wire Steam auth login/profile to backend"
```

---

### Task 2: Upgrade Page — Real Inventory from Backend

**Files:**
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx`
- Modify: `resources/js/Pages/Upgrade/Index.tsx`
- Delete: `resources/js/mock/inventory.ts` (after wiring)

- [ ] **Step 1: Pass Inertia page props to UpgradeBlock**

`UpgradeController::index()` already returns `inventory` and `balance` via Inertia. Update `Pages/Upgrade/Index.tsx`:

```tsx
import { usePage } from '@inertiajs/react';
import { PageProps, UserSkin } from '@/types';

interface UpgradePageProps extends PageProps {
    inventory: Array<{
        id: number;
        skin: import('@/types').Skin;
        price_at_acquisition: number;
    }>;
    balance: number;
}

export default function Upgrade() {
    const { inventory, balance } = usePage<UpgradePageProps>().props;
    // Pass to UpgradeBlock
    return (
        <div className="flex items-stretch min-h-screen 1024:flex-row flex-col">
            <VideoPreloader srcs={ALL_UPGRADE_VIDEO_SRCS} />
            <LiveFeed />
            <div className="flex flex-col w-full flex-1 min-h-0">
                <Header />
                <UpgradeBlock inventory={inventory} balance={balance} />
            </div>
        </div>
    );
}
```

- [ ] **Step 2: Convert inventory prop to SkinEntry format in UpgradeBlock**

Map backend inventory items into the `SkinEntry` format that `SkinCard` expects:

```tsx
const inventoryItems: SkinEntry[] = useMemo(() =>
    inventory.map((item) => ({
        id: item.id, // user_skin ID
        rarity: mapRarityColor(item.skin.rarity_color),
        weapon: item.skin.market_hash_name.split(' | ')[0] || '',
        name: item.skin.market_hash_name.split(' | ')[1] || item.skin.market_hash_name,
        price: formatPrice(item.price_at_acquisition),
        image: item.skin.image_url || '',
    })),
[inventory]);
```

Add helper `mapRarityColor(hex: string): SkinRarity` that maps backend hex colors to frontend rarity enum.

- [ ] **Step 3: Replace MOCK_INVENTORY with real inventory in left panel**

Replace `MOCK_INVENTORY.map(...)` in the inventory SkinsPanel with `inventoryItems.map(...)`.

- [ ] **Step 4: Show empty state when no skins**

If `inventoryItems.length === 0`, show message "Нет доступных скинов" in the panel.

- [ ] **Step 5: Commit**
```bash
git add resources/js/
git commit -m "feat: display real user inventory from backend"
```

---

### Task 3: Target Skins — Paginated API with Infinite Scroll

**Files:**
- Create: `resources/js/hooks/useTargetSkins.ts`
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx`
- Modify: `resources/js/Components/Upgrade/SkinsPanel.tsx`

- [ ] **Step 1: Create useTargetSkins hook**

Hook fetches from `/api/skins` with pagination, search, sort, and min_price filters. Returns data + `loadMore` callback + `hasMore` flag.

```tsx
// resources/js/hooks/useTargetSkins.ts
import { useCallback, useEffect, useRef, useState } from 'react';
import axios from 'axios';
import { Skin } from '@/types';

interface UseTargetSkinsParams {
    search: string;
    sort: 'price' | 'name' | null;
    direction: 'asc' | 'desc';
    minPrice: string;
    inventoryPrice: number | null; // exclude skins cheaper than this
}

interface PaginatedResponse {
    data: Skin[];
    next_page_url: string | null;
    current_page: number;
}

export function useTargetSkins(params: UseTargetSkinsParams) {
    const [skins, setSkins] = useState<Skin[]>([]);
    const [loading, setLoading] = useState(false);
    const [hasMore, setHasMore] = useState(true);
    const nextUrl = useRef<string | null>(null);
    const paramsRef = useRef(params);
    paramsRef.current = params;

    // Reset and fetch page 1 when filters change
    const fetchFirst = useCallback(async () => {
        setLoading(true);
        try {
            const res = await axios.get<PaginatedResponse>('/api/skins', {
                params: {
                    per_page: 50,
                    sort: params.sort || 'price',
                    direction: params.direction || 'desc',
                    q: params.search || undefined,
                },
            });
            let items = res.data.data;
            // Client-side filter: exclude skins cheaper than inventory
            if (params.inventoryPrice) {
                items = items.filter(s => s.price > params.inventoryPrice!);
            }
            setSkins(items);
            nextUrl.current = res.data.next_page_url;
            setHasMore(!!res.data.next_page_url);
        } catch {
            // noop
        } finally {
            setLoading(false);
        }
    }, [params.search, params.sort, params.direction, params.inventoryPrice]);

    useEffect(() => { fetchFirst(); }, [fetchFirst]);

    // Fetch next page (infinite scroll)
    const loadMore = useCallback(async () => {
        if (loading || !hasMore || !nextUrl.current) return;
        setLoading(true);
        try {
            const res = await axios.get<PaginatedResponse>(nextUrl.current);
            let items = res.data.data;
            if (paramsRef.current.inventoryPrice) {
                items = items.filter(s => s.price > paramsRef.current.inventoryPrice!);
            }
            setSkins(prev => [...prev, ...items]);
            nextUrl.current = res.data.next_page_url;
            setHasMore(!!res.data.next_page_url);
        } catch {
            // noop
        } finally {
            setLoading(false);
        }
    }, [loading, hasMore]);

    return { skins, loading, hasMore, loadMore };
}
```

- [ ] **Step 2: Add scroll detection to SkinsPanel**

Add `onScroll` handler that detects when user scrolls near bottom:

```tsx
// In SkinsPanel.tsx — add onScrollEnd prop
interface SkinsPanelProps {
    // ...existing
    onScrollEnd?: () => void;
}

// In the scrollable grid div:
const handleScroll = (e: React.UIEvent<HTMLDivElement>) => {
    const el = e.currentTarget;
    if (el.scrollHeight - el.scrollTop - el.clientHeight < 100) {
        onScrollEnd?.();
    }
};
// <div onScroll={handleScroll} className="skins-scroll grid ...">
```

- [ ] **Step 3: Wire useTargetSkins into UpgradeBlock**

Replace `filteredTargets` useMemo with `useTargetSkins` hook. Pass `loadMore` to target SkinsPanel's `onScrollEnd`.

```tsx
const invPrice = inventorySkin
    ? parsePrice(inventorySkin.price)
    : null;

const { skins: targetSkins, loading: targetsLoading, loadMore } = useTargetSkins({
    search,
    sort: priceSort === 'asc' || priceSort === 'desc' ? 'price' : null,
    direction: priceSort || 'desc',
    minPrice,
    inventoryPrice: invPrice,
});
```

Convert `targetSkins` (API `Skin[]`) to `SkinEntry[]` format for SkinCard rendering.

- [ ] **Step 4: Show loading spinner at bottom of target panel**

Add a small spinner or "Загрузка..." at the end of the target panel while `targetsLoading` is true.

- [ ] **Step 5: Commit**
```bash
git add resources/js/
git commit -m "feat: paginated target skins with infinite scroll from API"
```

---

### Task 4: POST /upgrade — Real Gameplay

**Files:**
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx`

- [ ] **Step 1: Replace random roll with backend POST**

In `handleGo`, instead of `Math.random()`, POST to `/upgrade`:

```tsx
const handleGo = useCallback(async () => {
    if (!canStart || !inventorySkin || !targetSkin) return;

    setStage('closing');

    try {
        const response = await axios.post('/upgrade', {
            user_skin_ids: [inventorySkin.id], // user_skin ID from backend
            balance_amount: 0, // TODO: support balance betting
            target_skin_id: targetSkin.backendSkinId, // skin catalog ID
        });

        // Backend returns redirect with flash message
        // Inertia will handle the redirect automatically
        // Check flash for result
        const flash = response.data?.props?.flash;
        const isWin = !!flash?.success;
        setOutcome(isWin ? 'success' : 'fail');

    } catch (err) {
        // On validation error, reset
        setStage('idle');
        setOutcome(null);
        return;
    }
}, [canStart, inventorySkin, targetSkin]);
```

Note: Since UpgradeController returns `back()->with()` (redirect), Inertia handles this as a page reload with flash. We need to use `router.post()` from Inertia instead of axios:

```tsx
import { router } from '@inertiajs/react';

router.post('/upgrade', {
    user_skin_ids: [inventorySkin.id],
    balance_amount: 0,
    target_skin_id: targetSkin.backendSkinId,
}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: (page) => {
        const flash = page.props.flash as Flash;
        const isWin = !!flash.success;
        setOutcome(isWin ? 'success' : 'fail');
    },
    onError: () => {
        setStage('idle');
        setOutcome(null);
    },
});
```

- [ ] **Step 2: Track backend skin ID for target selection**

`SkinEntry` needs a `backendSkinId` field (the skin catalog ID) separate from display ID. Add to SkinEntry type:

```tsx
export type SkinEntry = {
    id: string | number;         // display/selection ID
    backendSkinId?: number;      // skin catalog ID for POST
    backendUserSkinId?: number;  // user_skin ID for inventory items
    // ...rest
};
```

Map from API response accordingly.

- [ ] **Step 3: Handle post-upgrade state**

After result stage auto-resets:
- Refresh inventory (Inertia will auto-refresh page props on redirect)
- Clear selected skins
- If win: new skin appears in inventory

Since `router.post` triggers Inertia page reload, `inventory` prop updates automatically.

- [ ] **Step 4: Show flash messages**

Read `flash.error` / `flash.success` from page props and show toast or inline message:

```tsx
const { flash } = usePage<PageProps>().props;
useEffect(() => {
    if (flash.error) { /* show error notification */ }
    if (flash.success) { /* show success notification */ }
}, [flash]);
```

- [ ] **Step 5: Commit**
```bash
git add resources/js/
git commit -m "feat: POST /upgrade for real gameplay via Inertia"
```

---

### Task 5: Route Fix — Upgrade Page for Guests

**Files:**
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/UpgradeController.php`

- [ ] **Step 1: Allow guests to view upgrade page**

Currently `/` inside auth middleware. Move upgrade GET route outside:

```php
// Public: guests can view upgrade page (with empty inventory)
Route::get('/', [UpgradeController::class, 'index'])->name('upgrade');

Route::middleware('auth')->group(function () {
    // Only POST requires auth
    Route::post('/upgrade', [UpgradeController::class, 'store'])
        ->name('upgrade.store')
        ->middleware('throttle:upgrade');
    // ...rest
});
```

- [ ] **Step 2: Handle guest in UpgradeController::index()**

```php
public function index(Request $request): Response
{
    $user = $request->user();

    $inventory = $user
        ? $user->userSkins()
            ->where('status', UserSkinStatus::Available)
            ->with('skin')
            ->get()
            ->map(fn ($us) => [
                'id' => $us->id,
                'skin' => (new SkinBriefResource($us->skin))->resolve($request),
                'price_at_acquisition' => $us->price_at_acquisition,
            ])
        : collect();

    return Inertia::render('Upgrade/Index', [
        'inventory' => $inventory,
        'balance' => $user?->balance ?? 0,
    ]);
}
```

- [ ] **Step 3: Frontend: disable GO button for guests**

```tsx
const isGuest = !usePage<PageProps>().props.auth.user;
// canStart should be false for guests
const canStart = useMemo(() =>
    !isGuest && stage === 'idle' && !!inventorySkin && /* ...rest */
, [isGuest, ...]);
```

- [ ] **Step 4: Commit**
```bash
git add routes/web.php app/Http/Controllers/UpgradeController.php resources/js/
git commit -m "feat: allow guests to view upgrade page, require auth for play"
```

---

### Task 6: Skin Rarity Mapping & Price Formatting

**Files:**
- Create: `resources/js/utils/skinHelpers.ts`

- [ ] **Step 1: Create helper functions**

```tsx
// resources/js/utils/skinHelpers.ts
import { SkinRarity } from '@/Components/Upgrade/LiveFeedItem';
import { Skin } from '@/types';
import { SkinEntry } from '@/Components/Upgrade/SkinCard';

const RARITY_COLOR_MAP: Record<string, SkinRarity> = {
    '#878F9D': 'cons',
    '#356C9D': 'indus',
    '#2D4FFA': 'mils',
    '#50318D': 'restr',
    '#A64BB5': 'class',
    '#EA2F2F': 'covrt',
    '#D4AF37': 'contra',
};

export function mapRarityColor(hex: string | null): SkinRarity {
    if (!hex) return 'cons';
    return RARITY_COLOR_MAP[hex.toUpperCase()] || RARITY_COLOR_MAP[hex] || 'cons';
}

export function formatKopecks(kopecks: number): string {
    const rubles = kopecks / 100;
    return rubles.toLocaleString('ru-RU', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 1,
    }) + '₽';
}

export function parseSkinName(marketHashName: string): { weapon: string; name: string } {
    const parts = marketHashName.split(' | ');
    return {
        weapon: parts[0]?.trim() || marketHashName,
        name: parts[1]?.trim() || '',
    };
}

export function apiSkinToEntry(skin: Skin, idPrefix = ''): SkinEntry {
    const { weapon, name } = parseSkinName(skin.market_hash_name);
    return {
        id: `${idPrefix}${skin.id}`,
        backendSkinId: skin.id,
        rarity: mapRarityColor(skin.rarity_color),
        weapon,
        name,
        price: formatKopecks(skin.price),
        image: skin.image_url || '',
    };
}

export function inventoryItemToEntry(item: {
    id: number;
    skin: Skin;
    price_at_acquisition: number;
}): SkinEntry {
    const { weapon, name } = parseSkinName(item.skin.market_hash_name);
    return {
        id: item.id,
        backendUserSkinId: item.id,
        backendSkinId: item.skin.id,
        rarity: mapRarityColor(item.skin.rarity_color),
        weapon,
        name,
        price: formatKopecks(item.price_at_acquisition),
        image: item.skin.image_url || '',
    };
}
```

- [ ] **Step 2: Commit**
```bash
git add resources/js/utils/
git commit -m "feat: add skin rarity mapping and price formatting helpers"
```

---

### Task 7: Cleanup — Remove Mock Data

**Files:**
- Delete: `resources/js/mock/inventory.ts`
- Delete: `resources/js/mock/liveFeed.ts`
- Modify: `resources/js/Components/Upgrade/UpgradeBlock.tsx` — remove MOCK_INVENTORY imports
- Modify: `resources/js/Components/Upgrade/LiveFeed.tsx` — keep mock for now (live feed is WebSocket-dependent)

- [ ] **Step 1: Remove mock inventory imports and references**

Replace all `MOCK_INVENTORY` references in UpgradeBlock with real data from props.

- [ ] **Step 2: Keep LiveFeed mock temporarily**

LiveFeed mock (`mock/liveFeed.ts`) stays until WebSocket integration. Add TODO comment.

- [ ] **Step 3: Commit**
```bash
git add resources/js/
git commit -m "chore: remove mock inventory, use real backend data"
```

---

## Execution Order

1. **Task 6** (helpers) — no dependencies, foundational
2. **Task 5** (route fix) — allow guest access
3. **Task 1** (auth) — Steam login button
4. **Task 2** (inventory) — real user skins
5. **Task 3** (target skins) — paginated API
6. **Task 4** (POST upgrade) — real gameplay
7. **Task 7** (cleanup) — remove mocks

## Key Backend Notes

- Prices are in **kopecks** (integer). 1₽ = 100 kopecks. Frontend must divide by 100 for display.
- `UpgradeController::store()` expects `user_skin_ids[]`, `balance_amount` (int, kopecks), `target_skin_id` (from skins table).
- Steam auth is at `/auth/steam` (GET redirect) and `/auth/steam/callback` (auto-handled).
- `SkinBriefResource` returns: id, market_hash_name, image_url, price, rarity_color, exterior, category.
- User's `rarity_color` from backend is a hex string like `#EA2F2F`. Map to frontend `SkinRarity` enum via lookup table.
- `usePage().props.auth.user` gives current user or null (shared by HandleInertiaRequests middleware).
