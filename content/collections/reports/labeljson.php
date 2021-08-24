<?php
 $LABEL_FORMAT_JSON = '{
    "labelFormats": [
        {
            "title": "Generic Herbarium Label",
            "displaySpeciesAuthor": 1,
            "displayBarcode": 0,
            "labelType": "2",
            "customStyles": "body{ font-size:10pt; }",
            "defaultCss": "..\/..\/css\/symb\/labelhelpers.css",
            "customCss": "",
            "customJs": "",
            "pageSize": "letter",
            "labelHeader": {
                "prefix": "Flora of ",
                "midText": 3,
                "suffix": " county",
                "className": "text-center font-bold font-sans text-2xl",
                "style": "margin-bottom:10px;"
            },
            "labelBlocks": [
                {
                    "divBlock": {
                        "className": "label-block",
                        "blocks": [
                            {
                                "divBlock": {
                                    "className": "taxonomy my-2 text-lg",
                                    "blocks": [
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "identificationqualifier"
                                                },
                                                {
                                                    "field": "speciesname",
                                                    "className": "font-bold italic"
                                                },
                                                {
                                                    "field": "parentauthor"
                                                },
                                                {
                                                    "field": "taxonrank",
                                                    "className": "font-bold"
                                                },
                                                {
                                                    "field": "infraspecificepithet",
                                                    "className": "font-bold italic"
                                                },
                                                {
                                                    "field": "scientificnameauthorship"
                                                }
                                            ],
                                            "delimiter": " "
                                        },
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "family",
                                                    "styles": [
                                                        "float:right"
                                                    ]
                                                }
                                            ]
                                        }
                                    ]
                                }
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identifiedby",
                                        "prefix": "Det by: "
                                    },
                                    {
                                        "field": "dateidentified"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identificationreferences"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identificationremarks"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "taxonremarks"
                                    }
                                ]
                            },
                            {
                                "divBlock": {
                                    "className": "text-lg",
                                    "style": "margin-top:10px;",
                                    "blocks": [
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "country",
                                                    "className": "font-bold"
                                                },
                                                {
                                                    "field": "stateprovince",
                                                    "style": "font-weight:bold"
                                                },
                                                {
                                                    "field": "county"
                                                },
                                                {
                                                    "field": "municipality"
                                                },
                                                {
                                                    "field": "locality"
                                                }
                                            ],
                                            "delimiter": ", "
                                        }
                                    ]
                                }
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "decimallatitude"
                                    },
                                    {
                                        "field": "decimallongitude",
                                        "style": "margin-left:10px"
                                    },
                                    {
                                        "field": "coordinateuncertaintyinmeters",
                                        "prefix": "+-",
                                        "suffix": " meters",
                                        "style": "margin-left:10px"
                                    },
                                    {
                                        "field": "geodeticdatum",
                                        "prefix": "[",
                                        "suffix": "]",
                                        "style": "margin-left:10px"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "verbatimcoordinates"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "elevationinmeters",
                                        "prefix": "Elev: ",
                                        "suffix": "m. "
                                    },
                                    {
                                        "field": "verbatimelevation"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "habitat",
                                        "suffix": "."
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "substrate",
                                        "suffix": "."
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "verbatimattributes"
                                    },
                                    {
                                        "field": "establishmentmeans"
                                    }
                                ],
                                "delimiter": "; "
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "associatedtaxa",
                                        "prefix": "Associated species: ",
                                        "className": "italic"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "occurrenceremarks"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "typestatus"
                                    }
                                ]
                            },
                            {
                                "divBlock": {
                                    "className": "collector",
                                    "style": "margin-top:10px;",
                                    "blocks": [
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "recordedby",
                                                    "style": "float:left"
                                                },
                                                {
                                                    "field": "recordnumber",
                                                    "style": "float:left;margin-left:10px"
                                                },
                                                {
                                                    "field": "eventdate",
                                                    "style": "float:right"
                                                }
                                            ]
                                        },
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "associatedcollectors",
                                                    "prefix": "with: "
                                                }
                                            ],
                                            "style": "clear:both; margin-left:10px;"
                                        }
                                    ]
                                }
                            }
                        ]
                    }
                }
            ],
            "labelFooter": {
                "textValue": "",
                "className": "text-center font-bold font-sans",
                "style": "margin-top:10px;"
            }
        },
        {
            "title": "Generic Vertebrate Label",
            "displaySpeciesAuthor": 0,
            "displayBarcode": 0,
            "labelType": "3",
            "customStyles": "body{ font-size:10pt; }",
            "defaultCss": "..\/..\/css\/symb\/labelhelpers.css",
            "customCss": "",
            "customJs": "",
            "pageSize": "letter",
            "labelHeader": {
                "prefix": "",
                "midText": 0,
                "suffix": "",
                "className": "text-center font-bold font-sans text-2xl",
                "style": "text-align:center;margin-bottom:5px;font:bold 7pt arial,sans-serif;clear:both;"
            },
            "labelFooter": {
                "textValue": "",
                "className": "text-center font-bold font-sans text-2xl",
                "style": "text-align:center;margin-top:10px;font:bold 10pt arial,sans-serif;clear:both;"
            },
            "labelBlocks": [
                {
                    "divBlock": {
                        "className": "labelBlockDiv",
                        "blocks": [
                            {
                                "fieldBlock": [
                                    {
                                        "field": "family",
                                        "styles": [
                                            "margin-bottom:2px;font-size:pt"
                                        ]
                                    }
                                ]
                            },
                            {
                                "divBlock": {
                                    "className": "taxonomyDiv",
                                    "style": "font-size:10pt;",
                                    "blocks": [
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "identificationqualifier"
                                                },
                                                {
                                                    "field": "speciesname",
                                                    "style": "font-weight:bold;font-style:italic"
                                                },
                                                {
                                                    "field": "parentauthor"
                                                },
                                                {
                                                    "field": "taxonrank",
                                                    "style": "font-weight:bold"
                                                },
                                                {
                                                    "field": "infraspecificepithet",
                                                    "style": "font-weight:bold;font-style:italic"
                                                },
                                                {
                                                    "field": "scientificnameauthorship"
                                                }
                                            ],
                                            "delimiter": " "
                                        }
                                    ]
                                }
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identifiedby",
                                        "prefix": "Det by: "
                                    },
                                    {
                                        "field": "dateidentified"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identificationreferences"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "identificationremarks"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "taxonremarks"
                                    }
                                ]
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "catalognumber",
                                        "style": "font-weight:bold;font-size:14pt;margin:5pt 0pt;"
                                    }
                                ]
                            },
                            {
                                "divBlock": {
                                    "className": "localDiv",
                                    "style": "margin-top:3px;padding-top:3px;border-top:3px solid black",
                                    "blocks": [
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "country"
                                                },
                                                {
                                                    "field": "stateprovince",
                                                    "prefix": ", "
                                                },
                                                {
                                                    "field": "county",
                                                    "prefix": ", "
                                                },
                                                {
                                                    "field": "municipality",
                                                    "prefix": ", "
                                                },
                                                {
                                                    "field": "locality",
                                                    "prefix": ": "
                                                },
                                                {
                                                    "field": "decimallatitude",
                                                    "prefix": ": ",
                                                    "suffix": "\u00b0 N"
                                                },
                                                {
                                                    "field": "decimallongitude",
                                                    "prefix": " ",
                                                    "suffix": "\u00b0 W"
                                                },
                                                {
                                                    "field": "coordinateuncertaintyinmeters",
                                                    "prefix": " +-",
                                                    "suffix": " meters",
                                                    "style": "margin-left:10px"
                                                },
                                                {
                                                    "field": "elevationinmeters",
                                                    "prefix": ", ",
                                                    "suffix": "m."
                                                }
                                            ]
                                        }
                                    ]
                                }
                            },
                            {
                                "divBlock": {
                                    "className": "collectorDiv",
                                    "style": "margin-top:10px;font-size:6pt;clear:both;",
                                    "blocks": [
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "recordedby",
                                                    "style": "float:left;",
                                                    "prefix": "Coll.: ",
                                                    "prefixStyle": "font-weight:bold"
                                                },
                                                {
                                                    "field": "preparations",
                                                    "style": "float:right",
                                                    "prefix": "Prep.: "
                                                }
                                            ]
                                        }
                                    ]
                                }
                            },
                            {
                                "divBlock": {
                                    "className": "collectorDiv",
                                    "style": "margin-top:10px;font-size:6pt;clear:both;",
                                    "blocks": [
                                        {
                                            "fieldBlock": [
                                                {
                                                    "field": "recordnumber",
                                                    "style": "float:left;",
                                                    "prefix": "Coll. No: ",
                                                    "prefixStyle": "font-weight:bold"
                                                },
                                                {
                                                    "field": "eventdate",
                                                    "style": "float:right",
                                                    "prefix": "Date: "
                                                }
                                            ]
                                        }
                                    ]
                                }
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
                "style": "font-variant: small-caps; font-size: 0.8em; text-align: center; margin-top: 0.5em;"
            },
            "customStyles": ".field-block{clear: both;} .family{font-variant: small-caps;} .associatedtaxaPrefix{font-style: normal; font-variant: small-caps;} .text-base{font-size: 0.95em;} .text-sm{font-size: 0.8em;} .cn-barcode{text-align: center;} .label{box-shadow: inset 0 0 0 5px white, inset 0 0 0 6px black;}",
            "defaultCss": "..\/..\/css\/symb\/labelhelpers.css",
            "customCss": "",
            "customJS": "",
            "labelType": "2",
            "pageSize": "letter",
            "displaySpeciesAuthor": 0,
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
                                "className": "mb-1"
                            },
                            {
                                "fieldBlock": [
                                    {
                                        "field": "scientificname",
                                        "className": "font-bold font-family-arial italic text-base"
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
                                "className": "ml-2"
                            }
                        ]
                    }
                }
            ]
        }
    ]
}'; 
?>