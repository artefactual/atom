AIP Detail
==========

The AIP detail REST API endpoint provides details about a specific AIP.

.. http:get:: /api/aips/<uuid>

   Summary of API data.

   **Example request**:

   .. sourcecode:: http

      GET /api/aips/109a8155-9e0e-409c-bb60-b81aca351663 HTTP/1.1
      Host: example.com
      Accept: application/json, text/javascript

   **Example response (METS data removed for the sake of brevity)**:

   .. sourcecode:: http

      HTTP/1.1 200 OK
      Content-Type: text/javascript

    {
        "id": "639",
        "name": "Cat_Pictures",
        "uuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
        "size": "22033310",
        "type": {
            "id": 0,
            "name": null
        },
        "part_of": {
            "id": 622,
            "title": "500",
            "level_of_description_id": 375
        },
        "digital_object_count": "7",
        "digitalObjects": [{
            "id": "640",
            "slug": "0239-mpg-3",
            "identifier": null,
            "inheritReferenceCode": null,
            "levelOfDescriptionId": "386",
            "publicationStatusId": "160",
            "ancestors": ["1", "622", "623", "638"],
            "collectionRootId": 622,
            "parentId": "638",
            "digitalObject": {
                "mediaTypeId": "138",
                "usageId": null,
                "mimeType": "video\/mpeg",
                "byteSize": "4118728",
                "checksum": "b794e283d7f6552717a7311f13bdbedc84945caca7b0203b212c71542a1beb1b",
                "thumbnailPath": null,
                "masterPath": "\/uploads\/r\/null\/b\/7\/b794e283d7f6552717a7311f13bdbedc84945caca7b0203b212c71542a1beb1b\/03bb832b-c03b-45f9-82d3-d4f837ad59f4-0239.mpg"
            },
            "hasDigitalObject": true,
            "transcript": false,
            "aipUuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aipName": "Cat_Pictures",
            "aipPartOf": "500",
            "aipAttachedTo": "Components",
            "createdAt": "2014-06-23T23:44:45Z",
            "updatedAt": "2014-06-23T23:44:45Z",
            "sourceCulture": "en",
            "i18n": {
                "en": {
                    "title": "0239.mpg"
                },
                "languages": ["en"]
            }
        }, {
            "id": "643",
            "slug": "bigteen-short1-mp3-3",
            "identifier": null,
            "inheritReferenceCode": null,
            "levelOfDescriptionId": "386",
            "publicationStatusId": "160",
            "ancestors": ["1", "622", "623", "638"],
            "collectionRootId": 622,
            "parentId": "638",
            "digitalObject": {
                "mediaTypeId": "135",
                "usageId": null,
                "mimeType": "audio\/mpeg",
                "byteSize": "971275",
                "checksum": "5dc2e020455fdc154c02cb775fbd731ff58c776cf0034f268f7ee3dd7e15f5f2",
                "thumbnailPath": null,
                "masterPath": "\/uploads\/r\/null\/5\/d\/5dc2e020455fdc154c02cb775fbd731ff58c776cf0034f268f7ee3dd7e15f5f2\/69f1cbcc-dc90-437b-8640-70745ed5dea6-BigTeen_Short1.mp3"
            },
            "hasDigitalObject": true,
            "transcript": false,
            "aipUuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aipName": "Cat_Pictures",
            "aipPartOf": "500",
            "aipAttachedTo": "Components",
            "createdAt": "2014-06-23T23:44:48Z",
            "updatedAt": "2014-06-23T23:44:48Z",
            "sourceCulture": "en",
            "i18n": {
                "en": {
                    "title": "BigTeen_Short1.mp3"
                },
                "languages": ["en"]
            }
        }, {
            "id": "646",
            "slug": "blastoff-wmv-3",
            "identifier": null,
            "inheritReferenceCode": null,
            "levelOfDescriptionId": "386",
            "publicationStatusId": "160",
            "ancestors": ["1", "622", "623", "638"],
            "collectionRootId": 622,
            "parentId": "638",
            "hasDigitalObject": false,
            "aipUuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aipName": "Cat_Pictures",
            "aipPartOf": "500",
            "aipAttachedTo": "Components",
            "createdAt": "2014-06-23T23:44:49Z",
            "updatedAt": "2014-06-23T23:44:49Z",
            "sourceCulture": "en",
            "i18n": {
                "en": {
                    "title": "BlastOff.wmv"
                },
                "languages": ["en"]
            }
        }, {
            "id": "647",
            "slug": "funky-breakbeat-4-wav-3",
            "identifier": null,
            "inheritReferenceCode": null,
            "levelOfDescriptionId": "386",
            "publicationStatusId": "160",
            "ancestors": ["1", "622", "623", "638"],
            "collectionRootId": 622,
            "parentId": "638",
            "hasDigitalObject": false,
            "aipUuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aipName": "Cat_Pictures",
            "aipPartOf": "500",
            "aipAttachedTo": "Components",
            "createdAt": "2014-06-23T23:44:49Z",
            "updatedAt": "2014-06-23T23:44:49Z",
            "sourceCulture": "en",
            "i18n": {
                "en": {
                    "title": "funky_breakbeat_4.wav"
                },
                "languages": ["en"]
            }
        }, {
            "id": "648",
            "slug": "j6059-02-wma-3",
            "identifier": null,
            "inheritReferenceCode": null,
            "levelOfDescriptionId": "386",
            "publicationStatusId": "160",
            "ancestors": ["1", "622", "623", "638"],
            "collectionRootId": 622,
            "parentId": "638",
            "hasDigitalObject": false,
            "aipUuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aipName": "Cat_Pictures",
            "aipPartOf": "500",
            "aipAttachedTo": "Components",
            "createdAt": "2014-06-23T23:44:49Z",
            "updatedAt": "2014-06-23T23:44:49Z",
            "sourceCulture": "en",
            "i18n": {
                "en": {
                    "title": "j6059_02.wma"
                },
                "languages": ["en"]
            }
        }, {
            "id": "649",
            "slug": "makeup-mov-3",
            "identifier": null,
            "inheritReferenceCode": null,
            "levelOfDescriptionId": "386",
            "publicationStatusId": "160",
            "ancestors": ["1", "622", "623", "638"],
            "collectionRootId": 622,
            "parentId": "638",
            "hasDigitalObject": false,
            "aipUuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aipName": "Cat_Pictures",
            "aipPartOf": "500",
            "aipAttachedTo": "Components",
            "createdAt": "2014-06-23T23:44:49Z",
            "updatedAt": "2014-06-23T23:44:49Z",
            "sourceCulture": "en",
            "i18n": {
                "en": {
                    "title": "MakeUp.mov"
                },
                "languages": ["en"]
            }
        }, {
            "id": "650",
            "slug": "sample-aif-3",
            "identifier": null,
            "inheritReferenceCode": null,
            "levelOfDescriptionId": "386",
            "publicationStatusId": "160",
            "ancestors": ["1", "622", "623", "638"],
            "collectionRootId": 622,
            "parentId": "638",
            "hasDigitalObject": false,
            "aipUuid": "d9e5f49c-6d2a-4d01-8e6e-c1d602ff9229",
            "aipName": "Cat_Pictures",
            "aipPartOf": "500",
            "aipAttachedTo": "Components",
            "createdAt": "2014-06-23T23:44:50Z",
            "updatedAt": "2014-06-23T23:44:50Z",
            "sourceCulture": "en",
            "i18n": {
                "en": {
                   "title": "sample.aif"
                },
                "languages": ["en"]
            }
        }],
        "created_at": "2014-06-24T06:44:35Z"
    }

   :statuscode 200: no error
