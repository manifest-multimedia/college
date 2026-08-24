# Public identifier rollout

## Rules

- Keep numeric primary keys and foreign keys internal.
- Use immutable ULIDs in browser URLs, public/API payloads and external integrations.
- Retain a short-lived legacy numeric URL redirect while each module is adopted.
- Add identifiers as nullable, backfill in bounded batches, verify, then enforce a
  non-null database constraint in a later release.

## Rollout checklist

- [x] Define the identifier standard: public_id ULID, unique per table.
- [x] Phase 1: support tickets use public IDs in new links; old numeric links
  redirect to the canonical public URL.
- [x] Phase 2: public election routes use immutable public IDs. Election voting
  sessions already use UUID session IDs; legacy numeric election links remain
  accepted during adoption.
- [ ] Phase 3: student fee bills/items and payment-facing API contracts. This
  must be coordinated with the existing public_reference migration.
- [ ] Phase 4: exam clearances, offline exams, entry tickets, chat sessions and
  memos.
- [ ] Phase 5: authenticated operational resources: students, question sets,
  questions, assets, notifications and related management APIs.
- [ ] Phase 6: remove numeric URL compatibility after each institution has
  completed the adoption window.

## Per-phase production checklist

- [ ] Add nullable public_id and a unique index.
- [ ] Backfill existing records in small batches and verify no null/duplicate
  values remain.
- [ ] Generate IDs for all new records.
- [ ] Update routes, links, API resources and authorization lookups.
- [ ] Deploy with legacy numeric URL compatibility and monitor 404s.
- [ ] Enforce non-null after verification; schedule legacy-route removal.
