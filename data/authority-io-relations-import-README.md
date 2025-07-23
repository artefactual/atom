# Authority to Information Object Relation Import

This feature allows you to import name access point relationships between existing authority records (actors) and information objects using a CSV file.

## Usage

```bash
php symfony csv:authority-info-object-relation-import /path/to/relations.csv
```

### Options

- `--index`: Update the search index during import
- `--update=match-and-update`: Skip if relation already exists
- `--update=delete-and-replace`: Delete existing relations of the same type and replace with new ones

## CSV Format

The CSV file should have the following columns:

| Column | Required | Description |
|--------|----------|-------------|
| authorityAuthorizedFormOfName | Yes | The authorized form of name of the authority record |
| informationObjectIdentifier | Yes | The identifier or slug of the information object |
| relationType | Yes* | The type of relationship from the Event Type taxonomy |
| relationTypeId | Yes* | Direct relation type ID (overrides relationType) |
| culture | No | Culture code for multilingual lookups (defaults to en) |

*Either relationType or relationTypeId must be specified.

### Relation Types

The `relationType` column should contain the name of a term from AtoM's Event Type taxonomy. Examples:

- `Member` - Member relationship (custom type)
- `Creator` - Creator relationship
- `Contributor` - Contributor relationship
- `Publisher` - Publisher relationship
- `Subject` - Subject relationship
- `Editor` - Editor relationship
- Any other term from the Event Type taxonomy

If the specified relation type doesn't exist in the Event Type taxonomy, it will be automatically created.

**Note**: This creates direct name access point relationships (QubitRelation objects) between authorities and information objects, using the Event Type terms as relation types. This is different from creating events - it simply links the authority to the information object with a typed relationship.

### Example CSV

```csv
authorityAuthorizedFormOfName,informationObjectIdentifier,relationType,relationTypeId,culture
"Smith, John","DOC-2024-001","Member",,en
"Johnson, Mary","DOC-2024-002","Creator",,en
"Williams, Robert","collection-slug","Contributor",,en
```

## Notes

1. Both the authority record and information object must already exist in AtoM
2. The information object can be identified by either its identifier field or its slug
3. Duplicate relations are prevented automatically
4. When using update modes, be careful as they affect all relations of the specified type between the objects
