---
name: arch-cql-deprecated
description: CQL (Contextual Query Language) is dead code in the map module, superseded by backend SOLR query building
---

# CQL Status

CQL (OGC Contextual Query Language, used for WFS/WMS filter parameters) is present in the
codebase but fully deprecated as of commit `ea3c24f29` ("Move SOLR execution to backend;
deprecate JS query builders").

**Why**: The original map module built both CQL filter strings (for GeoServer WFS vector
queries) and SOLR query strings in parallel in JS. The refactor moved all SOLR query building to
PHP (`spatial/rpc/solrSearch.php`) and left the CQL code as dead weight.

**Where CQL code still lives (but is unused)**, all confirmed present in the repo:
- `js/symb/collections.map.index.OregonFlora.js` - builds CQL filter syntax (LIKE, BETWEEN, IN,
  IS NULL, !=) but the output is never consumed
- `spatial/index.php` - legacy CQL variables (`cqlArr`, `tempcqlArr`, `cqlString`,
  `newcqlString`)
- `js/symb/spatial.module.js` - `buildCQLString()` function and `cqlArr` variable, no longer
  called

**New pipeline**: `spatial/rpc/solrSearch.php` builds SOLR query syntax only.

**How to apply**: If asked to clean up or remove dead code in the map/spatial module, CQL-related
code is safe to delete. Do not mistake CQL strings for active query logic.

## Related
[[map-solr-pipeline]], [[map-module]]
