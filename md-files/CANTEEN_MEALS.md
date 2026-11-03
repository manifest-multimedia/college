# Meal redemption and offline serving

PNMTC uses configurable Africa/Accra windows: Breakfast 07:00–07:45, Lunch 13:00–13:45, Supper 17:00–17:45. Opening is inclusive; closing is the cutoff. Institutions configure their own windows in `/canteen/meals/settings`.

## Setup

1. Install the meal-redemption Android APK on each serving phone and sign in online with College staff credentials.
2. Open Your workspace / Meal service and register each scanner. Match the six-character scanner codes in College.
3. In College's Canteen meal windows module, save the schedule, name each scanner's serving lane, and assign every class to one scanner. Current qualification-list class labels are used where available; other active students appear under their College class.
4. Update all attendants' apps before enabling service. Enable the schedule, then prepare each assigned scanner online before serving.
5. A qualified scan or manual submission records the current meal immediately. There is no additional redemption confirmation. A red Already redeemed result must not receive another meal. A Use assigned lane result directs the student to their designated scanner.

System / Super Admin / Administrator users, or staff with `canteen.manage meals`, can configure meal service and view `/canteen/meals/activity`. Other staff cannot change routing or meal times.

## Duplicate and clock controls

The database uniquely identifies a meal by institution, student index, serving date and meal. Requests carry durable UUIDs and repeated network submissions return the original receipt. Registered scanners authenticate using a private device credential stored in Android secure storage; the server retains only its hash.

The first prepared scanner freezes the day's routes and schedule. Editing assignments or times applies to the next prepared day, so disconnected scanners cannot disagree about today's ownership. Staff may change shifts on the same phone; that phone retains its meal slots and original attendant attribution. A later authorized shift can upload the device's prior queued captures.

Cached authorization is bounded by the day's final meal and the staff session expiry. Android elapsed time and boot count anchor the saved server time. Changing the wall clock cannot extend serving windows. App restarts retain the reference; a phone reboot requires online preparation again. Fresh sign-in and first preparation require internet.

## Offline and sync

The roster, consumed meal slots and outbox are stored in SQLCipher-encrypted SQLite; its private key remains in Android secure storage. The app checks that SQLCipher is present before writing student data. Browser builds are previews and do not support offline serving.

Every attempt is persisted before contacting College. Only a transport failure may use the cached roster; an HTTP denial or server error never becomes an offline approval. Offline serving is limited to the scanner's own frozen classes. The device commits the meal slot before displaying success and preserves it across app restarts and sign-out.

When connectivity returns while the app is active, captures upload automatically; opening the app or pressing Sync also retries. A supervisor-review item remains visible if a capture cannot be accepted. One bad item does not prevent valid queued captures syncing. Unanswered checks that never issued a meal are reconciled without creating a new redemption.

Clearance can change while a phone is offline. Sync records the original authorized offline issuance and flags the current clearance difference. If a lost online response originally denied clearance but the valid cached authorization was used offline, the server preserves that original response while reconciling the actual offline issuance. Do not issue another meal to resolve a sync warning.

The College attendance page shows confirmed meal totals, original versus reconciled outcomes, and rejected uploads. Offline activity reaches College only after sync; the phone's pending count remains the operational indicator meanwhile. Do not clear app data or reinstall a scanner while uploads are pending. A replacement device cannot take over an already-frozen lane that day.

## Deployment

Migration: `2026_11_03_000002_create_canteen_meal_redemption_tables.php`. New institutions start disabled. Existing `/verify` and clearance-only sync are rejected with HTTP 426 once meal service is enabled, preventing older apps from treating qualification as a meal receipt.

API: authenticated `/api/v1/canteen/meals/register-device`, `/prepare`, `/redeem`, `/sync`. Device registration, server-side grants, daily plans and immutable meal keys are separate from the qualification-list evidence and Finance records.

Validation: College meal, device, settings, clearance, session, authentication-provider and import regression suites; mobile SQLite persistence, offline protocol, duplicate, clock-cutoff, shift and sync tests. Physical-device acceptance must also exercise scanning, airplane-mode serving, restart persistence and synchronization before a full serving rollout.
