<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:ns2="http://www.w3.org/1999/xlink" xmlns:local="http://www.yoursite.org/namespace" xmlns:ead="urn:isbn:1-931666-22-9" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="2.0" exclude-result-prefixes="#all">
    <!--
        *******************************************************************
        *                                                                 *
        * VERSION:      2.1.2                                             *
        *                                                                 *
        * AUTHOR:       Winona Salesky                                    *
        *               wsalesky@gmail.com                                *
        *                                                                 *
        * MODIFIED BY:  mikeg@artefactual.com                             *
        *               david@artefactual.com                             *
        *               thomas@tgconsulting.ca                            *
        *                                                                 *
        * DATE:         2024-04-10                                        *
        *                                                                 *
        *******************************************************************
    -->
    <xsl:output method="xml" encoding="utf-8" indent="yes"/>
    <xsl:strip-space elements="*"/>
    <!-- Define keys -->
    <xsl:key name="physlocId" match="ead:physloc" use="@id"/>
    <!-- The following attribute sets are reusabe styles used throughout the stylesheet. -->
    <!-- Headings -->
    <xsl:attribute-set name="h1">
        <xsl:attribute name="font-size">22pt</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="margin-top">16pt</xsl:attribute>
        <xsl:attribute name="margin-bottom">8pt</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h2">
        <xsl:attribute name="font-size">16pt</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="border-top">4pt solid #333</xsl:attribute>
        <xsl:attribute name="border-bottom">1pt dotted #333</xsl:attribute>
        <xsl:attribute name="margin-bottom">12pt</xsl:attribute>
        <xsl:attribute name="margin-top">4pt</xsl:attribute>
        <xsl:attribute name="padding-top">8pt</xsl:attribute>
        <xsl:attribute name="padding-bottom">8pt</xsl:attribute>
        <xsl:attribute name="keep-with-next.within-page">always</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h3">
        <xsl:attribute name="font-size">14pt</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="margin-bottom">4pt</xsl:attribute>
        <xsl:attribute name="padding-bottom">0</xsl:attribute>
        <xsl:attribute name="keep-with-next.within-page">always</xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h4">
        <xsl:attribute name="font-size">12pt</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="margin-bottom">4pt</xsl:attribute>
        <xsl:attribute name="padding-bottom">0</xsl:attribute>
        <xsl:attribute name="keep-with-next.within-page">always</xsl:attribute>
    </xsl:attribute-set>
    <!-- Headings with id attribute -->
    <xsl:attribute-set name="h1ID" use-attribute-sets="h1">
        <xsl:attribute name="id">
            <xsl:value-of select="local:buildID(.)"/>
        </xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h2ID" use-attribute-sets="h2">
        <xsl:attribute name="id">
            <xsl:value-of select="local:buildID(.)"/>
        </xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h3ID" use-attribute-sets="h3">
        <xsl:attribute name="id">
            <xsl:value-of select="local:buildID(.)"/>
        </xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h4ID" use-attribute-sets="h4">
        <xsl:attribute name="id">
            <xsl:value-of select="local:buildID(.)"/>
        </xsl:attribute>
    </xsl:attribute-set>
    <!-- Linking attributes styles -->
    <xsl:attribute-set name="ref">
        <xsl:attribute name="color">#14A6DC</xsl:attribute>
        <xsl:attribute name="text-decoration">underline</xsl:attribute>
    </xsl:attribute-set>
    <!-- Standard margin and padding for most fo:block elements, including paragraphs -->
    <xsl:attribute-set name="smp">
        <xsl:attribute name="margin">4pt</xsl:attribute>
        <xsl:attribute name="padding">4pt</xsl:attribute>
    </xsl:attribute-set>
    <!-- Standard margin and padding for elements with in the dsc table -->
    <xsl:attribute-set name="smpDsc">
        <xsl:attribute name="font-size">12pt</xsl:attribute>
        <xsl:attribute name="margin">2pt</xsl:attribute>
        <xsl:attribute name="padding">2pt</xsl:attribute>
    </xsl:attribute-set>
    <!-- Styles for main sections -->
    <xsl:attribute-set name="section">
        <xsl:attribute name="margin">4pt</xsl:attribute>
        <xsl:attribute name="padding">2pt</xsl:attribute>
    </xsl:attribute-set>
    <!-- Styles for table sections -->
    <xsl:attribute-set name="sectionTable">
        <xsl:attribute name="margin">4pt</xsl:attribute>
        <xsl:attribute name="padding">4pt</xsl:attribute>
        <xsl:attribute name="left">4pt</xsl:attribute>
    </xsl:attribute-set>
    <!-- Table attributes for tables with borders -->
    <xsl:attribute-set name="tableBorder">
        <xsl:attribute name="table-layout">fixed</xsl:attribute>
        <xsl:attribute name="width">100%</xsl:attribute>
        <xsl:attribute name="border">.5pt solid #000</xsl:attribute>
        <xsl:attribute name="border-collapse">separate</xsl:attribute>
        <xsl:attribute name="space-after">12pt</xsl:attribute>
    </xsl:attribute-set>
    <!-- Table headings -->
    <xsl:attribute-set name="th">
        <xsl:attribute name="background-color">#000</xsl:attribute>
        <xsl:attribute name="font-weight">bold</xsl:attribute>
        <xsl:attribute name="text-align">left</xsl:attribute>
    </xsl:attribute-set>
    <!-- Table cells with borders -->
    <xsl:attribute-set name="tdBorder">
        <xsl:attribute name="border">.5pt solid #000</xsl:attribute>
        <xsl:attribute name="border-collapse">separate</xsl:attribute>
    </xsl:attribute-set>
    <!-- Start main page design and layout -->
    <xsl:template match="/">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-size="12pt" font-family="serif">
            <xsl:attribute name="xml:lang">
                <xsl:value-of select="key('languageCode', /ead:ead/ead:eadheader/ead:profiledesc/ead:langusage/ead:language/@langcode, $languages)/@iso639-1" />
            </xsl:attribute>
            <!-- Set up page types and page layouts -->
            <fo:layout-master-set>
                <!-- Page master for Cover Page -->
                <fo:simple-page-master master-name="cover-page" page-width="8.5in" page-height="11in" margin="0.2in">
                    <fo:region-body margin="0in 0.3in 1in 0.3in"/>
                    <fo:region-before extent="0.2in"/>
                    <fo:region-after extent="3in"/>
                </fo:simple-page-master>
                <!-- Page master for Table of Contents -->
                <fo:simple-page-master master-name="toc" page-width="8.5in" page-height="11in" margin="0.5in">
                    <fo:region-body margin-top="0.25in" margin-bottom="0.25in"/>
                    <fo:region-before extent="0.5in"/>
                    <fo:region-after extent="0.2in"/>
                </fo:simple-page-master>
                <!-- Page master for Finding Aid Contents -->
                <fo:simple-page-master master-name="contents" page-width="8.5in" page-height="11in" margin="0.5in">
                    <fo:region-body margin-top="0.25in" margin-bottom="0.25in"/>
                    <fo:region-before extent="0.5in"/>
                    <fo:region-after extent="0.2in"/>
                </fo:simple-page-master>
            </fo:layout-master-set>
            <!-- Adds document XMP metadata -->
            <fo:declarations>
                <pdf:catalog xmlns:pdf="http://xmlgraphics.apache.org/fop/extensions/pdf">
                    <!-- this will replace the window title from filename to below dc:title -->
                    <pdf:dictionary type="normal" key="ViewerPreferences">
                        <pdf:boolean key="DisplayDocTitle">true</pdf:boolean>
                    </pdf:dictionary>
                </pdf:catalog>
                <x:xmpmeta xmlns:x="adobe:ns:meta/">
                    <rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
                        <rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">
                            <dc:title>
                                <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:titlestmt/ead:titleproper" mode="coverPage"/>
                                <xsl:text> Finding aid</xsl:text>
                            </dc:title>
                        </rdf:Description>
                    </rdf:RDF>
                </x:xmpmeta>
            </fo:declarations>
            <!-- Builds PDF bookmarks for all major sections -->
            <xsl:apply-templates select="/ead:ead/ead:archdesc" mode="bookmarks"/>
            <!-- The fo:page-sequence establishes headers, footers and the body of the page.-->
            <!-- Cover page layout -->
            <fo:page-sequence master-reference="cover-page">
                <fo:static-content flow-name="xsl-region-after">
                    <fo:block margin="0 0.3in">
                        <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt/ead:publisher" mode="coverPage"/>
                    </fo:block>
                    <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt" mode="coverPage"/>
                </fo:static-content>
                <fo:flow flow-name="xsl-region-body">
                    <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:titlestmt" mode="coverPage"/>
                </fo:flow>
            </fo:page-sequence>
            <!-- Table of Contents layout -->
            <fo:page-sequence master-reference="toc">
                <!-- Page header -->
                <fo:static-content role="artifact" flow-name="xsl-region-before" margin-top=".15in">
                    <fo:block color="black" font-weight="bold" font-size="16pt" text-align="center">
                        <xsl:apply-templates select="ead:ead/ead:eadheader/ead:filedesc/ead:titlestmt" mode="pageHeader"/>
                    </fo:block>
                </fo:static-content>
                <!-- Page footer-->
                <fo:static-content role="artifact" flow-name="xsl-region-after">
                    <fo:block text-align="center" color="gray">
                        <xsl:text>- Page </xsl:text>
                        <fo:page-number/>
                        <xsl:text> -</xsl:text>
                    </fo:block>
                </fo:static-content>
                <!-- Content of page -->
                <fo:flow flow-name="xsl-region-body">
                    <xsl:apply-templates select="/ead:ead/ead:archdesc" mode="toc"/>
                </fo:flow>
            </fo:page-sequence>
            <!-- All the rest -->
            <fo:page-sequence master-reference="contents">
                <!-- Page header -->
                <fo:static-content role="artifact" flow-name="xsl-region-before" margin-top=".15in">
                    <fo:block color="black" font-weight="normal" font-size="12pt" text-align="left" text-align-last="justify" border-bottom-style="solid">
                        <xsl:value-of select="//ead:eadid"/>
                        <fo:leader leader-pattern="space"/>
                        <xsl:apply-templates select="ead:ead/ead:eadheader/ead:filedesc/ead:titlestmt" mode="pageHeader"/>
                        <fo:leader leader-pattern="space"/>
                        <xsl:text/>
                    </fo:block>
                </fo:static-content>
                <!-- Page footer-->
                <fo:static-content role="artifact" flow-name="xsl-region-after">
                    <fo:block text-align="left" text-align-last="justify" border-top-style="solid">
                        <xsl:apply-templates select="(//ead:repository/ead:corpname)[1]"/>
                        <fo:leader leader-pattern="space"/>
                        <xsl:text/>
                        <fo:leader leader-pattern="space"/>
                        <xsl:text>Page </xsl:text>
                        <fo:page-number/>
                    </fo:block>
                </fo:static-content>
                <!-- Content of page -->
                <fo:flow flow-name="xsl-region-body">
                    <xsl:apply-templates select="/ead:ead/ead:archdesc"/>
                </fo:flow>
            </fo:page-sequence>
        </fo:root>
    </xsl:template>
    <xsl:template name="collectionUrl">
        <xsl:value-of select="//ead:eadid/@url"/>
    </xsl:template>
    <!-- Display Title Notes fields in RAD notes header -->
    <xsl:template name="titleNotes">
        <xsl:if test="ead:odd[starts-with(@type, 'title')]">
            <fo:block role="H3" xsl:use-attribute-sets="h3ID">Title notes</fo:block>
            <xsl:call-template name="toc"/>
            <fo:block xsl:use-attribute-sets="smp">
                <fo:list-block>
                    <xsl:for-each select="ead:odd[starts-with(@type, 'title')]">
                        <xsl:call-template name="titleNoteListItem"/>
                    </xsl:for-each>
                </fo:list-block>
            </fo:block>
        </xsl:if>
    </xsl:template>
    <xsl:template name="titleNoteListItem">
        <fo:list-item>
            <fo:list-item-label end-indent="label-end()">
                <fo:block>•</fo:block>
            </fo:list-item-label>
            <fo:list-item-body start-indent="body-start()">
                <fo:block>
                    <fo:inline text-decoration="underline" font-weight="bold">
                        <xsl:value-of select="local:titleNoteLabel(.)"/>
                    </fo:inline>: <xsl:value-of select="."/>
                </fo:block>
            </fo:list-item-body>
        </fo:list-item>
    </xsl:template>
    <!-- Named template to link back to the table of contents -->
    <xsl:template name="toc">
        <!-- Uncomment to enable 'back to toc' links on each section.
        <fo:block font-size="11pt" margin-top="12pt" margin-bottom="18pt">
            <fo:basic-link text-decoration="none" internal-destination="toc" color="#14A6DC">
            <fo:inline font-weight="bold">^</fo:inline> Return to Table of Contents </fo:basic-link>
        </fo:block>
        -->
    </xsl:template>
    <!-- Cover page templates -->
    <!-- Builds title -->
    <xsl:template match="ead:titlestmt" mode="pageHeader">
        <xsl:apply-templates select="ead:titleproper[1]"/>
    </xsl:template>
    <xsl:template match="ead:titlestmt" mode="coverPage">
        <!-- Calls template with links to archive logo -->
        <fo:block border-bottom="1pt solid #666" margin-top="1.5cm" id="cover-page">
            <xsl:call-template name="logo"/>
            <fo:block role="H1" xsl:use-attribute-sets="h1">
                Finding Aid -
                <xsl:apply-templates select="ead:titleproper[1]"/>
                (<xsl:value-of select="//ead:eadid"/>)
                <xsl:if test="ead:subtitle">
                    <fo:block font-size="16" font-weight="bold">
                        <xsl:apply-templates select="ead:subtitle"/>
                    </fo:block>
                </xsl:if>
            </fo:block>
        </fo:block>
        <fo:block margin-top="8pt">
            <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:profiledesc"/>
        </fo:block>
        <fo:block margin-top="8pt">
            <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:editionstmt"/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:publicationstmt" mode="coverPage">
        <fo:block margin="0 0.3in">
            <xsl:apply-templates select="ead:address"/>
            <fo:block>
                <fo:basic-link external-destination="{//ead:eadid/@url}" xsl:use-attribute-sets="ref">
                    <xsl:value-of select="//ead:eadid/@url"/>
                </fo:basic-link>
            </fo:block>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:profiledesc/child::*[not(self::ead:creation)]">
        <fo:block>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:profiledesc/ead:descrules"/>
    <xsl:template match="ead:profiledesc/ead:language">
        <xsl:value-of select="."/>
    </xsl:template>
    <xsl:template match="ead:profiledesc/ead:creation">
        Generated by <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt/ead:publisher" mode="coverPage"/> on <xsl:apply-templates select="ead:date"/>
    </xsl:template>
    <xsl:template match="ead:profiledesc/ead:creation/ead:date">
        <!--
            Uses local function to format date into Month, day year.
            To print date as seen in xml change to select="."
        -->
        <xsl:apply-templates select="local:parseDate(.)"/>
    </xsl:template>
    <!-- Generates PDF Bookmarks -->
    <xsl:template match="ead:archdesc" mode="bookmarks">
        <fo:bookmark-tree>
            <fo:bookmark internal-destination="cover-page">
                <fo:bookmark-title>Title Page</fo:bookmark-title>
            </fo:bookmark>
            <xsl:if test="ead:did">
                <fo:bookmark internal-destination="{local:buildID(ead:did)}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:did)"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:bioghist">
                <fo:bookmark internal-destination="{local:buildID(ead:bioghist[1])}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:bioghist[1])"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:scopecontent">
                <fo:bookmark internal-destination="{local:buildID(ead:scopecontent[1])}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:scopecontent[1])"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:fileplan">
                <fo:bookmark internal-destination="{local:buildID(ead:fileplan[1])}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:fileplan[1])"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <!-- Administrative Information -->
            <xsl:if test="ead:accessrestrict or ead:userestrict or ead:custodhist or ead:accruals or ead:altformavail or ead:acqinfo or ead:processinfo or ead:appraisal or ead:originalsloc or /ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt or /ead:ead/ead:eadheader/ead:revisiondesc">
                <fo:bookmark internal-destination="adminInfo">
                    <fo:bookmark-title>Notes</fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:phystech">
                <fo:bookmark internal-destination="{local:buildID(ead:phystech[1])}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:phystech[1])"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:controlaccess">
                <fo:bookmark internal-destination="{local:buildID(ead:controlaccess[1])}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:controlaccess[1])"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:bibliography">
                <fo:bookmark internal-destination="{local:buildID(ead:bibliography[1])}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:bibliography[1])"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:index">
                <fo:bookmark internal-destination="{local:buildID(ead:index[1])}">
                    <fo:bookmark-title>
                        <xsl:value-of select="local:tagName(ead:index[1])"/>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <!-- Get c-level bookmarks -->
            <xsl:apply-templates select="ead:dsc" mode="bookmarks"/>
        </fo:bookmark-tree>
    </xsl:template>
    <!-- Table of Contents -->
    <xsl:template match="ead:archdesc" mode="toc">
        <fo:block line-height="18pt" margin-top="0.25in">
            <fo:block role="H2" xsl:use-attribute-sets="h2" id="toc">Table of contents</fo:block>
            <fo:block xsl:use-attribute-sets="section">
                <xsl:if test="ead:did">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:did)}">
                            <xsl:value-of select="local:tagName(ead:did)"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:did)}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:bioghist">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:bioghist[1])}">
                            <xsl:value-of select="local:tagName(ead:bioghist[1])"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:bioghist[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:scopecontent">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:scopecontent[1])}">
                            <xsl:value-of select="local:tagName(ead:scopecontent[1])"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:scopecontent[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:fileplan">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:fileplan[1])}">
                            <xsl:value-of select="local:tagName(ead:fileplan[1])"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:fileplan[1])}"/>
                    </fo:block>
                </xsl:if>
                <!-- NOTES HEADING -->
                <xsl:if test="ead:accessrestrict | ead:userestrict | ead:custodhist | ead:accruals | ead:altformavail | ead:acqinfo | ead:processinfo | ead:appraisal | ead:originalsloc | /ead:ead/ead:eadheader/ead:revisiondesc">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="adminInfo">Notes</fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="adminInfo"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:controlaccess">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:controlaccess[1])}">
                            <xsl:value-of select="local:tagName(ead:controlaccess[1])"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:controlaccess[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:phystech">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:phystech[1])}">
                            <xsl:value-of select="local:tagName(ead:phystech[1])"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:phystech[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:bibliography">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:bibliography[1])}">
                            <xsl:value-of select="local:tagName(ead:bibliography[1])"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:bibliography[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:index">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:index[1])}">
                            <xsl:value-of select="local:tagName(ead:index[1])"/>
                        </fo:basic-link>
                        <xsl:text>  </xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>  </xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:index[1])}"/>
                    </fo:block>
                </xsl:if>
                <!-- Get c-level menu and submenu -->
                <xsl:apply-templates select="ead:dsc" mode="toc"/>
            </fo:block>
        </fo:block>
    </xsl:template>
    <!--
        Formats children of archdesc. This template orders the children of the archdesc,
        if order is changed it must also be changed in the table of contents.
    -->
    <xsl:template match="ead:archdesc">
        <xsl:apply-templates select="ead:did"/>
        <xsl:apply-templates select="ead:bioghist"/>
        <xsl:apply-templates select="ead:custodhist"/>
        <xsl:apply-templates select="ead:scopecontent"/>
        <xsl:apply-templates select="ead:fileplan"/>
        <!-- NOTES SECTION -->
        <xsl:if test="ead:accessrestrict | ead:userestrict | ead:custodhist | ead:accruals | ead:did/ead:note | ead:altformavail | ead:acqinfo | ead:processinfo | ead:appraisal | ead:originalsloc | /ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt | /ead:ead/ead:eadheader/ead:revisiondesc">
            <fo:block xsl:use-attribute-sets="section">
                <fo:block role="H2" xsl:use-attribute-sets="h2" id="adminInfo">Notes</fo:block>
                <!-- Try to handle EAD tags in RAD order... -->
                <xsl:call-template name="titleNotes"/>
                <xsl:apply-templates select="ead:phystech"/>
                <xsl:apply-templates select="ead:acqinfo"/>
                <xsl:apply-templates select="ead:arrangement"/>
                <xsl:apply-templates select="ead:originalsloc"/>
                <xsl:apply-templates select="ead:altformavail"/>
                <xsl:apply-templates select="ead:accessrestrict"/>
                <xsl:apply-templates select="ead:userestrict"/>
                <xsl:apply-templates select="ead:otherfindaid"/>
                <xsl:apply-templates select="ead:relatedmaterial"/>
                <xsl:apply-templates select="ead:accruals"/>
                <xsl:apply-templates select="ead:did/ead:note"/>
                <xsl:call-template name="otherNotes"/>
                <xsl:call-template name="toc"/>
            </fo:block>
        </xsl:if>
        <xsl:apply-templates select="ead:controlaccess"/>
        <xsl:apply-templates select="ead:bibliography"/>
        <xsl:apply-templates select="ead:dsc"/>
    </xsl:template>
    <!-- Formats archdesc did -->
    <xsl:template match="ead:archdesc/ead:did">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block role="H2" xsl:use-attribute-sets="h2ID">Summary information</fo:block>
            <!--
                Determines the order in which elements from the archdesc did appear,
                to change the order of appearance change the order of the following
                apply-template statements.
            -->
            <fo:table table-layout="fixed" width="100%">
                <fo:table-column xmlns:fox="http://xmlgraphics.apache.org/fop/extensions" fox:header="true" column-width="2in"/>
                <fo:table-column column-width="5in"/>
                <fo:table-body>
                    <xsl:call-template name="summaryInfoOtherField">
                        <xsl:with-param name="path" select="//ead:repository/ead:corpname"/>
                        <xsl:with-param name="label" select="'Repository'"/>
                    </xsl:call-template>
                    <xsl:apply-templates select="//ead:archdesc/ead:origination" mode="overview"/>
                    <xsl:apply-templates select="ead:unittitle" mode="overview"/>
                    <xsl:apply-templates select="ead:unitid" mode="overview"/>
                    <xsl:apply-templates select="ead:unitdate" mode="overview"/>
                    <xsl:apply-templates select="ead:physdesc" mode="overview"/>
                    <xsl:apply-templates select="ead:container" mode="overview"/>
                    <xsl:apply-templates select="ead:langmaterial/ead:language" mode="overview"/>
                    <xsl:call-template name="summaryInfoOtherField">
                        <xsl:with-param name="path" select="//ead:processinfo/ead:p/ead:date"/>
                        <xsl:with-param name="label" select="'Dates of creation, revision and deletion'"/>
                    </xsl:call-template>
                </fo:table-body>
            </fo:table>
            <!-- Link back to table of contents -->
            <xsl:call-template name="toc"/>
        </fo:block>
    </xsl:template>
    <!-- Formats children of arcdesc/did -->
    <xsl:template match="ead:repository | ead:origination | ead:unittitle | ead:unitdate | ead:unitid | ead:physdesc | ead:container | ead:dao | ead:daogrp | ead:langmaterial | ead:materialspec | ead:abstract | ead:note | ead:langmaterial/ead:language" mode="overview">
        <fo:table-row>
            <fo:table-cell padding-bottom="8pt" padding-right="16pt" text-align="right" font-weight="bold">
                <fo:block>
                    <xsl:apply-templates select="." mode="fieldLabel"/>:
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-bottom="2pt">
                <fo:block>
                    <xsl:apply-templates select="." mode="fieldValue"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
    <xsl:template name="summaryInfoOtherField">
        <xsl:param name="path"/>
        <xsl:param name="label"/>
        <xsl:if test="$path">
            <fo:table-row>
                <fo:table-cell padding-bottom="8pt" padding-right="16pt" text-align="right" font-weight="bold">
                    <fo:block>
                        <xsl:value-of select="$label"/>:
                    </fo:block>
                </fo:table-cell>
                <fo:table-cell padding-bottom="2pt">
                    <fo:block>
                        <xsl:apply-templates select="($path)[1]"/>
                    </fo:block>
                </fo:table-cell>
            </fo:table-row>
        </xsl:if>
    </xsl:template>
    <!-- Formats the 'other' notes headings -->
    <xsl:template name="otherNotes">
        <xsl:if test="ead:odd">
            <fo:block role="H3" xsl:use-attribute-sets="h3ID">Other notes</fo:block>
            <fo:list-block xsl:use-attribute-sets="section">
                <xsl:for-each select="ead:odd">
                    <xsl:variable name="label" select="local:oddLabel(.)"/>
                    <xsl:if test="string-length($label) > 0">
                        <fo:list-item>
                            <fo:list-item-label end-indent="label-end()">
                                <fo:block>•</fo:block>
                            </fo:list-item-label>
                            <fo:list-item-body start-indent="body-start()">
                                <fo:block>
                                    <!-- We have other <odd> elements used
                                    elsewhere, such as title notes. Only print
                                    the <odd> elements we've *handled* here in
                                    this section. -->
                                    <fo:inline text-decoration="underline" font-weight="bold">
                                        <xsl:value-of select="$label"/>
                                    </fo:inline>
                                    <xsl:text>: </xsl:text>
                                    <xsl:value-of select="."/>
                                </fo:block>
                            </fo:list-item-body>
                        </fo:list-item>
                    </xsl:if>
                </xsl:for-each>
            </fo:list-block>
        </xsl:if>
    </xsl:template>
    <xsl:template name="otherNotesSeries">
        <xsl:if test="following-sibling::ead:odd">
            <xsl:for-each select="following-sibling::ead:odd">
                <xsl:variable name="label" select="local:oddLabel(.)"/>
                <xsl:if test="string-length($label) > 0">
                    <fo:block font-size="12" padding="2" margin="2">
                        <!-- We have other <odd> elements used elsewhere, such
                        as title notes. only print the <odd> elements we've
                        *handled* here in this section. -->
                        <fo:inline text-decoration="underline" margin="2">
                            <xsl:value-of select="$label"/>
                        </fo:inline>
                        <xsl:text>: </xsl:text>
                        <fo:block/>
                        <fo:block margin="4" padding="4">
                            <xsl:value-of select="."/>
                        </fo:block>
                    </fo:block>
                </xsl:if>
            </xsl:for-each>
        </xsl:if>
    </xsl:template>
    <!-- Adds space between extents -->
    <xsl:template match="ead:extent"><xsl:apply-templates/> </xsl:template>
    <!-- Formats children of arcdesc not in administrative or related materials sections-->
    <xsl:template match="ead:bibliography | ead:odd | ead:bioghist | ead:scopecontent | ead:fileplan">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block role="H2" xsl:use-attribute-sets="h2ID">
                <xsl:value-of select="local:tagName(.)"/>
            </fo:block>
            <xsl:choose>
              <xsl:when test="ead:note">
                <xsl:apply-templates select="./ead:note/*" />
              </xsl:when>
              <xsl:otherwise>
                <xsl:apply-templates/>
              </xsl:otherwise>
            </xsl:choose>
            <xsl:call-template name="toc"/>
        </fo:block>
    </xsl:template>
    <!-- Formats children of arcdesc in administrative and related materials sections -->
    <xsl:template match="ead:relatedmaterial | ead:separatedmaterial | ead:accessrestrict | ead:userestrict | ead:custodhist | ead:accruals | ead:note | ead:notestmt | ead:altformavail | ead:acqinfo | ead:processinfo | ead:appraisal | ead:originalsloc | ead:phystech | ead:arrangement | ead:otherfindaid">
        <fo:block role="H3" xsl:use-attribute-sets="h3ID">
            <xsl:apply-templates select="." mode="fieldLabel"/>
        </fo:block>
        <fo:block xsl:use-attribute-sets="section">
            <xsl:apply-templates select="." mode="fieldValue"/>
        </fo:block>
    </xsl:template>
    <!-- Publication statement included in administrative information section -->
    <xsl:template match="ead:publicationstmt"/>
    <!-- Formats Address elements -->
    <xsl:template match="ead:address">
        <fo:block>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:addressline">
        <xsl:choose>
            <xsl:when test="contains(.,'@')">
                <fo:block>
                    <fo:basic-link external-destination="url('mailto:{replace(.,'(.*?)([a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})(.*?)','$2')}')" xsl:use-attribute-sets="ref">
                        <xsl:value-of select="."/>
                    </fo:basic-link>
                </fo:block>
            </xsl:when>
            <xsl:when test="contains(text(),'https://') or contains(text(),'http://')">
                <fo:block>
                    <fo:basic-link external-destination="{.}" xsl:use-attribute-sets="ref">
                        <xsl:value-of select="."/>
                    </fo:basic-link>
                </fo:block>
            </xsl:when>
            <xsl:otherwise>
                <fo:block>
                    <xsl:apply-templates/>
                </fo:block>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <!-- Templates for revision description -->
    <xsl:template match="ead:revisiondesc">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block role="H3" xsl:use-attribute-sets="h3ID">
                <xsl:value-of select="local:tagName(.)"/>
            </fo:block>
            <xsl:if test="ead:change/ead:item">
                <xsl:value-of select="ead:change/ead:item"/>
            </xsl:if>
            <xsl:if test="ead:change/ead:date"> <xsl:value-of select="ead:change/ead:date"/></xsl:if>
        </fo:block>
    </xsl:template>
    <!-- Formats controlled access terms -->
    <xsl:template match="ead:controlaccess">
        <xsl:if test="child::*">
            <fo:block xsl:use-attribute-sets="section">
                <fo:block role="H2" xsl:use-attribute-sets="h2ID">
                    <xsl:value-of select="local:tagName(.)"/>
                </fo:block>
                <fo:list-block xsl:use-attribute-sets="smp">
                    <xsl:apply-templates/>
                </fo:list-block>
            </fo:block>
        </xsl:if>
    </xsl:template>
    <xsl:template match="ead:controlaccess/child::*">
        <fo:list-item>
            <fo:list-item-label end-indent="label-end()">
                <fo:block>•</fo:block>
            </fo:list-item-label>
            <fo:list-item-body start-indent="body-start()">
                <fo:block>
                    <xsl:value-of select="."/>
                    <!-- Present the type of access point after the value in parentheses -->
                    <xsl:if test="name()='genreform'"> (documentary form)</xsl:if>
                    <xsl:if test="name()='subject'"> (subject)</xsl:if>
                    <xsl:if test="name()='geogname'"> (place)</xsl:if>
                    <xsl:if test="name()='name' or name()='persname'">
                        <xsl:if test="current()[@role='Creator']"> (creator)</xsl:if>
                        <xsl:if test="current()[@role='subject']"> (subject)</xsl:if>
                    </xsl:if>
                </fo:block>
            </fo:list-item-body>
        </fo:list-item>
    </xsl:template>
    <!-- Formats index and child elements, groups indexentry elements by type (i.e. corpname, subject...) -->
    <xsl:template match="ead:index">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block role="H2" xsl:use-attribute-sets="h2ID">
                <xsl:value-of select="local:tagName(.)"/>
            </fo:block>
            <xsl:apply-templates select="child::*[not(self::ead:indexentry)]"/>
            <fo:list-block xsl:use-attribute-sets="smp">
                <xsl:apply-templates select="ead:indexentry"/>
            </fo:list-block>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:indexentry">
        <fo:list-item>
            <fo:list-item-label end-indent="label-end()">
                <fo:block>•</fo:block>
            </fo:list-item-label>
            <fo:list-item-body start-indent="body-start()">
                <fo:block>
                    <xsl:apply-templates/>
                </fo:block>
            </fo:list-item-body>
        </fo:list-item>
    </xsl:template>
    <!-- Formats a simple table. The width of each column is defined by the colwidth attribute in a colspec element. -->
    <xsl:template match="ead:table">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="ead:table/ead:thead">
        <fo:block role="H4" xsl:use-attribute-sets="h4ID">
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:tgroup">
        <fo:table xsl:use-attribute-sets="tableBorder">
            <xsl:apply-templates/>
            <fo:table-body>
                <xsl:apply-templates select="*[not(ead:colspec)]"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>
    <xsl:template match="ead:colspec">
        <fo:table-column xmlns:fox="http://xmlgraphics.apache.org/fop/extensions" fox:header="true" column-width="{@colwidth}"/>
    </xsl:template>
    <xsl:template match="ead:thead">
        <xsl:apply-templates mode="thead"/>
    </xsl:template>
    <xsl:template match="ead:tbody">
        <fo:table-body>
            <xsl:apply-templates/>
        </fo:table-body>
    </xsl:template>
    <xsl:template match="ead:row" mode="thead">
        <fo:table-row xsl:use-attribute-sets="th">
            <xsl:apply-templates/>
        </fo:table-row>
    </xsl:template>
    <xsl:template match="ead:row">
        <fo:table-row>
            <xsl:apply-templates/>
        </fo:table-row>
    </xsl:template>
    <xsl:template match="ead:entry">
        <fo:table-cell xsl:use-attribute-sets="tdBorder">
            <fo:block xsl:use-attribute-sets="smp">
                <xsl:apply-templates/>
            </fo:block>
        </fo:table-cell>
    </xsl:template>
    <!--Bibref citation inline, if there is a parent element.-->
    <xsl:template match="ead:p/ead:bibref">
        <xsl:choose>
            <xsl:when test="@*:href">
                <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
                    <xsl:apply-templates/>
                </fo:basic-link>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <!--Bibref citation on its own line, typically when it is a child of the bibliography element-->
    <xsl:template match="ead:bibref">
        <fo:block>
            <xsl:choose>
                <xsl:when test="@*:href">
                    <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
                        <xsl:apply-templates/>
                    </fo:basic-link>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:block>
    </xsl:template>
    <!-- Lists -->
    <!-- Lists with listhead element are output as tables -->
    <xsl:template match="ead:list[ead:listhead]">
        <xsl:apply-templates select="ead:head"/>
        <fo:table xsl:use-attribute-sets="tableBorder">
            <fo:table-body>
                <fo:table-row xsl:use-attribute-sets="th">
                    <fo:table-cell xsl:use-attribute-sets="tdBorder">
                        <fo:block xsl:use-attribute-sets="smp">
                            <xsl:value-of select="ead:listhead/ead:head01"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell xsl:use-attribute-sets="tdBorder">
                        <fo:block>
                            <xsl:value-of select="ead:listhead/ead:head02"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <xsl:apply-templates select="ead:defitem" mode="listTable"/>
            </fo:table-body>
        </fo:table>
    </xsl:template>
    <!-- Formats ordered and definition lists -->
    <xsl:template match="ead:list">
        <xsl:apply-templates select="ead:head"/>
        <fo:list-block xsl:use-attribute-sets="smp">
            <xsl:choose>
                <xsl:when test="@type='deflist'">
                    <xsl:apply-templates select="ead:defitem"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:apply-templates select="ead:item"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:list-block>
    </xsl:template>
    <xsl:template match="ead:item">
        <fo:list-item>
            <fo:list-item-label end-indent="label-end()">
                <fo:block>
                    <xsl:choose>
                        <xsl:when test="../@type='ordered' and ../@numeration = 'arabic'">
                            <xsl:number format="1"/>
                        </xsl:when>
                        <xsl:when test="../@type='ordered' and ../@numeration = 'upperalpha'">
                            <xsl:number format="A"/>
                        </xsl:when>
                        <xsl:when test="../@type='ordered' and ../@numeration = 'loweralpha'">
                            <xsl:number format="a"/>
                        </xsl:when>
                        <xsl:when test="../@type='ordered' and ../@numeration = 'upperroman'">
                            <xsl:number format="I"/>
                        </xsl:when>
                        <xsl:when test="../@type='ordered' and ../@numeration = 'upperalpha'">
                            <xsl:number format="i"/>
                        </xsl:when>
                        <xsl:when test="../@type='ordered' and not(../@numeration)">
                            <xsl:number format="1"/>
                        </xsl:when>
                        <xsl:otherwise>•</xsl:otherwise>
                    </xsl:choose>
                </fo:block>
            </fo:list-item-label>
            <fo:list-item-body start-indent="body-start()">
                <fo:block>
                    <xsl:apply-templates/>
                </fo:block>
            </fo:list-item-body>
        </fo:list-item>
    </xsl:template>
    <xsl:template match="ead:defitem">
        <fo:list-item>
            <fo:list-item-label end-indent="label-end()">
                <fo:block/>
            </fo:list-item-label>
            <fo:list-item-body start-indent="body-start()">
                <fo:block font-weight="bold">
                    <xsl:apply-templates select="ead:label"/>
                </fo:block>
                <fo:block margin-left="18pt">
                    <xsl:apply-templates select="ead:item" mode="deflist"/>
                </fo:block>
            </fo:list-item-body>
        </fo:list-item>
    </xsl:template>
    <!-- Formats list as table if list has listhead element -->
    <xsl:template match="ead:defitem" mode="listTable">
        <fo:table-row>
            <fo:table-cell xsl:use-attribute-sets="tdBorder">
                <fo:block xsl:use-attribute-sets="smp">
                    <xsl:apply-templates select="ead:label"/>
                </fo:block>
            </fo:table-cell>
            <fo:table-cell xsl:use-attribute-sets="tdBorder">
                <fo:block xsl:use-attribute-sets="smp">
                    <xsl:apply-templates select="ead:item"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
    <!-- Output chronlist and children in a table -->
    <xsl:template match="ead:chronlist">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="ead:chronitem">
        <fo:block linefeed-treatment="preserve">
            <xsl:apply-templates select="descendant::ead:note"/>
            <xsl:value-of select="text()"/>
        </fo:block>
    </xsl:template>
    <!-- Formats legalstatus -->
    <xsl:template match="ead:legalstatus">
        <fo:block xsl:use-attribute-sets="smp">
            <fo:inline font-weight="bold"><xsl:value-of select="local:tagName(.)"/>: </fo:inline>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <!-- General headings -->
    <!-- Children of the archdesc are handled by the local:tagName function -->
    <xsl:template match="ead:head[parent::*/parent::ead:archdesc]"/>
    <!-- All other headings -->
    <xsl:template match="ead:head">
        <fo:block role="H4" xsl:use-attribute-sets="h4" id="{local:buildID(parent::*)}">
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <!-- Linking elmenets -->
    <xsl:template match="ead:ref">
        <fo:basic-link internal-destination="{@target}" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="text()">
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:when test="@*:title">
                    <xsl:value-of select="@*:title"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@target"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:ptr">
        <fo:basic-link external-destination="url('{@target}')" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="child::*">
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:when test="@*:title">
                    <xsl:value-of select="@*:title"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@target"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:extref">
        <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="text()">
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@*:href"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:extrefloc">
        <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="text()">
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@*:href"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:extptr[@*:entityref]">
        <fo:basic-link external-destination="url('{@*:entityref}')" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="@*:title">
                    <xsl:value-of select="@*:title"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@*:entityref"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:extptr[@*:href]">
        <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="@*:title">
                    <xsl:value-of select="@*:title"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@*:href"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:dao">
        <xsl:variable name="linkTitle">
            <xsl:choose>
                <xsl:when test="child::*">
                    <xsl:apply-templates/>
                </xsl:when>
                <xsl:when test="@*:title">
                    <xsl:value-of select="@*:title"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@*:href"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
            <xsl:value-of select="$linkTitle"/>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:daogrp">
        <fo:block>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:daoloc">
        <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="text()">
                    <xsl:value-of select="."/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@*:href"/>
                </xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <!--Render elements -->
    <xsl:template match="*[@render = 'bold'] | *[@altrender = 'bold'] ">
        <fo:inline font-weight="bold">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'bolddoublequote'] | *[@altrender = 'bolddoublequote']">
        <fo:inline font-weight="bold">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            "<xsl:apply-templates/>"
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'boldsinglequote'] | *[@altrender = 'boldsinglequote']">
        <fo:inline font-weight="bold">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            '<xsl:apply-templates/>'
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'bolditalic'] | *[@altrender = 'bolditalic']">
        <fo:inline font-weight="bold" font-style="italic">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'boldsmcaps'] | *[@altrender = 'boldsmcaps']">
        <fo:inline font-weight="bold" font-variant="small-caps">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'boldunderline'] | *[@altrender = 'boldunderline']">
        <fo:inline font-weight="bold" border-bottom="1pt solid #000">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'doublequote'] | *[@altrender = 'doublequote']"><xsl:if test="preceding-sibling::*">  </xsl:if>"<xsl:apply-templates/>" </xsl:template>
    <xsl:template match="*[@render = 'italic'] | *[@altrender = 'italic']">
        <fo:inline font-style="italic">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'singlequote'] | *[@altrender = 'singlequote']"><xsl:if test="preceding-sibling::*">  </xsl:if>'<xsl:apply-templates/>' </xsl:template>
    <xsl:template match="*[@render = 'smcaps'] | *[@altrender = 'smcaps']">
        <fo:inline font-variant="small-caps">
            <xsl:if test="preceding-sibling::*">  </xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'sub'] | *[@altrender = 'sub']">
        <fo:inline baseline-shift="sub">
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'super'] | *[@altrender = 'super']">
        <fo:inline baseline-shift="super">
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'underline'] | *[@altrender = 'underline']">
        <fo:inline text-decoration="underline">
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <!-- Formatting elements -->
    <xsl:template match="ead:p">
        <xsl:choose>
            <xsl:when test="preceding-sibling::ead:p">
                <fo:block margin-top="4pt">
                    <xsl:apply-templates/>
                </fo:block>
            </xsl:when>
            <xsl:otherwise><xsl:apply-templates/></xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="ead:lb">
        <fo:block/>
    </xsl:template>
    <xsl:template match="ead:blockquote">
        <fo:block margin="4pt 18pt">
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:emph[not(@render)]">
        <fo:inline font-style="italic">
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="ead:index" mode="dsc">
        <xsl:apply-templates select="child::*[not(self::ead:indexentry)]"/>
        <fo:list-block xsl:use-attribute-sets="smpDsc">
            <xsl:apply-templates select="ead:indexentry"/>
        </fo:list-block>
    </xsl:template>
    <xsl:template match="ead:controlaccess" mode="dsc">
        <fo:block xsl:use-attribute-sets="smpDsc" text-decoration="underline"><xsl:value-of select="local:tagName(.)"/>:</fo:block>
        <fo:list-block xsl:use-attribute-sets="smpDsc">
            <xsl:apply-templates/>
        </fo:list-block>
    </xsl:template>
    <xsl:template match="ead:dao" mode="dsc">
        <xsl:variable name="title">
            <xsl:choose>
                <xsl:when test="child::*">
                    <xsl:apply-templates/>
                </xsl:when>
                <xsl:when test="@*:title">
                    <xsl:value-of select="@*:title"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="@*:href"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <fo:block xsl:use-attribute-sets="smpDsc">
            <fo:inline text-decoration="underline">
                <xsl:choose>
                    <!-- Test for label attribute used by origination element -->
                    <xsl:when test="@label">
                        <xsl:value-of select="local:ucfirst(@label)"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="local:tagName(.)"/>
                    </xsl:otherwise>
                </xsl:choose>
            </fo:inline>:
            <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
                <xsl:value-of select="$title"/>
            </fo:basic-link>
        </fo:block>
    </xsl:template>
    <!-- Everything else in the dsc -->
    <xsl:template mode="dsc" match="*">
        <xsl:if test="child::*">
            <fo:block xsl:use-attribute-sets="smpDsc">
                <xsl:apply-templates/>
            </fo:block>
        </xsl:if>
    </xsl:template>
    <!-- Field label -->
    <xsl:template match="*" mode="fieldLabel">
        <xsl:choose>
            <!-- Don't use @label for ead:container-->
            <xsl:when test="self::ead:container">
                <xsl:value-of select="local:tagName(.)"/>
            </xsl:when>
            <xsl:when test="@label">
                <xsl:value-of select="local:ucfirst(@label)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="local:tagName(.)"/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:if test="@type and not(self::ead:container)">
            [<xsl:value-of select="local:typeLabel(.)"/>]
        </xsl:if>
    </xsl:template>
    <!-- Field value -->
    <xsl:template match="ead:container" mode="fieldValue">
        <xsl:if test="key('physlocId', @parent)">
            <xsl:value-of select="key('physlocId', @parent)"/> &#8211;
        </xsl:if>
        <xsl:choose>
            <xsl:when test="@label">
                <xsl:value-of select="local:ucfirst(@label)"/>
                <xsl:value-of select="concat(' ', @type)"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="local:ucfirst(@type)"/>
            </xsl:otherwise>
        </xsl:choose>:
        <xsl:value-of select="."/>
    </xsl:template>
    <xsl:template match="*" mode="fieldValue">
        <xsl:apply-templates/>
        <xsl:if test="self::ead:unitdate">
            <xsl:choose>
                <xsl:when test="@datechar = 'accumulation'">
                    <xsl:text> </xsl:text>(<xsl:value-of select="@datechar"/>)
                </xsl:when>
                <xsl:otherwise>
                    <xsl:text> (creation)</xsl:text>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:if>
    </xsl:template>
</xsl:stylesheet>
