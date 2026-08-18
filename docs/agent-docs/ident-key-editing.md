---
name: ident-key-editing
description: How character states (kmcs) for the interactive ID key are edited in the UI, and which admin classes/pages back each edit
---

# Editing Identification-Key Character States (`kmcs`)

The `Kmcs` model (`models/Kmcs.php`) maps the `kmcs` table: each row is one character state (e.g.
character "Leaf shape" -> state "lanceolate"). On a species taxon page these surface as
traits/attributes via `OSUTaxaManager::getAllAttribs()` (`classes/OSUTaxaManager.php:937+`), which
joins `kmdescr` (taxon-to-state links) to `kmcs`.

Two distinct edits, two different admin UIs:

## 1. Edit a state's own fields (name, description, notes, illustration)
- Backed by `classes/KeyCharAdmin.php`.
- URL: `ident/admin/chardetails.php?cid=<characterID>`, reached from `ident/admin/index.php`.
- Add/rename states, edit description/notes, set sortsequence, attach illustration images
  (`kmcsimages`). INSERT/UPDATE confirmed in `classes/KeyCharAdmin.php` (`addCharState()` /
  `editCharState()` methods, INSERT around line 209, UPDATE around line 227).

## 2. Change which states a given species has (edits `kmdescr`, not `kmcs`)
- **Per-taxon editor**: `ident/tools/editor.php?tid=<taxonID>` (`classes/KeyEditorManager.php`,
  method `processTaxa()`).
- **Matrix editor**: `ident/tools/matrixeditor.php?clid=<checklistID>&cid=<characterID>`
  (`classes/KeyMatrixEditor.php`, method `processAttributes()`) — grid view to assign one
  character's states across many taxa at once.

## Access requirement
Both editor pages require `$SYMB_UID` (logged in) and gate editing behind
`$IS_ADMIN || KeyEditor || KeyAdmin` user rights (confirmed at `ident/tools/matrixeditor.php:29`).

## Sitemap visibility
The Identification Keys section in `sitemap.php` is shown only when `$KEY_MOD_IS_ACTIVE` is enabled or the user has the explicit `KeyAdmin` role (`sitemap.php:215`). It does not include `$IS_ADMIN`, so a `SuperAdmin` can use the editor endpoints but may not see the Identification Keys section or its hyperlinks in the sitemap. Assign `KeyAdmin`, or change the condition to include `$IS_ADMIN`.

The per-taxon editor is not directly linked from the sitemap. It is reached from a matrix editor taxon edit icon or directly at `ident/tools/editor.php?tid=<taxonID>`. To focus on the federal conservation character, append `&char=242`.

## editor.php (per-taxon editor) is not reachable from sitemap.php
`sitemap.php` only links to `matrixeditor.php` (via a per-checklist loop), never to
`ident/tools/editor.php`. The only in-app link to `editor.php?tid=<taxonID>` is from
the matrix editor taxon edit icon (`classes/KeyMatrixEditor.php:181-184`). The
`chardeficit.php` report also links to it. The sitemap itself does not link directly
to `editor.php`, so it can also be reached by constructing the URL directly.

## Reaching chardetails.php from sitemap.php
`sitemap.php` has no direct link to `chardetails.php`. Path: the "Manage Character/States" link
(`$LANG['AUTHOKEY']`, gated on `$IS_ADMIN || KeyAdmin`, in the same conditional block as the
`$KEY_MOD_IS_ACTIVE || KeyAdmin` "Identification Keys" section around `sitemap.php:216-230`) goes
to `ident/admin/index.php`, which lists characters as links to `chardetails.php?cid=<cid>`
(`ident/admin/index.php:150,168`) and also has a "new character" form that POSTs straight to
`chardetails.php` (`ident/admin/index.php:80`).

Related: [[ident-settaxa-flow]], [[ident-key-filter-flow]], [[php-manager-classes]],
[[db-identkey-tables]].
