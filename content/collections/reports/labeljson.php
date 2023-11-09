<?php
 $LABEL_FORMAT_JSON = '{
    "labelFormats": [
        {
            "title": "Basic OSU Herbarium Label",
            "labelHeader": {
                "prefix": "Flora of ",
                "midText": "2",
                "suffix": ", U.S.A.",
                "className": "",
                "style": "font-weight: bold;"
            },
            "labelFooter": {
                "textValue": "Oregon State University",
                "className": "",
                "style": ""
            },
            "customStyles": ".associatedtaxaPrefix{font-style: normal; font-variant: small-caps;} .row{margin-bottom: 20px;} .label{margin: 0 5px;} .label-databased{text-align: right; font-size: 0.6em;}",
            "defaultCss": "..\/..\/css\/symb\/labelhelpers.css",
            "customCss": "",
            "customJS": "..\/..\/content\/collections\/reports\/general.js",
            "labelType": "2",
            "pageSize": "letter",
            "displaySpeciesAuthor": 1,
            "displayBarcode": 0,
            "labelBlocks": [
                {
                    "divBlock": {
                        "className": "label-blocks",
                        "style": "",
                        "blocks": [
                            {
                                "fieldBlock": [
                                    {
                                        "field": "family",
                                        "className": "font-family-times"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2 mt-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "speciesname",
                                        "className": "font-family-times font-bold italic text-base"
                                    },
                                    {
                                        "field": "parentauthor",
                                        "className": "font-family-times text-base"
                                    },
                                    {
                                        "field": "taxonrank",
                                        "className": "font-bold text-base font-family-times"
                                    },
                                    {
                                        "field": "infraspecificepithet",
                                        "className": "font-family-times text-base font-bold italic"
                                    },
                                    {
                                        "field": "scientificnameauthorship",
                                        "className": "font-family-times text-base"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "county",
                                        "className": "font-family-times",
                                        "suffix": " Co.,"
                                    },
                                    {
                                        "field": "stateprovince",
                                        "className": "font-family-times",
                                        "suffix": ":"
                                    },
                                    {
                                        "field": "locality",
                                        "className": "font-family-times"
                                    },
                                    {
                                        "field": "elevationinmeters",
                                        "className": "font-family-times",
                                        "prefix": "Elev. ",
                                        "suffix": "m."
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "decimallatitude",
                                        "className": "font-family-times",
                                        "suffix": "\u00b0"
                                    },
                                    {
                                        "field": "decimallongitude",
                                        "className": "font-family-times",
                                        "suffix": "\u00b0"
                                    },
                                    {
                                        "field": "coordinateuncertaintyinmeters",
                                        "className": "font-family-times",
                                        "prefix": "\u00b1",
                                        "suffix": " m."
                                    },
                                    {
                                        "field": "geodeticdatum",
                                        "prefix": "(",
                                        "suffix": ")"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "habitat",
                                        "className": "font-family-times",
                                        "prefix": "Habitat: "
                                    },
                                    {
                                        "field": "substrate"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "verbatimattributes",
                                        "className": "font-family-times"
                                    },
                                    {
                                        "field": "reproductivecondition",
                                        "className": "font-family-times"
                                    },
                                    {
                                        "field": "occurrenceremarks",
                                        "className": "font-family-times"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "associatedtaxa",
                                        "className": "font-family-times italic",
                                        "prefix": "Associated taxa: "
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "recordedby",
                                        "className": "font-family-times"
                                    },
                                    {
                                        "field": "recordnumber",
                                        "className": "font-family-times"
                                    },
                                    {
                                        "field": "eventdate",
                                        "className": "font-family-times float-right"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            }
                        ]
                    }
                }
            ]
        },
        {
            "title": "Mickley Label",
            "labelHeader": {
                "prefix": "Flora of ",
                "midText": "2",
                "suffix": "",
                "className": "font-family-times",
                "style": "text-transform: uppercase; font-weight: bold;"
            },
            "labelFooter": {
                "textValue": "OSC Herbarium \u2013 Oregon State University",
                "className": "",
                "style": "font-variant: small-caps; font-size: 0.9em; transform: scaleY(1.1); text-align: center; margin-top: 0.5em; "
            },
            "customStyles": ".field-block{clear: both;} .family{font-variant: small-caps;} .associatedtaxaPrefix{font-style: normal; font-variant: small-caps;} .text-base{font-size: 0.95em;} .text-sm{font-size: 0.8em;} .cn-barcode{text-align: center;} .label{box-shadow: inset 0 0 0 5px white, inset 0 0 0 6px black;} .row{margin-bottom: 10px;} .label{margin: 0 5px;} .label-databased{text-align: right; font-size: 0.6em;}",
            "defaultCss": "..\/..\/css\/symb\/labelhelpers.css",
            "customCss": "",
            "customJS": "..\/..\/content\/collections\/reports\/general.js",
            "labelType": "2",
            "pageSize": "letter",
            "displaySpeciesAuthor": 1,
            "displayBarcode": 0,
            "labelBlocks": [
                {
                    "divBlock": {
                        "className": "label-blocks",
                        "style": "",
                        "blocks": [
                            {
                                "fieldBlock": [
                                    {
                                        "field": "family",
                                        "className": "float-right font-family-arial text-base"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mt-2 mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "speciesname",
                                        "className": "font-bold italic font-family-arial text-base"
                                    },
                                    {
                                        "field": "parentauthor",
                                        "className": "text-base font-family-arial"
                                    },
                                    {
                                        "field": "taxonrank",
                                        "className": "font-bold text-base font-family-arial"
                                    },
                                    {
                                        "field": "infraspecificepithet",
                                        "className": "font-family-arial text-base font-bold italic"
                                    },
                                    {
                                        "field": "scientificnameauthorship",
                                        "className": "font-family-arial text-base"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-1"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identifiedby",
                                        "className": "text-sm font-family-arial",
                                        "prefix": "Det: "
                                    },
                                    {
                                        "field": "dateidentified",
                                        "className": "text-sm font-family-arial"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2 text-align-left ml-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "country",
                                        "className": "font-bold font-family-arial text-base",
                                        "suffix": ","
                                    },
                                    {
                                        "field": "stateprovince",
                                        "className": "font-bold font-family-arial text-base",
                                        "suffix": ","
                                    },
                                    {
                                        "field": "county",
                                        "className": "font-bold font-family-arial text-base",
                                        "suffix": " County,"
                                    },
                                    {
                                        "field": "locality",
                                        "className": "font-family-arial text-base"
                                    }
                                ],
                                "delimiter": " "
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "decimallatitude",
                                        "className": "font-family-arial text-base",
                                        "suffix": "\u00b0,"
                                    },
                                    {
                                        "field": "decimallongitude",
                                        "className": "font-family-arial text-base",
                                        "suffix": "\u00b0"
                                    },
                                    {
                                        "field": "coordinateuncertaintyinmeters",
                                        "className": "font-family-arial text-base",
                                        "prefix": "\u00b1",
                                        "suffix": " m"
                                    },
                                    {
                                        "field": "geodeticdatum",
                                        "className": "font-family-arial text-base",
                                        "prefix": "(",
                                        "suffix": ")."
                                    },
                                    {
                                        "field": "elevationinmeters",
                                        "className": "font-family-arial text-base",
                                        "prefix": "Elev: ",
                                        "suffix": " m."
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "habitat",
                                        "className": "font-family-arial text-sm",
                                        "suffix": "."
                                    },
                                    {
                                        "field": "substrate",
                                        "className": "font-family-arial text-sm",
                                        "suffix": "."
                                    },
                                    {
                                        "field": "verbatimattributes",
                                        "className": "font-family-arial text-sm",
                                        "suffix": "."
                                    },
                                    {
                                        "field": "occurrenceremarks",
                                        "className": "text-sm font-family-arial",
                                        "suffix": "."
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "associatedtaxa",
                                        "className": "text-sm font-family-arial italic",
                                        "prefix": "Associated Species: "
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-2"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "recordedby",
                                        "className": "font-bold font-family-arial text-base"
                                    },
                                    {
                                        "field": "recordnumber",
                                        "className": "font-family-arial font-bold text-base"
                                    },
                                    {
                                        "field": "eventdate",
                                        "className": "font-family-arial float-right font-bold text-base"
                                    }
                                ],
                                "delimiter": " "
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "associatedcollectors",
                                        "className": "font-family-arial text-sm",
                                        "prefix": "    With: "
                                    }
                                ],
                                "delimiter": " ",
                                "className": "ml-2 mb-2"
                            }
                        ]
                    }
                }
            ]
        },
        {
            "title": "FIA Label",
            "labelHeader": {
                "prefix": "",
                "midText": "0",
                "suffix": "",
                "className": "font-family-times",
                "style": ""
            },
            "labelFooter": {
                "textValue": "U.S. Forest Service, Forest Inventory and Analysis Program",
                "className": "font-family-arial",
                "style": "font-size: 0.7em; padding-top: 5px;"
            },
            "customStyles": ".field-block{clear: both;} .text-base{font-size: 0.8em;} .text-sm{font-size: 0.7em;} .label{box-shadow: inset 0 0 0 5px white, inset 0 0 0 6px black; margin: 0 5px; width: 40%;} .row{margin-bottom: 10px;} .other-catalog-numbers{display: none;} .cn-barcode{padding: 10px; float: right; width: 40px; height: 149px;} .cn-barcode img {height: 40px; border: 1px solid black; padding: 5px; transform: rotate(270deg); transform-origin: top left;}",
            "defaultCss": "../../css/v202209/symbiota/collections/reports/labelhelpers.css",
            "customCss": "",
            "customJS": "",
            "labelType": "2",
            "pageSize": "letter",
            "displaySpeciesAuthor": 1,
            "displayBarcode": 0,
            "labelBlocks": [
                {
                    "divBlock": {
                        "className": "label-blocks",
                        "style": "",
                        "blocks": [
                            {
                                "fieldBlock": [
                                    {
                                        "field": "speciesname",
                                        "className": "font-bold italic font-family-arial text-base"
                                    },
                                    {
                                        "field": "parentauthor",
                                        "className": "font-family-arial text-base"
                                    },
                                    {
                                        "field": "taxonrank",
                                        "className": "font-bold font-family-arial text-base"
                                    },
                                    {
                                        "field": "infraspecificepithet",
                                        "className": "font-family-arial font-bold italic text-base"
                                    },
                                    {
                                        "field": "scientificnameauthorship",
                                        "className": "font-family-arial text-base"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-0"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identifiedby",
                                        "className": "text-sm font-family-arial",
                                        "prefix": "Det. by "
                                    },
                                    {
                                        "field": "dateidentified",
                                        "className": "text-sm font-family-arial"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "text-align-left ml-0 mb-1"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "stateprovince",
                                        "className": "font-bold font-family-arial text-base",
                                        "prefix": "U.S.A., ",
                                        "suffix": ","
                                    },
                                    {
                                        "field": "county",
                                        "className": "font-bold font-family-arial text-base",
                                        "suffix": " County"
                                    }
                                ],
                                "delimiter": " "
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "locality",
                                        "className": "font-family-arial text-base"
                                    }
                                ],
                                "delimiter": " ",
                                "className": "ml-0 mb-1"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "decimallatitude",
                                        "className": "font-family-arial text-base",
                                        "suffix": "\u00b0 N,"
                                    },
                                    {
                                        "field": "decimallongitude",
                                        "className": "font-family-arial text-base",
                                        "suffix": "\u00b0 W, "
                                    },
                                    {
                                        "field": "elevationinmeters",
                                        "className": "font-family-arial text-base",
                                        "prefix": "Elev. ",
                                        "suffix": "m."
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-1"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "habitat",
                                        "className": "font-family-arial text-base",
                                        "suffix": "."
                                    },
                                    {
                                        "field": "substrate",
                                        "className": "font-family-arial text-base",
                                        "suffix": "."
                                    },
                                    {
                                        "field": "verbatimattributes",
                                        "className": "font-family-arial text-base",
                                        "suffix": "."
                                    },
                                    {
                                        "field": "occurrenceremarks",
                                        "className": "font-family-arial text-base",
                                        "suffix": "."
                                    }
                                ],
                                "delimiter": " ",
                                "className": "mb-1"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "recordedby",
                                        "className": "font-bold font-family-arial text-base"
                                    },
                                    {
                                        "field": "recordnumber",
                                        "className": "font-family-arial font-bold text-base"
                                    },
                                    {
                                        "field": "eventdate",
                                        "className": "font-family-arial float-right font-bold text-base"
                                    }
                                ],
                                "delimiter": " "
                            }
                        ]
                    }
                }
            ]
        },
        {
            "title":"Symbiota Generic Herbarium Label",
            "displaySpeciesAuthor":1,
            "displayBarcode":0,
            "labelType":"2",
            "customStyles":"body{ font-size:10pt; }",
            "defaultCss":"../../css/v202209/symbiota/collections/reports/labelhelpers.css",
            "customCss":"",
            "customJs":"",
            "pageSize":"letter",
            "labelHeader":{
                "prefix":"Flora of ",
                "midText":3,
                "suffix":" county",
                "className":"text-center font-bold font-sans text-2xl",
                "style":"margin-bottom:10px;"
            },
            "labelBlocks":[
                {"divBlock":{"className":"label-block","blocks":[
                    {"divBlock":{"className":"taxonomy my-2 text-lg","blocks":[
                        {"fieldBlock":[
                            {"field":"identificationqualifier"},
                            {"field":"speciesname","className":"font-bold italic"},
                            {"field":"parentauthor"},
                            {"field":"taxonrank","className":"font-bold"},
                            {"field":"infraspecificepithet","className":"font-bold italic"},
                            {"field":"scientificnameauthorship"}
                            ],"delimiter":" "
                        },
                        {"fieldBlock":[{"field":"family","styles":["float:right"]}]}
                    ]}},
                    {"fieldBlock":[{"field":"identifiedby","prefix":"Det by: "},{"field":"dateidentified"}]},
                    {"fieldBlock":[{"field":"identificationreferences"}]},
                    {"fieldBlock":[{"field":"identificationremarks"}]},
                    {"fieldBlock":[{"field":"taxonremarks"}]},
                    {"divBlock":{"className":"localDiv","className":"text-lg","style":"margin-top:10px;","blocks":[
                        {"fieldBlock":[{"field":"country","className":"font-bold"},{"field":"stateprovince","style":"font-weight:bold"},{"field":"county"},{"field":"municipality"},{"field":"locality"}],"delimiter":", "}
                    ]}},
                    {"fieldBlock":[{"field":"decimallatitude"},{"field":"decimallongitude","style":"margin-left:10px"},{"field":"coordinateuncertaintyinmeters","prefix":"+-","suffix":" meters","style":"margin-left:10px"},{"field":"geodeticdatum","prefix":"[","suffix":"]","style":"margin-left:10px"}]},
                    {"fieldBlock":[{"field":"verbatimcoordinates"}]},
                    {"fieldBlock":[{"field":"elevationinmeters","prefix":"Elev: ","suffix":"m. "},{"field":"verbatimelevation"}]},
                    {"fieldBlock":[{"field":"habitat","suffix":"."}]},
                    {"fieldBlock":[{"field":"substrate","suffix":"."}]},
                    {"fieldBlock":[{"field":"verbatimattributes"},{"field":"establishmentmeans"}],"delimiter":"; "},
                    {"fieldBlock":[{"field":"associatedtaxa","prefix":"Associated species: ","className":"italic"}]},
                    {"fieldBlock":[{"field":"occurrenceremarks"}]},
                    {"fieldBlock":[{"field":"typestatus"}]},
                    {"divBlock":{"className":"collector","style":"margin-top:10px;","blocks":[
                        {"fieldBlock":[{"field":"recordedby","style":"float:left"},{"field":"recordnumber","style":"float:left;margin-left:10px"},{"field":"eventdate","style":"float:right"}]},
                        {"fieldBlock":[{"field":"associatedcollectors","prefix":"with: "}],"style":"clear:both; margin-left:10px;"}
                    ]}}
                ]}}
            ],
            "labelFooter":{
                "textValue":"",
                "className":"text-center font-bold font-sans",
                "style":"margin-top:10px;"
            }
        },
        {
            "title":"Symbiota Generic Vertebrate Label",
            "displaySpeciesAuthor":0,
            "displayBarcode":0,
            "labelType":"3",
            "customStyles":"body{ font-size:10pt; }",
            "defaultCss":"../../css/v202209/symbiota/collections/reports/labelhelpers.css",
            "customCss":"",
            "customJs":"",
            "pageSize":"letter",
            "labelHeader":{
                "prefix":"",
                "midText":0,
                "suffix":"",
                "className": "text-center font-bold font-sans text-2xl",
                "style":"text-align:center;margin-bottom:5px;font:bold 7pt arial,sans-serif;clear:both;"
            },
            "labelFooter":{
                "textValue":"",
                "className": "text-center font-bold font-sans text-2xl",
                "style":"text-align:center;margin-top:10px;font:bold 10pt arial,sans-serif;clear:both;"
            },
            "labelBlocks":[
                {"divBlock":{"className":"labelBlockDiv","blocks":[
                    {"fieldBlock":[{"field":"family","styles":["margin-bottom:2px;font-size:pt"]}]},
                    {"divBlock":{"className":"taxonomyDiv","style":"font-size:10pt;","blocks":[
                        {"fieldBlock":[
                            {"field":"identificationqualifier"},
                            {"field":"speciesname","style":"font-weight:bold;font-style:italic"},
                            {"field":"parentauthor"},
                            {"field":"taxonrank","style":"font-weight:bold"},
                            {"field":"infraspecificepithet","style":"font-weight:bold;font-style:italic"},
                            {"field":"scientificnameauthorship"}
                            ],"delimiter":" "
                        }
                    ]}},
                    {"fieldBlock":[{"field":"identifiedby","prefix":"Det by: "},{"field":"dateidentified"}]},
                    {"fieldBlock":[{"field":"identificationreferences"}]},
                    {"fieldBlock":[{"field":"identificationremarks"}]},
                    {"fieldBlock":[{"field":"taxonremarks"}]},
                    {"fieldBlock":[{"field":"catalognumber","style":"font-weight:bold;font-size:14pt;margin:5pt 0pt;"}]},
                    {"divBlock":{"className":"localDiv","style":"margin-top:3px;padding-top:3px;border-top:3px solid black","blocks":[
                        {"fieldBlock":[{"field":"country"},{"field":"stateprovince","prefix":", "},{"field":"county","prefix":", "},{"field":"municipality","prefix":", "},{"field":"locality","prefix":": "},{"field":"decimallatitude","prefix":": ","suffix":"° N"},{"field":"decimallongitude","prefix":" ","suffix":"° W"},{"field":"coordinateuncertaintyinmeters","prefix":" +-","suffix":" meters","style":"margin-left:10px"},{"field":"elevationinmeters","prefix":", ","suffix":"m."}]}
                    ]}},
                    {"divBlock":{"className":"collectorDiv","style":"margin-top:10px;font-size:6pt;clear:both;","blocks":[
                        {"fieldBlock":[{"field":"recordedby","style":"float:left;","prefix":"Coll.: ","prefixStyle":"font-weight:bold"},{"field":"preparations","style":"float:right","prefix":"Prep.: "}]}
                    ]}},
                    {"divBlock":{"className":"collectorDiv","style":"margin-top:10px;font-size:6pt;clear:both;","blocks":[
                        {"fieldBlock":[{"field":"recordnumber","style":"float:left;","prefix":"Coll. No: ","prefixStyle":"font-weight:bold"},{"field":"eventdate","style":"float:right","prefix":"Date: "}]}
                    ]}}
                ]}}
            ]
        },
        {
            "title": "Symbiota Generic Lichen Packet",
            "labelHeader": {
              "prefix": "Lichens of ",
              "midText": "2",
              "suffix": ", United States",
              "className": "text-2xl font-family-arial mt-2",
              "style": ""
            },
            "labelFooter": {
              "textValue": "Custom Collection Name",
              "className": "",
              "style": ""
            },
            "customStyles": "",
            "defaultCss": "../../css/v202209/symbiota/collections/reports/labelhelpers.css",
            "customCss": "../../css/v202209/symbiota/collections/reports/lichenpacket.css",
            "customJS": "../../js/symb/lichenpacket.js",
            "labelType": "packet",
            "pageSize": "letter",
            "displaySpeciesAuthor": 1,
            "displayBarcode": 1,
            "labelBlocks": [
              {
                "divBlock": {
                  "className": "label-blocks",
                  "style": "",
                  "blocks": [
                    {
                      "fieldBlock": [
                        {
                          "field": "scientificname",
                          "className": "font-bold italic text-xl font-family-arial"
                        },
                        {
                          "field": "scientificnameauthorship",
                          "className": "font-family-arial text-sm"
                        }
                      ],
                      "delimiter": " ",
                      "className": "mt-3"
                    },
                    {
                      "fieldBlock": [
                        {
                          "field": "identifiedby",
                          "className": "font-family-arial text-sm ml-2",
                          "prefix": "det. "
                        },
                        {
                          "field": "dateidentified",
                          "className": "font-family-arial text-sm ml-2"
                        }
                      ],
                      "delimiter": " "
                    },
                    {
                      "fieldBlock": [
                        {
                          "field": "country",
                          "className": "font-bold font-family-arial text-sm"
                        },
                        {
                          "field": "stateprovince",
                          "className": "font-bold font-family-arial text-sm"
                        },
                        {
                          "field": "county",
                          "className": "font-family-arial text-sm"
                        },
                        {
                          "field": "municipality",
                          "className": "text-sm font-family-arial"
                        },
                        {
                          "field": "locality",
                          "className": "font-family-arial text-sm"
                        }
                      ],
                      "delimiter": ", ",
                      "className": "mt-2 ml-2"
                    },
                    {
                      "fieldBlock": [
                        {
                          "field": "decimallatitude",
                          "className": "font-family-arial text-sm"
                        },
                        {
                          "field": "decimallongitude",
                          "className": "font-family-arial text-sm",
                          "prefix": ", ",
                          "suffix": ""
                        },
                        {
                          "field": "elevationinmeters",
                          "className": "font-family-arial text-sm",
                          "prefix": "; ",
                          "suffix": "m."
                        }
                      ],
                      "delimiter": "",
                      "className": "mt-2 ml-2"
                    },
                    {
                      "fieldBlock": [
                        {
                          "field": "habitat",
                          "className": "font-family-arial text-sm"
                        },
                        {
                          "field": "associatedtaxa",
                          "className": "font-family-arial text-sm"
                        },
                        {
                          "field": "substrate",
                          "className": "font-family-arial text-sm"
                        },
                        {
                          "field": "occurrenceremarks",
                          "className": "font-family-arial text-sm",
                          "prefix": ""
                        }
                      ],
                      "delimiter": "; ",
                      "className": "mt-2 ml-2"
                    },
                    {
                      "fieldBlock": [
                        {
                          "field": "recordedby",
                          "className": "font-bold font-family-arial text-sm"
                        },
                        {
                          "field": "recordnumber",
                          "className": "font-family-arial text-sm font-bold"
                        },
                        {
                          "field": "eventdate",
                          "className": "font-family-arial text-sm"
                        }
                      ],
                      "delimiter": " ",
                      "className": "mt-3"
                    }
                  ]
                }
              }
            ]
          }
    ]
}'; 
?>