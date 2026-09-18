---
name: prd-overview
description: "Product Requirements Document for OregonFlora written for non-technical stakeholders: what it is, who it serves, features, user journeys, and success measures"
---

# OregonFlora Product Requirements Document (PRD)

**Audience:** non-technical stakeholders (program leadership, funders, partners, content contributors).
**Status:** living description of the live site at https://oregonflora.org/ as observed September 2026.
**Technical companion docs:** [[arch-overview]], [[react-frontend]], [[map-module]], [[ident-key-filter-flow]].

## 1. What OregonFlora is

OregonFlora is a free public website and comprehensive guide to the vascular plants of Oregon: ferns, conifers, grasses, herbs, and trees that grow in the wild, both native and introduced. It covers roughly 4,700 plants in the state.

The site is run from the OSU Herbarium at Oregon State University (Department of Botany and Plant Pathology, Corvallis) and is funded through grants and donations.

OregonFlora is built on Symbiota, a collaborative open-source system used by many biodiversity portals to curate specimen and observation data. Symbiota itself (https://symbiota.org/) is a 20-year-old platform maintained by the Symbiota Support Hub: more than 60 portals, about 2,000 collections, over 90 million occurrence records, and over 40 million images. OregonFlora is one specialized portal in that family, focused only on Oregon vascular plants, with its own home page, plant profiles, garden and rare-plant tools, and identification aids layered on top of the shared Symbiota foundation.

## 2. Who it serves

- **Plant lovers of all ages:** anyone curious about a plant they saw.
- **Gardeners and landscapers:** people choosing native plants for Oregon yards.
- **Land managers and restorationists:** staff planning surveys, conservation, and habitat work.
- **Scientists, students, and educators:** users of herbarium specimens, maps, and the Flora of Oregon publications.
- **Nurseries and vendors:** native-plant sellers listed through the program.
- **Contributors and volunteers:** donors, photographers, and community members.

## 3. Product goals

1. Put authoritative information on every Oregon vascular plant in one place: descriptions, photos, maps, names, and lookalikes.
2. Help a non-expert go from "I saw a plant" to a confident identification.
3. Show where each plant grows, using specimen evidence and interactive maps.
4. Promote native-plant gardening and rare-plant conservation with dedicated tools.
5. Share the OSU Herbarium collections (plants, mosses, lichens, algae, fungi) with the public.
6. Sustain the program through clear ways to learn, contribute, donate, and volunteer.

## 4. What the site offers (by user need)

### Find a plant by name: plant profile pages
The home page search box ("Type a plant name here") leads to a profile for each plant. A profile gathers descriptions, photos, distribution maps, scientific and common names, synonyms, similar species, and links to outside references (Consortium of Pacific Northwest Herbaria, Flora of North America, iNaturalist, USDA PLANTS, and others). Genus and family pages help users browse upward from a single species. There are three profile flavors: standard wild-plant profiles, garden profiles, and rare-plant profiles with conservation detail.

### Identify an unknown plant: Identify Plants tool
A step-by-step tool for the question "what did I see?" The user marks where they saw the plant on a map, optionally limits the search to a plant family, and then narrows a candidate list by checking off recognizable features (flower color, size, habitat, and similar traits). Opening candidate profiles side by side lets the user compare. Verified live at `/checklists/dynamicmap.php?interface=key`.

### See where plants grow: Mapping
An interactive map answers "what grows here, and where does this plant grow?" Users draw a shape or enter plant names to see distributions built from specimen and observation records. County, ecoregion, and land-ownership overlays add context. Records with sensitive locations (rare species) are hidden from users without permission rather than blurred. Verified live at `/collections/map/index.php`.

### Browse plants of a place: Inventories
Thousands of ready-made plant lists for defined places and projects (parks, natural areas, counties, research sites). Users pick a project and browse or filter its checklist. Verified live at `/projects/index.php`.

### Garden with natives: Grow Natives
Guidance on almost 200 native species suited to gardens and landscapes: sun and water needs, mature size, flower characteristics, and which nurseries carry them. Preset plant combinations ("canned searches") and a carousel help beginners start. Verified live at `/garden/index.php`.

### Protect imperiled plants: Rare Plant Guide
A conservation tool for Oregon's rare species: search by habitat, ecoregion, flowering time, elevation, survey timing, and threats. Detailed rare profiles carry conservation ranks, ecoregion and county occurrence, and management notes. Access to the most sensitive location data requires approval through a request form. Verified live at `/rare/index.php`.

### Explore herbarium specimens: OSU Herbarium collections
Searchable specimen records behind the maps and profiles, extending beyond vascular plants to mosses, lichens, algae, and fungi. Verified live at `/collections/search/index.php`.

### Learn, follow, and contribute
Tutorials (text and video), news and events, newsletters archive, the Flora of Oregon book project pages, taxonomic checklist, rare-plant factsheets, partner listings, store, volunteer sign-up, contact, and donate pages. Login supports personal profiles and, for authorized editors, content management.

## 5. Typical user journeys

1. **Casual identification:** Home search or Identify Plants tool -> candidate list -> profile comparison -> map check -> done.
2. **Garden planning:** Grow Natives -> filter by sun, moisture, size, flower color -> profile -> find a nursery vendor.
3. **Conservation survey:** Rare Plant Guide -> filter by habitat, ecoregion, flowering time -> rare profile -> check distribution and survey guidance -> request sensitive-data access if authorized.
4. **Place-based exploration:** Inventories -> choose a project list -> browse or export the checklist.
5. **Research use:** Specimen search or map -> filter and download results (CSV/Word/KML formats) for analysis.

## 6. Content and data behind the site

- Taxonomic backbone: accepted names, synonyms, and common names for Oregon vascular plants.
- Descriptive content: morphology, habitat, flowering time, elevation, and lookalike notes.
- Images: field photos, living specimens, and preserved-specimen scans with fallbacks where photos are missing.
- Occurrence evidence: herbarium specimens and observations with locations, collectors, and dates.
- Conservation data: rarity ranks, ecoregion and county presence, threats, and survey guidance.
- Vendor data: nursery availability for garden plants.
- Editorial content: news, events, newsletters, tutorials, and program pages.

## 7. Scope boundaries (what this PRD does not promise)

- OregonFlora covers Oregon vascular plants in the wild. Non-vascular groups appear mainly through herbarium search, not full profiles.
- Sensitive rare-plant locations are protected. Full details require authorization; the public sees only what policy allows.
- The site presents curated data, not real-time field identification. It complements, not replaces, expert determination.
- This PRD describes user-facing behavior, not implementation. Technical architecture lives in [[arch-overview]] and the module docs.

## 8. How success is recognized

- A visitor can find any of the ~4,700 Oregon vascular plants by name and reach a complete profile.
- A beginner can complete an identification starting from only a location and a few visible features.
- Gardeners find suitable native plants and a way to buy them.
- Land managers find rare-plant survey and conservation guidance for their area.
- Map and list tools return trustworthy, specimen-backed answers with clear exports.
- The program visibly sustains itself: tutorials used, events attended, volunteers recruited, donations received.

## 9. Constraints and dependencies

- Based at the OSU Herbarium; content quality depends on curators, contributors, and partner herbaria.
- Funded by grants and contributions; long-term upkeep needs continued support.
- Runs as a Symbiota portal, so core specimen, taxonomy, and portal services are shared with the wider Symbiota network and its Support Hub.
- Public site must stay fast and readable on phones and desktops, and accessible to non-specialists.

## 10. Glossary (plain language)

- **Vascular plant:** a plant with water-conducting tissue: ferns, conifers, flowering plants, grasses, trees.
- **Herbarium:** a scientific collection of preserved plant specimens used as evidence of what grows where.
- **Specimen / occurrence record:** one documented sighting or collection of a plant at a place and time.
- **Taxon / taxonomy:** a named group of organisms (species, genus, family) and the system organizing those names.
- **Synonym:** an alternate scientific name for the same plant.
- **Checklist / inventory:** a list of plants found in a defined place or project.
- **Ecoregion:** a region with similar climate, landforms, and plant communities (Cascades, Willamette Valley, and others).
- **Symbiota:** the shared open-source software OregonFlora is built on.

## Related
[[arch-overview]], [[arch-page-entry-points]], [[react-home]], [[react-taxa]], [[react-identify]], [[react-checklist-special]], [[react-inventory]], [[react-explore]], [[map-module]], [[meta-memory-provenance]]
