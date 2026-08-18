---
name: ident-key-filter-flow
description: "Real entry point and RPC flow for the interactive identification key (ident/key.php, not checklists/dynamicmap.php), backed by IdentManager"
---

# Identification Key Filter Flow

**Entry point correction**: the interactive key is `ident/key.php?clid=<clid>&pid=<pid>`, which
mounts `<div id="react-identify-app">` and loads `js/react/dist/identify.js` (React bundle
`js/react/src/identify`). It is reached by browsing `ident/index.php` (lists checklists with keys
per project) and clicking through. **`checklists/dynamicmap.php` is unrelated** — that page is a
Google Maps typeahead widget hitting `checklists/rpc/taxasuggest.php`, not the identification key.

RPC backend: `ident/rpc/api.php`. Unlike the generic 4-stage description in older notes, the real
flow is:

1. Page load builds an `IdentManager`, wires up setters directly from `$_GET` params:
   `setClid`/`setDynClid`/`setTaxonFilter` (family/genus scoping)/`setRelevanceValue`/
   `setAttrsFromParams($params)` (parses `attr[]` params like `"680-2301"` as `cid-cs` pairs, plus
   a `range[]` param format for numeric/slider characteristics) /`setSearchTerm`+`setSearchName`.
2. Calls `setTaxa()` (see [[ident-settaxa-flow]] for the full query breakdown — it's one Doctrine
   query joining `Taxavernaculars`, `Taxstatus`, per-characteristic `Kmdescr` joins for each
   selected `{cid: [states]}` filter, and a checklist-membership join), then `getTaxa()`.
3. Separately calls `getCharacteristics()` (`classes/IdentManager.php:392+`) to fetch the
   `Kmdescr`/`Kmcharacters`/`Kmcs` data needed to render the filter sidebar for whichever taxa
   remain.
4. There is no distinct "remainingTaxa count vs nextCharacteristics" two-field response format
   confirmed in code — both `taxa` and `characteristics` come back from the same request, and the
   React `identify` bundle derives remaining-taxon count client-side from the `taxa` array length.

**Vendor/nursery filtering**: if a selected characteristic's `cid` equals `getNurseryCid()`
(`classes/Functional.php`, returns `209`), `setTaxa()` treats it specially — instead of an inner
join on `Kmdescr`, it collects the selected states as vendor checklist IDs and adds a second
`Fmchklsttaxalink` join with an OR condition (binomial directly in the vendor checklist, or its
parent binomial in the Natives checklist while the trinomial is in the vendor checklist). See
[[ident-settaxa-flow]] for detail.

When a single taxon remains, the frontend links to the taxon profile
(`taxa/index.php?taxon=<tid>` — see [[php-flow-taxon-lookup]] for that flow's actual param name).

Related: [[ident-settaxa-flow]], [[ident-key-editing]], [[php-manager-classes]],
[[db-identkey-tables]], [[react-identify]].
