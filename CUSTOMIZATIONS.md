# Customizations

The Winnipeg AtoM repo is based on stable/2.3.x (6 commits past 2.3.0 release).

Additionally, a custom theme has been added and CSV import/transformation
functionality has been backported from the qa/2.4.x branch to faciliate
migration of data.

## Custom theme

The theme was added by commit 08ad4b8537e02e00e70e32e0b7ac16cd408e77a2.

## Backported CSV import functionality

CSV import functionality was backported to:

* Import to existing parent IDs: a31429660263a370448f28dc1b2ac34380af8be9
* Use DB (not ElasticSearch) when checking description existence: c1e41fd99d001eecf46bc5a72910a722b75f83f1
* Add getByTitleIdentifierAndRepo method: 504489391c898115618e5874a617ad1091b1ce94
* Minor CSV import changes/improvements: 277b54bc834ac2533a240d80e165f978a8507017
                                         c95042cef4d7c4efd4ee4afd8f3bb578675e1d9a

## Backported CSV transformation functionality

CSV transformation functionality was backported to:

* Allow non-calculated parent keys: 79906e5b8a2e6bed8ed8f79c14cc2bdba4352fdb
* Ignore specific CSV rows in: 8b9b8d18744e69ca6366548a33662fefaed8280f
* Conditonally ignore CSV rows: ab63c3928425a993923f158ad013a09a518aaf9c
* Fully confi DB connection used during CSV transform: ab7426d9118267fc68d36492b04cc709c4e3683d
* Fix MySQL query issues in transform logic: ab7426d9118267fc68d36492b04cc709c4e3683d
                                             a321adee8b304095c08dfe4c5cae1fafe3793c6b

## Miscelaneous functionality

This functionality was backported to make re-indexing data during import testing easier:

* ElasticSearch indexing tweak from qa/2.4.x: 04ec3cf4098a89d92fefc86079df5299a006cabe
