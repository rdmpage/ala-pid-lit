# ALA, PIDS, and the taxonomic literature

Exploratory project between the [Atlas of Living Australia (ALA](https://www.ala.org.au), Nicole Kearney from [Biodiversity Heritage Library Australia (BHL)](https://www.biodiversitylibrary.org/collection/bhlau) and Rod Page to add persistent identifiers (PIDs) to the taxonomic literature in ALA.

## Goals

Much of the taxonomic literature is cited in ALA as “dumb strings”, rather than citations with persistent identifiers such as a DOIs. Hence ALA’s users have no obvious way to discover that taxonomic literature, which is often a rich source of information about the ecology, morphology, and behaviour of a species. The lack of links to the literature also means that taxonomists who contribute primary data that is aggregated by ALA receive no benefit, either in terms of increased readership of their work, or in formal citations of that work.

To help address this we want to do two things:

1. Add existing DOIs to any taxonomically relevant publication included in ALA.
2. For any taxonomically relevant publication listed in ALA that is in BHL, add a link to the corresponding page in BHL.

(1) adds work-level persistent identifiers (e.g., a DOI for an article). (2) adds page-level identifiers (e.g., a URL for a page in BHL).

DOIs are the standard persistent identifier for publications. Including DOIs makes the literature discoverable, as well as offering other benefits such as tools for standardised citation formatting (see [CrossRef’s Citation Formatting Service](https://www.crossref.org/labs/citation-formatting-service/)).

BHL is currently minting DOIs for much of the Australian taxonomic literature and so will provide many of the DOIs for (1) above. But BHL also has page-level identifiers, so it is possible to link directly to the relevant page in a given article.

## Preliminary experiment

The data for this preliminary dataset comes form several sources:

- A small subset of a mapping between animal names from the  [Australian Faunal Directory](https://biodiversity.org.au/afd/home) and DOIs, made as part of the [Ozymandias project](https://doi.org/10.7717/peerj.6739)

- A small subset of names from [Australian Plant Name Index (APNI)](https://biodiversity.org.au/nsl/services/search/names) and DOIs, derived from a mapping between IPNI and DOIs, see [Liberating links between datasets using lightweight data publishing: an example using plant names and the taxonomic literature](https://doi.org/10.3897/BDJ.6.e27539)

- A list of taxa used by BHL AU to highlight the importance of PIDS for biodiversity literature.

- A subset of a mapping between short citations (“micro citations”, equivalent to a “pinpoint citation” or “pincite” in legal documents) and a page in BHL for the journal *Muelleria*.

The data is in the form of a CSV spreadsheet. For each name there are three possible identifiers, not al of which are used. These identifiers come from the source databases. The [National Species List](https://biodiversity.org.au/nsl/services/) which houses APNI has distinct identifiers for a taxonomic name, the appearance of that name in a publication (an “instance”), and the name for the currently accepted taxon the name refers too. In the context of this project, an instance can be thought of as the identifier for the (name, DOI) tuple. The AFD data has identifiers for the name and the corresponding taxon. The taxon identifiers regularly change, the name identifier seems stable but not resolvable.

### Notes on identifiers

The relationship between the numerous different identifiers for names and taxa and ALA is, at best, complicated. See also [Taxonomic concepts for dummies](https://iphylo.blogspot.com/2020/07/taxonomic-concepts-for-dummies.html) and [Taxonomic concepts continued: All change at ALA and AFD](https://iphylo.blogspot.com/2020/08/taxonomic-concepts-continued-all-change.html).

Here are some notes.

| Identifier | Notes | ALA? |
| -- |-- | -- |
| https://id.biodiversity.org.au/name/apni/171465 | A name string | no |
| https://id.biodiversity.org.au/instance/apni/571483 | Identifies a (name, work) tuple in NSL. | no |
| https://id.biodiversity.org.au/node/apni/2917489 | Taxon | https://bie.ala.org.au/ + identifier | 
| NAME_GUID | Internal UUID-style identifier for a name (or possibly a name + work tuple. Not externally visible but present in CSV dumps | no |
| TAXON_GUID | UUID-style identifier for a taxon, which is a collection of one or more NAME_GUIDs. Note that TAXON_GUID can change over time, and hence the TAXON_GUID in AFD might not match that in ALA. | https://biodiversity.org.au/afd/taxa/ + identifier  



## Data format 

The test data has the following column headings.

| Key | Value |
| -- |-- |
| nameId | PID for name |
| instanceId | PID for instance (name + work) |
| taxonId | PID for taxon |
| scientificName | taxonomic name |
| rank | rank of the taxonomic name |
| doi | DOI for work (e.g., article) |
| bhl | If work is in BHL then this field has the PageID of the first page in that work where the taxonomic name appears  |
| citation | Formatted citation of work |


## Data

The data has been assembled based on a series of queries across various local databases, with some manual additions. The citations have been formatted using content negotiation via CrossRef’s formatting service. This is simply a call to `https://doi.org/` with the header `Accept: text/bibliography; style=apa`. Other formats are available. A better approach would be to get the bibliographic data in CSL-JSON (also available via CrossRef) and use CiteProc to format the data in any desired format (see [Citation.js](https://citation.js.org) for an implementation).

Data is available in both [TSV](ala.tsv) and [CSV](ala.csv) formats.

### Queries

These are notes on the queries used to generate the data, they won’t mean much as the databases are all local on my machine.

#### Sample from AFD mapped to Ozymandias

```sql
SELECT afd.NAME_GUID AS nameId, "", afd.TAXON_GUID AS taxonId, afd.SCIENTIFIC_NAME AS scientificName, afd.RANK AS rank, bibliography.doi, "", bibliography.PUB_FORMATTED AS citation 
FROM afd
INNER JOIN bibliography USING(PUBLICATION_GUID)
WHERE bibliography.doi IS NOT NULL
AND bibliography.PUB_YEAR LIKE "201%"
LIMIT 10;
```

#### Nicole’s favourites from AFD

```sql
SELECT afd.NAME_GUID AS nameId, "", afd.TAXON_GUID AS taxonId, afd.SCIENTIFIC_NAME AS scientificName, afd.RANK AS rank, bibliography.doi, "", bibliography.PUB_FORMATTED AS citation 
FROM afd
INNER JOIN bibliography USING(PUBLICATION_GUID)
WHERE afd.SCIENTIFIC_NAME IN ("Platypus anatinus Shaw, 1799", "Gymnobelideus leadbeateri McCoy, 1867");
```

#### Sample from NSL mapped to IPNI

```sql
SELECT nsl.name_id AS nameId, nsl.instance_id AS instanceId, "", nsl.name_text AS scientificName, names.Rank AS rank, names.doi, "", nsl.reference_text AS citation FROM nsl
INNER JOIN names ON nsl.name_text = names.Full_name_without_family_and_authors
WHERE names.doi IS NOT NULL
AND nsl.instanceType = "tax. nov."
AND names.Publication_year_full LIKE "20%"
LIMIT 10;
```






 





