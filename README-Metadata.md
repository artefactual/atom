read.me
sudo apt-get install -y libimage-exiftool-perl

/usr/share/nginx/atom/apps/qubit/modules/object/actions
addDigitalObjectAction.class.php

/usr/share/nginx/atom/apps/qubit/modules/digitalobject/actions
editAction.class.php
uploadAction.class.php

apps/qubit/modules/informationobject/actions/
multiFileUploadAction.class.php

/usr/share/nginx/atom/lib/helper
arEmbeddedMetadataParser.class.php


arEmbeddedMetadataParser will extract contextual metadata stored in the file, including description, title, etc. Here's what it extracts:
From EXIF tags:

ImageDescription (description/caption)
Artist (creator/photographer)
Copyright (rights information)
Software (editing software used)
DateTimeOriginal (when photo was taken)
Make/Model (camera info)
All technical camera settings (ISO, aperture, shutter speed, etc.)
GPS coordinates

From IPTC tags:

Headline (title/headline)
Caption/Abstract (description)
By-line (creator/author)
Keywords (subject terms)
Copyright Notice
City, Province/State, Country (location info)
Credit, Source

From XMP tags:

dc:Title (Dublin Core title)
dc:Description (Dublin Core description)
dc:Creator (Dublin Core creator)
dc:Subject (keywords/subjects)
dc:Rights (rights/copyright)
xmp:CreateDate, ModifyDate
xmp:CreatorTool (software used)

How It Works:
arEmbeddedMetadataParser::normalize() function:
php$n["title"] = self::firstNonEmpty([
    self::collapseLangAlt($m["XMP-dc:Title"] ?? null),  // Checks XMP first
    $m["Title"] ?? null,                                 // Then IPTC
]);

$n["description"] = self::firstNonEmpty([
    self::collapseLangAlt($m["XMP-dc:Description"] ?? null),  // XMP first
    $m["ImageDescription"] ?? null,                           // Then EXIF
    $m["Description"] ?? null,                                // Then IPTC
]);
The helper uses exiftool with the -j flag, which extracts ALL embedded metadata from the file in one pass. It then normalizes the most important fields into the _norm array for easy access.
In the code:
When the code call:
php$meta = arEmbeddedMetadataParser::extract($filePath);
$norm = $meta['_norm'] ?? [];

You get normalized access to:
$norm['title'] - from XMP, IPTC, or EXIF
$norm['description'] - from XMP, IPTC, or EXIF
$norm['creator'] - from XMP, IPTC, or EXIF
$norm['rights'] - from XMP, IPTC, or EXIF
$norm['createDate'] - from EXIF DateTimeOriginal

Plus access to raw metadata like $meta['Subject'], $meta['Make'], $meta['GPSLatitude'], etc.