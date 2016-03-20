<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:xlink="http://www.w3.org/1999/xlink"
    xmlns:ns2="http://www.w3.org/1999/xlink" xmlns:local="http://www.yoursite.org/namespace"
    xmlns:ead="urn:isbn:1-931666-22-9" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="2.0"  exclude-result-prefixes="#all">
    <!--
        *******************************************************************
        *                                                                 *
        * VERSION:          1.0                                           *
        *                                                                 *
        * AUTHOR:           Winona Salesky                                *
        *                   wsalesky@gmail.com                            *
        *                                                                 *
        * MODIFIED BY:      mikeg@artefactual.com                         *
        *                                                                 *
        * DATE:             2013-08-21                                    *
        *                                                                 *
        *******************************************************************
    -->
    <xsl:output method="xml" encoding="utf-8" indent="yes"/>

    <!-- Calls a stylesheet with local functions and lookup lists for languages and subject authorities -->
    <xsl:include href="helper-functions.xsl"/>

    <xsl:strip-space elements="*"/>

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
        <xsl:attribute name="id"><xsl:value-of select="local:buildID(.)"/></xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h2ID" use-attribute-sets="h2">
        <xsl:attribute name="id"><xsl:value-of select="local:buildID(.)"/></xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h3ID" use-attribute-sets="h3">
        <xsl:attribute name="id"><xsl:value-of select="local:buildID(.)"/></xsl:attribute>
    </xsl:attribute-set>
    <xsl:attribute-set name="h4ID" use-attribute-sets="h4">
        <xsl:attribute name="id"><xsl:value-of select="local:buildID(.)"/></xsl:attribute>
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

    <!--  Start main page design and layout -->
    <xsl:template match="/">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format" font-size="12pt" font-family="serif">
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
            <!-- Builds PDF bookmarks for all major sections  -->
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
                <fo:static-content flow-name="xsl-region-before" margin-top=".15in">
                    <fo:block color="black" font-weight="bold" font-size="16pt" text-align="center">
                        <xsl:apply-templates select="ead:ead/ead:eadheader/ead:filedesc/ead:titlestmt" mode="pageHeader"/>
                    </fo:block>
                </fo:static-content>
                <!-- Page footer-->
                <fo:static-content flow-name="xsl-region-after">
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
                <fo:static-content flow-name="xsl-region-before" margin-top=".15in">
                    <fo:block color="black" font-weight="normal" font-size="12pt" text-align="left"
                        text-align-last="justify" border-bottom-style="solid">

                        <xsl:value-of select="//ead:eadid"/>
                    <fo:leader leader-pattern="space"/>
                        <xsl:apply-templates select="ead:ead/ead:eadheader/ead:filedesc/ead:titlestmt" mode="pageHeader"/>
                    <fo:leader leader-pattern="space"/>
                        <xsl:text/>
                    </fo:block>
                </fo:static-content>
                <!-- Page footer-->
                <fo:static-content flow-name="xsl-region-after">
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
        <fo:block xsl:use-attribute-sets="smp">
            <xsl:if test="ead:odd">
                <fo:block xsl:use-attribute-sets="h3ID">Title notes</fo:block>
                <xsl:call-template name="toc"/>
            </xsl:if>
            <xsl:for-each select="ead:odd[@type]">
                <xsl:variable name="oddLabel" select="local:oddTitleNoteHeadings(./@type)"/>
                <!-- If this isn't a title notes <odd> this function will return string len 0 -->
                <xsl:if test="string-length($oddLabel) &gt; 0">
                    <fo:list-block>
                        <fo:list-item>
                            <fo:list-item-label end-indent="label-end()">
                                <fo:block>&#x2022;</fo:block>
                            </fo:list-item-label>

                            <fo:list-item-body start-indent="body-start()">
                                <fo:block>
                                    <fo:inline text-decoration="underline" font-weight="bold">
                                        <xsl:value-of select="$oddLabel"/>
                                    </fo:inline>: <xsl:value-of select="."/>
                                </fo:block>
                            </fo:list-item-body>
                        </fo:list-item>
                    </fo:list-block>
                </xsl:if>
            </xsl:for-each>
        </fo:block>

    </xsl:template>

    <!-- Named template to link back to the table of contents  -->
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
        <!-- Uses filing type title if present -->
        <xsl:choose>
            <xsl:when test="ead:titleproper[@type='filing']">
                <xsl:apply-templates select="ead:titleproper[@type='filing']"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates select="ead:titleproper[1]"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="ead:titlestmt" mode="coverPage">
        <!-- Calls template with links to archive icon -->
        <fo:block border-bottom="1pt solid #666" margin-top="0in" id="cover-page">
            <fo:block xsl:use-attribute-sets="h1" linefeed-treatment="preserve">
                <xsl:choose>
                    <xsl:when test="ead:titleproper[@type='filing']">
                        Finding Aid - <xsl:apply-templates select="ead:titleproper[@type='filing']"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <fo:external-graphic src="images/pdf-logo.png"  height="1cm" width="2cm"
                            content-width="scale-to-fit" content-height="scale-to-fit"/>
                        <xsl:text> </xsl:text>
                        <xsl:apply-templates select="(//ead:repository/ead:corpname)[1]"/>
                        <fo:block/>
                        Finding Aid - <xsl:apply-templates select="ead:titleproper[1]"/> (<xsl:value-of select="//ead:eadid"/>)
                    </xsl:otherwise>
                </xsl:choose>
            </fo:block>
            <xsl:if test="ead:subtitle">
                <fo:block font-size="16" font-weight="bold"><xsl:apply-templates select="ead:subtitle"/></fo:block>
            </xsl:if>
        </fo:block>
        <fo:block margin-top="8pt">
            <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:profiledesc"/>
        </fo:block>
        <fo:block margin-top="8pt">
            <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:editionstmt"/>
        </fo:block>
        <!-- Logo in middle of cover page: -->
        <!-- Atom: <xsl:call-template name="icon"/>-->
    </xsl:template>
    <xsl:template match="ead:publicationstmt" mode="coverPage">
        <fo:block margin="0 0.3in">
            <!-- Atom:
            <fo:block>
                <xsl:apply-templates select="ead:publisher"/>
            </fo:block>
            -->

            <xsl:apply-templates select="ead:address"/>
            <xsl:value-of select="//ead:eadid/@url"/>
        </fo:block>
    </xsl:template>

    <xsl:template match="ead:profiledesc/child::*">
        <fo:block><xsl:apply-templates/></fo:block>
    </xsl:template>


    <xsl:template match="ead:profiledesc/ead:language">
        <xsl:value-of select="."/>
    </xsl:template>
    <xsl:template match="ead:profiledesc/ead:creation/ead:date">
        <!--
            Uses local function to format date into Month, day year.
            To print date as seen in xml change to select="."
        -->
        Printed: <xsl:apply-templates select="local:parseDate(.)"/>
    </xsl:template>

    <!-- This template can be modified to include repository specific icons, use the template as a example.  -->

    <xsl:template name="icon">
        <fo:block text-align="center" margin-left="-.75in" margin-top="-.5in" padding-top="2in">
            <fo:external-graphic src="logo.png"  height="4cm" width="4cm"
                content-height="scale-to-fit" content-width="scale-to-fit"/>
        </fo:block>
    </xsl:template>

    <!-- Generates PDF Bookmarks -->
    <xsl:template match="ead:archdesc" mode="bookmarks">
        <fo:bookmark-tree>
            <fo:bookmark internal-destination="cover-page">
                <fo:bookmark-title>Title Page</fo:bookmark-title>
            </fo:bookmark>
            <xsl:if test="ead:did">
                <fo:bookmark internal-destination="{local:buildID(ead:did)}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:did)"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:bioghist">
                <fo:bookmark internal-destination="{local:buildID(ead:bioghist[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:bioghist[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:scopecontent">
                <fo:bookmark internal-destination="{local:buildID(ead:scopecontent[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:scopecontent[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:arrangement">
                <fo:bookmark internal-destination="{local:buildID(ead:arrangement[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:arrangement[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:fileplan">
                <fo:bookmark internal-destination="{local:buildID(ead:fileplan[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:fileplan[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>

            <!-- Administrative Information  -->
            <xsl:if test="ead:accessrestrict or ead:userestrict or
                ead:custodhist or ead:accruals or ead:altformavail or ead:acqinfo or
                ead:processinfo or ead:appraisal or ead:originalsloc or /ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt or /ead:ead/ead:eadheader/ead:revisiondesc">
                <fo:bookmark internal-destination="adminInfo">
                    <fo:bookmark-title>Notes</fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>

            <xsl:if test="ead:controlaccess">
                <fo:bookmark internal-destination="{local:buildID(ead:controlaccess[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:controlaccess[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>

            <xsl:if test="ead:phystech">
                <fo:bookmark internal-destination="{local:buildID(ead:phystech[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:phystech[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:odd">
                <fo:bookmark internal-destination="{local:buildID(ead:odd[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:odd[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:bibliography">
                <fo:bookmark internal-destination="{local:buildID(ead:bibliography[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:bibliography[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>
            <xsl:if test="ead:index">
                <fo:bookmark internal-destination="{local:buildID(ead:index[1])}">
                    <fo:bookmark-title><xsl:value-of select="local:tagName(ead:index[1])"/></fo:bookmark-title>
                </fo:bookmark>
            </xsl:if>

            <!-- Build Container List menu and submenu -->
            <xsl:for-each select="ead:dsc">
                <xsl:if test="child::*">
                    <fo:bookmark internal-destination="{local:buildID(.)}">
                        <fo:bookmark-title><xsl:value-of select="local:tagName(.)"/></fo:bookmark-title>
                    </fo:bookmark>
                </xsl:if>
                <!--Creates a submenu for collections, record groups and series and fonds-->
                <xsl:for-each select="child::*[@level = 'collection']  | child::*[@level = 'recordgrp']  | child::*[@level = 'series'] | child::*[@level = 'fonds']">
                    <xsl:if test="child::*/ead:unittitle[1]">
                        <fo:bookmark internal-destination="{local:buildID(child::*/ead:unittitle[1])}">
                            <fo:bookmark-title>
                                <xsl:value-of select="local:tagName(child::*/ead:unittitle[1])"/>
                            </fo:bookmark-title>
                        </fo:bookmark>
                    </xsl:if>
                    <!-- Creates a submenu for subfonds, subgrp or subseries -->
                    <xsl:for-each select="child::*[@level = 'subfonds'] | child::*[@level = 'subgrp']  | child::*[@level = 'subseries']">
                        <xsl:if test="child::*/ead:unittitle[1]">
                            <fo:bookmark internal-destination="{local:buildID(child::*/ead:unittitle[1])}">
                                <fo:bookmark-title>
                                    <xsl:value-of select="local:tagName(child::*/ead:unittitle[1])"/>
                                </fo:bookmark-title>
                            </fo:bookmark>
                        </xsl:if>
                    </xsl:for-each>
                </xsl:for-each>
            </xsl:for-each>
        </fo:bookmark-tree>
    </xsl:template>

    <!-- Table of Contents -->
    <xsl:template match="ead:archdesc" mode="toc">
        <fo:block line-height="18pt" margin-top="0.25in">
            <fo:block xsl:use-attribute-sets="h2" id="toc">Table of contents</fo:block>
            <fo:block xsl:use-attribute-sets="section">
                <xsl:if test="ead:did">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:did)}"><xsl:value-of select="local:tagName(ead:did)"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:did)}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:bioghist">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:bioghist[1])}"><xsl:value-of select="local:tagName(ead:bioghist[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:bioghist[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:scopecontent">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:scopecontent[1])}"><xsl:value-of select="local:tagName(ead:scopecontent[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:scopecontent[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:arrangement">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:arrangement[1])}"><xsl:value-of select="local:tagName(ead:arrangement[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:arrangement[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:fileplan">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:fileplan[1])}"><xsl:value-of select="local:tagName(ead:fileplan[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:fileplan[1])}"/>
                    </fo:block>
                </xsl:if>

                <!-- NOTES HEADING  -->
                <xsl:if test="ead:accessrestrict or ead:userestrict or
                              ead:custodhist or ead:accruals or ead:altformavail or ead:acqinfo or
                              ead:processinfo or ead:appraisal or ead:originalsloc or
                              /ead:ead/ead:eadheader/ead:revisiondesc">
                        <fo:block text-align-last="justify">
                            <fo:basic-link internal-destination="adminInfo">Notes</fo:basic-link>
                            <xsl:text>&#160;&#160;</xsl:text>
                            <fo:leader leader-pattern="dots"/>
                            <xsl:text>&#160;&#160;</xsl:text>
                            <fo:page-number-citation ref-id="adminInfo"/>
                        </fo:block>
                </xsl:if>

                <xsl:if test="ead:controlaccess">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:controlaccess[1])}"><xsl:value-of select="local:tagName(ead:controlaccess[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:controlaccess[1])}"/>
                    </fo:block>
                </xsl:if>

                <xsl:if test="ead:phystech">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:phystech[1])}"><xsl:value-of select="local:tagName(ead:phystech[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:phystech[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:odd and not(ead:odd[@type='publicationStatus'])">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:odd[1])}"><xsl:value-of select="local:tagName(ead:odd[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:odd[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:bibliography">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:bibliography[1])}"><xsl:value-of select="local:tagName(ead:bibliography[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:bibliography[1])}"/>
                    </fo:block>
                </xsl:if>
                <xsl:if test="ead:index">
                    <fo:block text-align-last="justify">
                        <fo:basic-link internal-destination="{local:buildID(ead:index[1])}"><xsl:value-of select="local:tagName(ead:index[1])"/></fo:basic-link>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:leader leader-pattern="dots"/>
                        <xsl:text>&#160;&#160;</xsl:text>
                        <fo:page-number-citation ref-id="{local:buildID(ead:index[1])}"/>
                    </fo:block>
                </xsl:if>

                <!-- Build Container List menu and submenu -->
                <xsl:for-each select="ead:dsc">
                    <xsl:if test="child::*">
                        <fo:block text-align-last="justify">
                            <fo:basic-link internal-destination="{local:buildID(.)}"><xsl:value-of select="local:tagName(.)"/></fo:basic-link>
                            <xsl:text>&#160;&#160;</xsl:text>
                            <fo:leader leader-pattern="dots"/>
                            <xsl:text>&#160;&#160;</xsl:text>
                            <fo:page-number-citation ref-id="{local:buildID(.)}"/>
                        </fo:block>
                    </xsl:if>
                    <!--Creates a submenu for collections, record groups and series and fonds-->
                    <xsl:for-each select="child::*[@level = 'collection']  | child::*[@level = 'recordgrp']  | child::*[@level = 'series'] | child::*[@level = 'fonds']">
                        <fo:block text-align-last="justify" margin-left="8pt">
                            <fo:basic-link internal-destination="{local:buildID(.)}">
                                <xsl:choose>
                                    <xsl:when test="ead:head">
                                        <xsl:apply-templates select="child::*/ead:head"/>
                                    </xsl:when>
                                    <xsl:otherwise>
                                        <xsl:value-of select="child::*/ead:unitid"/>,
                                        <xsl:apply-templates select="child::*/ead:unittitle"/>,
                                        <xsl:apply-templates select="child::*/ead:unitdate" mode="did"/>
                                    </xsl:otherwise>
                                </xsl:choose>
                            </fo:basic-link>
                            <xsl:text>&#160;&#160;</xsl:text>
                            <fo:leader leader-pattern="dots"/>
                            <xsl:text>&#160;&#160;</xsl:text>
                            <fo:page-number-citation ref-id="{local:buildID(.)}"/>
                        </fo:block>
                        <!-- Creates a submenu for subfonds, subgrp or subseries -->
                        <xsl:for-each select="child::*[@level = 'subfonds'] | child::*[@level = 'subgrp']  | child::*[@level = 'subseries']">
                            <fo:block text-align-last="justify" margin-left="16pt">
                                <fo:basic-link internal-destination="{local:buildID(.)}">
                                    <xsl:choose>
                                        <xsl:when test="ead:head">
                                            <xsl:apply-templates select="child::*/ead:head"/>
                                        </xsl:when>
                                        <xsl:otherwise>
                                            <xsl:value-of select="child::*/ead:unitid"/>,
                                            <xsl:apply-templates select="child::*/ead:unittitle"/>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </fo:basic-link>
                                <xsl:text>&#160;&#160;</xsl:text>
                                <fo:leader leader-pattern="dots"/>
                                <xsl:text>&#160;&#160;</xsl:text>
                                <fo:page-number-citation ref-id="{local:buildID(.)}"/>
                            </fo:block>
                        </xsl:for-each>
                    </xsl:for-each>
                </xsl:for-each>
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

            <!-- NOTES SECTION  -->
            <xsl:if test="ead:accessrestrict or ead:userestrict or
                ead:custodhist or ead:accruals or ead:altformavail or ead:acqinfo or
                ead:processinfo or ead:appraisal or ead:originalsloc or
                /ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt or /ead:ead/ead:eadheader/ead:revisiondesc">
                <fo:block xsl:use-attribute-sets="section">
                    <fo:block xsl:use-attribute-sets="h2" id="adminInfo">Notes</fo:block>
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
        <fo:block xsl:use-attribute-sets="h2ID">Summary information</fo:block>
            <!--
                Determines the order in which elements from the archdesc did appear,
                to change the order of appearance change the order of the following
                apply-template statements.
            -->

            <fo:table table-layout="fixed" width="100%">
                <fo:table-column column-width="2in"/>
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
                        <xsl:apply-templates select="ead:physloc" mode="overview"/>
                        <xsl:apply-templates select="ead:langmaterial/ead:language" mode="overview"/>
                        <xsl:call-template name="summaryInfoOtherField">
                            <xsl:with-param name="path" select="//ead:processinfo/ead:date"/>
                            <xsl:with-param name="label" select="'Dates of creation, revision and deletion'"/>
                        </xsl:call-template>
                        <xsl:apply-templates select="ead:note" mode="overview"/>
                </fo:table-body>
            </fo:table>

            <!-- Link back to table of contents -->
            <xsl:call-template name="toc"/>
        </fo:block>
    </xsl:template>

    <!-- Formats children of arcdesc/did -->
    <xsl:template match="ead:repository | ead:origination | ead:unittitle | ead:unitdate | ead:unitid
        | ead:physdesc | ead:physloc | ead:dao | ead:daogrp | ead:langmaterial | ead:materialspec | ead:container
        | ead:abstract | ead:note | ead:langmaterial/ead:language" mode="overview">
        <fo:table-row>
            <fo:table-cell padding-bottom="8pt" padding-right="16pt" text-align="right" font-weight="bold">
                <fo:block>
                <xsl:choose>
                    <!-- Test for label attribute used by origination element -->
                    <xsl:when test="@label">
                        <xsl:value-of select="concat(upper-case(substring(@label,1,1)),substring(@label,2))"></xsl:value-of>
                        <xsl:if test="@type"> [<xsl:value-of select="@type"/>]</xsl:if>
                        <xsl:if test="self::ead:origination">
                            <xsl:choose>
                                <xsl:when test="ead:persname[@role != ''] and contains(ead:persname/@role,' (')">
                                    - <xsl:value-of select="substring-before(ead:persname/@role,' (')"/>
                                </xsl:when>
                                <xsl:when test="ead:persname[@role != '']">
                                    - <xsl:value-of select="ead:persname/@role"/>
                                </xsl:when>
                                <xsl:otherwise/>
                            </xsl:choose>
                        </xsl:if>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="local:tagName(.)"/>
                        <!-- Test for type attribute used by unitdate -->
                        <xsl:if test="@type"> [<xsl:value-of select="@type"/>]</xsl:if>
                    </xsl:otherwise>
                </xsl:choose>:
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-bottom="2pt">
                <fo:block>
                    <xsl:apply-templates/>
                    <xsl:if test="self::ead:unitdate">
                        <xsl:choose>
                            <xsl:when test="@datechar = 'creation'">
                                <xsl:text> (date of creation)</xsl:text>
                            </xsl:when>
                            <xsl:when test="@datechar = 'accumulation'">
                                <xsl:text> (date of accumulation)</xsl:text>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:text> (date of creation)</xsl:text>
                            </xsl:otherwise>
                        </xsl:choose>
                    </xsl:if>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>

    <xsl:template name="summaryInfoOtherField">
        <xsl:param name="path"/>
        <xsl:param name="label"/>
        <fo:table-row>
            <fo:table-cell padding-bottom="8pt" padding-right="16pt" text-align="right" font-weight="bold">
                <fo:block>
                    <xsl:value-of select="$label"/>:
                </fo:block>
            </fo:table-cell>
            <fo:table-cell padding-bottom="2pt">
                <fo:block>
                    <xsl:value-of select="($path)[1]"/>
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>

    <!-- Formats the 'other' notes headings -->
    <!--
        <xsl:apply-templates select="ead:processinfo"/>
        <xsl:apply-templates select="ead:appraisal"/>
        <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:filedesc/ead:publicationstmt"/>
        <xsl:apply-templates select="/ead:ead/ead:eadheader/ead:revisiondesc"/>
    -->
    <xsl:template name="otherNotes">
        <xsl:if test="ead:odd">
            <fo:block xsl:use-attribute-sets="h3ID">Other notes</fo:block>
            <fo:list-block xsl:use-attribute-sets="section">
                <xsl:for-each select="ead:odd">
                    <xsl:variable name="otherNoteHeading">
                        <xsl:if test="current()[@type='levelOfDetail']">Level of detail</xsl:if>
                        <xsl:if test="current()[@type='statusDescription']">Status description</xsl:if>
                        <xsl:if test="current()[@type='descriptionIdentifier']">Description identifier</xsl:if>
                        <xsl:if test="current()[@type='institutionIdentifier']">Institution identifier</xsl:if>
                        <xsl:if test="current()[@type='edition']">Edition</xsl:if>
                        <xsl:if test="current()[@type='physDesc']">Physical description</xsl:if>
                        <xsl:if test="current()[@type='conservation']">Conservation</xsl:if>
                        <xsl:if test="current()[@type='material']">Accompanying material</xsl:if>
                        <xsl:if test="current()[@type='alphanumericDesignation']">Alpha-numeric designations</xsl:if>
                        <xsl:if test="current()[@type='bibSeries']">Publisher's series</xsl:if>
                        <xsl:if test="current()[@type='rights']">Rights</xsl:if>
                        <xsl:if test="current()[@type='general']">General note</xsl:if>
                        <xsl:if test="current()[@type='publicationStatus']">Publication status</xsl:if>
                    </xsl:variable>
                    <xsl:if test="string-length($otherNoteHeading) &gt; 0">
                        <fo:list-item>
                        <fo:list-item-label end-indent="label-end()">
                            <fo:block>&#x2022;</fo:block>
                        </fo:list-item-label>
                        <fo:list-item-body start-indent="body-start()">
                            <fo:block>
                                <!-- We have other <odd> elements used elsewhere, such as title notes.
                                     only print the <odd> elements we've *handled* here in this section. -->
                                <fo:inline text-decoration="underline" font-weight="bold">
                                    <xsl:value-of select="$otherNoteHeading"/>
                                </fo:inline>
                                <xsl:text>: </xsl:text><xsl:value-of select="."/>
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
                <xsl:variable name="otherNoteHeading">
                    <xsl:if test="current()[@type='levelOfDetail']">Level of detail</xsl:if>
                    <xsl:if test="current()[@type='statusDescription']">Status description</xsl:if>
                    <xsl:if test="current()[@type='descriptionIdentifier']">Description identifier</xsl:if>
                    <xsl:if test="current()[@type='institutionIdentifier']">Institution identifier</xsl:if>
                    <xsl:if test="current()[@type='edition']">Edition</xsl:if>
                    <xsl:if test="current()[@type='physDesc']">Physical description</xsl:if>
                    <xsl:if test="current()[@type='conservation']">Conservation</xsl:if>
                    <xsl:if test="current()[@type='material']">Accompanying material</xsl:if>
                    <xsl:if test="current()[@type='alphanumericDesignation']">Alpha-numeric designations</xsl:if>
                    <xsl:if test="current()[@type='bibSeries']">Publisher's series</xsl:if>
                    <xsl:if test="current()[@type='rights']">Rights</xsl:if>
                    <xsl:if test="current()[@type='general']">General note</xsl:if>
                    <xsl:if test="current()[@type='publicationStatus']">Publication status</xsl:if>
                </xsl:variable>
                <xsl:if test="string-length($otherNoteHeading) &gt; 0">
                    <fo:block font-size="12" padding="2" margin="2">
                        <!-- We have other <odd> elements used elsewhere, such as title notes.
                             only print the <odd> elements we've *handled* here in this section. -->
                        <fo:inline text-decoration="underline" margin="2">
                            <xsl:value-of select="$otherNoteHeading"/>
                        </fo:inline>
                        <xsl:text>: </xsl:text><fo:block></fo:block><fo:block margin="4" padding="4"><xsl:value-of select="."/></fo:block>
                    </fo:block>
                </xsl:if>
            </xsl:for-each>
        </xsl:if>
    </xsl:template>

    <!-- Adds space between extents -->
    <xsl:template match="ead:extent"><xsl:apply-templates/>&#160;</xsl:template>

    <!-- Formats children of arcdesc not in administrative or related materials sections-->
    <xsl:template match="ead:bibliography | ead:odd |
        ead:bioghist | ead:scopecontent | ead:fileplan">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block xsl:use-attribute-sets="h2ID"><xsl:value-of select="local:tagName(.)"/></fo:block>
                <xsl:apply-templates/>
            <xsl:call-template name="toc"/>
        </fo:block>
    </xsl:template>

    <!-- Formats children of arcdesc in administrative and related materials sections -->
    <xsl:template match="ead:relatedmaterial | ead:separatedmaterial | ead:accessrestrict |
        ead:userestrict | ead:custodhist | ead:accruals | ead:altformavail | ead:acqinfo |
        ead:processinfo | ead:appraisal | ead:originalsloc | ead:phystech | ead:arrangement |
        ead:otherfindaid">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block xsl:use-attribute-sets="h3ID"><xsl:value-of select="local:tagName(.)"/></fo:block>
                <xsl:apply-templates/>
        </fo:block>
    </xsl:template>

    <!-- Publication statement included in administrative information section -->

    <xsl:template match="ead:publicationstmt"></xsl:template>

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
                    <fo:basic-link external-destination="url('mailto:{.}')" xsl:use-attribute-sets="ref">
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

    <!-- Templates for revision description  -->
    <xsl:template match="ead:revisiondesc">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block xsl:use-attribute-sets="h3ID"><xsl:value-of select="local:tagName(.)"/></fo:block>
            <xsl:if test="ead:change/ead:item"><xsl:value-of select="ead:change/ead:item"/></xsl:if>
            <xsl:if test="ead:change/ead:date">&#160;<xsl:value-of select="ead:change/ead:date"/></xsl:if>
        </fo:block>
    </xsl:template>

    <!-- Formats controlled access terms -->
    <xsl:template match="ead:controlaccess">
        <xsl:if test="child::*">
            <fo:block xsl:use-attribute-sets="section">
                <fo:block xsl:use-attribute-sets="h2ID"><xsl:value-of select="local:tagName(.)"/></fo:block>
                <fo:list-block xsl:use-attribute-sets="smp">
                    <xsl:apply-templates/>
                </fo:list-block>
            </fo:block>
        </xsl:if>
    </xsl:template>
    <xsl:template match="ead:controlaccess/child::*">
        <fo:list-item>
            <fo:list-item-label end-indent="label-end()">
                <fo:block>&#x2022;</fo:block>
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
            <fo:block xsl:use-attribute-sets="h2ID"><xsl:value-of select="local:tagName(.)"/></fo:block>
            <xsl:apply-templates select="child::*[not(self::ead:indexentry)]"/>
            <fo:list-block xsl:use-attribute-sets="smp">
            <xsl:apply-templates select="ead:indexentry"/>
            </fo:list-block>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:indexentry">
        <fo:list-item>
            <fo:list-item-label  end-indent="label-end()">
                <fo:block>&#x2022;</fo:block>
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
        <fo:block xsl:use-attribute-sets="h4ID"><xsl:apply-templates/></fo:block>
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
        <fo:table-column column-width="{@colwidth}"/>
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
        <fo:table-row xsl:use-attribute-sets="th"><xsl:apply-templates/></fo:table-row>
    </xsl:template>
    <xsl:template match="ead:row">
        <fo:table-row><xsl:apply-templates/></fo:table-row>
    </xsl:template>
    <xsl:template match="ead:entry">
        <fo:table-cell xsl:use-attribute-sets="tdBorder">
            <fo:block xsl:use-attribute-sets="smp">
                <xsl:apply-templates/>
            </fo:block>
        </fo:table-cell>
    </xsl:template>

    <!--Bibref citation  inline, if there is a parent element.-->
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
                    <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref"><xsl:apply-templates/></fo:basic-link>
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
            <fo:list-item-label  end-indent="label-end()">
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
                        <xsl:otherwise>&#x2022;</xsl:otherwise>
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
            <fo:list-item-label  end-indent="label-end()">
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

    <!-- Formats list as table if list has listhead element  -->
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


    <!-- Formats notestmt and notes -->
    <xsl:template match="ead:notestmt">
        <fo:block xsl:use-attribute-sets="section">
            <fo:block xsl:use-attribute-sets="h4ID">Note</fo:block>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:note">
        <xsl:choose>
            <xsl:when test="parent::ead:notestmt">
                <xsl:apply-templates/>
            </xsl:when>

            <!-- Atom: Do not write "Note:" header when it's a note inside creation event: -->
            <xsl:when test="parent::ead:event">
                <xsl:apply-templates/>
            </xsl:when>

            <xsl:otherwise>
                <xsl:choose>
                    <xsl:when test="@label"><fo:block xsl:use-attribute-sets="h4ID"><xsl:value-of select="@label"/></fo:block><xsl:apply-templates/></xsl:when>
                    <xsl:otherwise>
                        <fo:block xsl:use-attribute-sets="h4ID">
                            Note
                        </fo:block>
                        <xsl:apply-templates/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!-- Formats legalstatus -->
    <xsl:template match="ead:legalstatus">
        <fo:block xsl:use-attribute-sets="smp">
            <fo:inline font-weight="bold"><xsl:value-of select="local:tagName(.)"/>:&#160;</fo:inline>
            <xsl:apply-templates/>
        </fo:block>
    </xsl:template>

    <!-- General headings -->
    <!-- Children of the archdesc are handled by the local:tagName function -->
    <xsl:template match="ead:head[parent::*/parent::ead:archdesc]"/>
    <!-- All other headings -->
    <xsl:template match="ead:head">
        <fo:block xsl:use-attribute-sets="h4" id="{local:buildID(parent::*)}"><xsl:apply-templates/></fo:block>
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
                <xsl:when test="@*:title"><xsl:value-of select="@*:title"/></xsl:when>
                <xsl:otherwise><xsl:value-of select="@*:entityref"/></xsl:otherwise>
            </xsl:choose>
        </fo:basic-link>
    </xsl:template>
    <xsl:template match="ead:extptr[@*:href]">
        <fo:basic-link external-destination="url('{@*:href}')" xsl:use-attribute-sets="ref">
            <xsl:choose>
                <xsl:when test="@*:title"><xsl:value-of select="@*:title"/></xsl:when>
                <xsl:otherwise><xsl:value-of select="@*:href"/></xsl:otherwise>
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
            <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'bolddoublequote'] | *[@altrender = 'bolddoublequote']">
        <fo:inline font-weight="bold"><xsl:if test="preceding-sibling::*">
            &#160;</xsl:if>"<xsl:apply-templates/>"</fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'boldsinglequote'] | *[@altrender = 'boldsinglequote']">
        <fo:inline font-weight="bold"><xsl:if test="preceding-sibling::*">
            &#160;</xsl:if>'<xsl:apply-templates/>'</fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'bolditalic'] | *[@altrender = 'bolditalic']">
        <fo:inline font-weight="bold" font-style="italic">
            <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'boldsmcaps'] | *[@altrender = 'boldsmcaps']">
        <fo:inline font-weight="bold" font-variant="small-caps">
            <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'boldunderline'] | *[@altrender = 'boldunderline']">
        <fo:inline font-weight="bold" border-bottom="1pt solid #000">
            <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'doublequote'] | *[@altrender = 'doublequote']">
        <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>"<xsl:apply-templates/>" </xsl:template>
    <xsl:template match="*[@render = 'italic'] | *[@altrender = 'italic']">
        <fo:inline font-style="italic">
            <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>
            <xsl:apply-templates/>
        </fo:inline>
    </xsl:template>
    <xsl:template match="*[@render = 'singlequote'] | *[@altrender = 'singlequote']">
        <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>'<xsl:apply-templates/>' </xsl:template>
    <xsl:template match="*[@render = 'smcaps'] | *[@altrender = 'smcaps']">
        <fo:inline font-variant="small-caps">
            <xsl:if test="preceding-sibling::*"> &#160;</xsl:if>
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
        <fo:block xsl:use-attribute-sets="smp"><xsl:apply-templates/></fo:block>
    </xsl:template>
    <xsl:template match="ead:lb"><fo:block/></xsl:template>
    <xsl:template match="ead:blockquote">
        <fo:block margin="4pt 18pt"><xsl:apply-templates/></fo:block>
    </xsl:template>
    <xsl:template match="ead:emph[not(@render)]"><fo:inline font-style="italic"><xsl:apply-templates/></fo:inline></xsl:template>

    <!-- Collection Inventory (dsc) templates -->
    <xsl:template match="ead:archdesc/ead:dsc">
        <xsl:if test="*">
            <fo:block xsl:use-attribute-sets="sectionTable" >
                <fo:block xsl:use-attribute-sets="h2ID"><xsl:value-of select="local:tagName(.)"/></fo:block>
                <fo:table table-layout="fixed" space-after="12pt" width="100%" font-size="10pt"
                    border-bottom="1pt solid #000" border-top="1pt solid #000"
                    border-left="1pt solid #000" border-right="1pt solid #000" text-align="left"
                    border-after-width.length="1pt" border-after-width.conditionality="retain"
                    border-before-width.length="1pt" border-before-width.conditionality="retain">

                    <fo:table-column column-number="1" column-width="1.5in"
                                     border-bottom="1px solid #000" border-top="1pt solid #000"
                                     border-left="1pt solid #000" border-right="1pt solid #000"/>

                    <fo:table-column column-number="2" column-width="2.5in"
                                     border-bottom="1px solid #000" border-top="1pt solid #000"/>
                    <fo:table-column column-number="3" column-width="1.1in"
                                     border-bottom="1px solid #000" border-top="1pt solid #000"/>
                    <fo:table-column column-number="4" column-width="1.3in"
                                     border-bottom="1px solid #000" border-top="1pt solid #000"/>
                    <fo:table-column column-number="5" column-width="1in"
                                     border-bottom="1px solid #000" border-top="1pt solid #000"/>
                    <fo:table-body start-indent="0in">
                        <xsl:if test="child::*[@level][1][@level='item' or @level='file' or @level='otherlevel']">
                            <xsl:call-template name="tableHeaders"/>
                        </xsl:if>
                        <xsl:apply-templates select="*[not(self::ead:head)]"/>
                    </fo:table-body>
                </fo:table>
            </fo:block>
        </xsl:if>
    </xsl:template>

    <!--
        Calls the clevel template passes the calculates the level of current component in xml tree and passes it to clevel template via the level parameter
        Adds a row to with a link to top if level series
    -->
    <xsl:template match="ead:c | ead:c01 | ead:c02 | ead:c03 | ead:c04 | ead:c05 |
        ead:c06 | ead:c07 | ead:c08 | ead:c09 | ead:c10 | ead:c11 | ead:c12">
        <xsl:variable name="findClevel" select="count(ancestor::*[not(ead:dsc or ead:archdesc or ead:ead)])"/>

        <xsl:call-template name="clevel">
            <xsl:with-param name="level" select="$findClevel"></xsl:with-param>
        </xsl:call-template>
        <xsl:if test="@level='series'">
            <fo:table-row>
                <fo:table-cell number-columns-spanned="5"><xsl:call-template name="toc"/></fo:table-cell>
            </fo:table-row>
        </xsl:if>
    </xsl:template>
    <!--This is a named template that processes all the components  -->
    <xsl:template name="clevel">
        <!-- Establishes which level is being processed in order to provided indented displays. -->
        <xsl:param name="level" />
        <xsl:variable name="clevelMargin" select="'2pt'">
            <!-- Uncomment for indented series descriptions
            <xsl:choose>
                <xsl:when test="$level = 1">4pt</xsl:when>
                <xsl:when test="$level = 2">12pt</xsl:when>
                <xsl:when test="$level = 3">20pt</xsl:when>
                <xsl:when test="$level = 4">28pt</xsl:when>
                <xsl:when test="$level = 5">36pt</xsl:when>
                <xsl:when test="$level = 6">44pt</xsl:when>
                <xsl:when test="$level = 7">52pt</xsl:when>
                <xsl:when test="$level = 8">60pt</xsl:when>
                <xsl:when test="$level = 9">68pt</xsl:when>
                <xsl:when test="$level = 10">74pt</xsl:when>
                <xsl:when test="$level = 11">82pt</xsl:when>
                <xsl:when test="$level = 12">90pt</xsl:when>
            </xsl:choose>
            -->
        </xsl:variable>
        <xsl:choose>
            <!--Formats Series and Groups  -->
            <xsl:when test="@level='subcollection' or @level='subgrp' or @level='series'
                or @level='subseries' or @level='collection'or @level='fonds' or
                @level='recordgrp' or @level='subfonds' or @level='class' or
                (@level='otherlevel' and not(child::ead:did/ead:container))">

                <fo:table-row background-color="#ffffff" border-bottom="1pt solid #000"
                              border-top="1pt solid #000"  text-align="left">

                    <fo:table-cell margin-left="{$clevelMargin}" padding-top="4pt">
                        <xsl:attribute name="number-columns-spanned">5</xsl:attribute>

                        <xsl:apply-templates select="ead:did" mode="dscSeriesTitle"/>
                        <xsl:apply-templates select="ead:did" mode="dscSeries"/>
                        <xsl:value-of select="self::bioghist"/>
                        <!--<xsl:apply-templates select="child::*[not(ead:did) and not(self::ead:did)]" mode="dsc"/>-->
                    </fo:table-cell>
                </fo:table-row>
                <fo:table-row border-top="1px solid #000" border-bottom="1pt solid #000" margin-top="3pt">
                    <fo:table-cell margin-left="{$clevelMargin}" padding-top="4pt" number-columns-spanned="5">
                        <fo:block text-align="center" xsl:use-attribute-sets="h4">
                            File / item list
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>

                <!-- Adds column headings if series/subseries is followed by an item -->
                <xsl:if test="child::*[@level][1][@level='item' or @level='file' or @level='otherlevel']">
                    <xsl:call-template name="tableHeaders"/>
                </xsl:if>
            </xsl:when>

            <xsl:otherwise>
                <fo:table-row border-top="1px solid #000" padding-left="2pt" margin-left="2pt">
                    <fo:table-cell>
                        <!-- We insert zero width spaces before dashes so text-wrap works,
                        this is a kludge due to a bug where text-wrap doesn't work, see here:
                        http://xmlgraphics.apache.org/fop/faq.html#cells-overflow -->
                        <fo:block>
                            <xsl:value-of select="ead:did/ead:unitid"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block><xsl:apply-templates select="ead:did" mode="dsc"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block><xsl:value-of select="ead:did/ead:unitdate"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block><xsl:value-of select="(ead:accessrestrict/p | ead:accessrestrict)"/></fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block><xsl:value-of select="ead:did/ead:container"/></fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </xsl:otherwise>
        </xsl:choose>
        <!-- Calls child components -->
        <xsl:apply-templates select="ead:c | ead:c01 | ead:c02 | ead:c03 | ead:c04 | ead:c05 | ead:c06 | ead:c07 | ead:c08 | ead:c09 | ead:c10 | ead:c11 | ead:c12"/>
    </xsl:template>

    <!-- Named template to generate table headers -->
    <xsl:template name="tableHeaders">
        <fo:table-row background-color="#f7f7f9" padding-left="2pt" margin-left="2pt">
            <fo:table-cell number-columns-spanned="1">
                <fo:block>
                    Ref code
                </fo:block>
            </fo:table-cell>
            <fo:table-cell number-columns-spanned="1">
                <fo:block>
                    Title
                </fo:block>
            </fo:table-cell>
            <fo:table-cell number-columns-spanned="1">
                <fo:block>
                    Dates
                </fo:block>
            </fo:table-cell>
            <fo:table-cell number-columns-spanned="1">
                <fo:block>
                    Access status
                </fo:block>
            </fo:table-cell>
            <fo:table-cell number-columns-spanned="1">
                <fo:block>
                    Container
                </fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
    <!-- Formats did containers -->
    <xsl:template match="ead:container">
        <fo:table-cell>
            <fo:block margin="4pt 0 2pt 0" font-size="12"><xsl:value-of select="."/></fo:block>
        </fo:table-cell>
    </xsl:template>

    <!-- Series titles -->
    <xsl:template match="ead:did" mode="dscSeriesTitle">
        <fo:block font-weight="bold" font-size="14" margin-bottom="0" margin-top="0" id="{local:buildID(parent::*)}">
            <!-- Uncomment the following to add 'Series' to series titles  -->
            <xsl:if test="ead:unitid">
                <xsl:choose>
                    <xsl:when test="../@level='series'">Series <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='subseries'">Subseries <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='subsubseries'">Sub-Subseries <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='collection'">Collection <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='subcollection'">Subcollection <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='fonds'">Fonds <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='subfonds'">Subfonds <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='recordgrp'">Record group <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:when test="../@level='subgrp'">Subgroup <xsl:value-of select="ead:unitid"/>: </xsl:when>
                    <xsl:otherwise>
                        <xsl:if test="../@otherlevel">
                            <xsl:call-template name="ucfirst">
                                <xsl:with-param name="value" select="../@otherlevel"/>
                            </xsl:call-template>
                            <xsl:text> </xsl:text>
                            <!--<xsl:value-of select="../@otherlevel"/><xsl:text> </xsl:text>-->
                        </xsl:if>
                        <xsl:value-of select="ead:unitid"/>: </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
            <xsl:apply-templates select="ead:unittitle"/>
            <!--<xsl:if test="(string-length(ead:unittitle[1]) &gt; 1) and (string-length(ead:unitdate[1]) &gt; 1)"></xsl:if>
            <xsl:apply-templates select="ead:unitdate" mode="did"/>-->
        </fo:block>
    </xsl:template>

    <!-- Series child elements -->
    <xsl:template match="ead:did" mode="dscSeries">
        <fo:block margin-left="2pt" margin-bottom="4pt" margin-top="0" font-size="9">
            <!--Atom: <xsl:apply-templates select="ead:repository" mode="dsc"/> -->
            <xsl:apply-templates select="ead:origination" mode="dsc"/>
            <xsl:apply-templates select="ead:unitdate" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:scopecontent[1]" mode="dsc"/>
            <xsl:apply-templates select="ead:physdesc" mode="dsc"/>
            <xsl:apply-templates select="ead:physloc" mode="dsc"/>
            <xsl:apply-templates select="ead:langmaterial" mode="dsc"/>
            <xsl:apply-templates select="ead:materialspec" mode="dsc"/>
            <xsl:apply-templates select="ead:abstract" mode="dsc"/>
            <xsl:apply-templates select="ead:note" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:controlaccess" mode="dsc"/>
        </fo:block>

        <fo:block margin-left="2pt" margin-bottom="4pt" margin-top="0" font-size="9">
            <!-- Try to handle EAD tags in RAD order... -->
            <xsl:call-template name="titleNotes"/>
            <xsl:apply-templates select="following-sibling::ead:phystech" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:acqinfo" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:arrangement" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:originalsloc" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:altformavail" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:accessrestrict" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:userestrict" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:otherfindaid" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:relatedmaterial" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:accruals" mode="dsc"/>
            <xsl:call-template name="otherNotesSeries"/>
        </fo:block>
    </xsl:template>

    <!-- Single row unittitles and all other clevel elements -->
    <xsl:template match="ead:did" mode="dsc">
        <fo:block margin-bottom="0">
            <xsl:if test="parent::ead:c[@level]">
                <xsl:call-template name="ucfirst">
                    <xsl:with-param name="value" select="parent::ead:c/@level"/>
                </xsl:call-template>
            </xsl:if>
            - <xsl:apply-templates select="ead:unittitle"/>
        </fo:block>
    </xsl:template>

    <!-- Formats unitdates -->
    <xsl:template match="ead:unitdate[@type = 'bulk']" mode="did">
        (<xsl:apply-templates/>)
    </xsl:template>
    <xsl:template match="ead:unitdate" mode="did"><xsl:apply-templates/></xsl:template>

    <xsl:template match="ead:langmaterial" mode="dsc">
        <fo:block xsl:use-attribute-sets="smpDsc">
            <fo:inline text-decoration="underline">
                <xsl:value-of select="local:tagName(.)"/>
            </fo:inline>:<fo:block></fo:block>

            <xsl:for-each select="ead:language">
                <fo:block margin="4pt">
                    <xsl:value-of select="."/>
                </fo:block>
            </xsl:for-each>
        </fo:block>
    </xsl:template>

    <!-- Special formatting for elements in the collection inventory list -->
    <xsl:template match="ead:repository | ead:origination | ead:unitdate | ead:unitid | ead:scopecontent
        | ead:physdesc | ead:physloc | ead:materialspec | ead:container
        | ead:abstract | ead:note | ead:phystech | ead:acqinfo | ead:arrangement | ead:originalsloc
        | ead:altformavail | ead:accessrestrict | ead:userestrict | ead:otherfindaid | ead:relatedmaterial
        | ead:accruals | ead:odd" mode="dsc">

        <fo:block xsl:use-attribute-sets="smpDsc">
            <fo:inline text-decoration="underline">
            <xsl:choose>
                <!-- Test for label attribute used by origination element -->
                <xsl:when test="@label">
                    <xsl:value-of select="concat(upper-case(substring(@label,1,1)),substring(@label,2))"></xsl:value-of>
                    <xsl:if test="@type"> [<xsl:value-of select="@type"/>]</xsl:if>
                    <xsl:if test="self::ead:origination">
                        <xsl:choose>
                            <xsl:when test="ead:persname[@role != ''] and contains(ead:persname/@role,' (')">
                                - <xsl:value-of select="substring-before(ead:persname/@role,' (')"/>
                            </xsl:when>
                            <xsl:when test="ead:persname[@role != '']">
                                - <xsl:value-of select="ead:persname/@role"/>
                            </xsl:when>
                            <xsl:otherwise/>
                        </xsl:choose>
                    </xsl:if>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="local:tagName(.)"/>
                </xsl:otherwise>
            </xsl:choose></fo:inline>: <xsl:apply-templates/>
            <xsl:if test="@datechar"> (<xsl:value-of select="@datechar"/>)</xsl:if>
            <xsl:if test="name()='unitdate'"> (date of creation)</xsl:if>
        </fo:block>
    </xsl:template>
    <!--
    <xsl:template match="ead:relatedmaterial | ead:separatedmaterial | ead:accessrestrict | ead:userestrict |
        ead:custodhist | ead:accruals | ead:altformavail | ead:acqinfo |
        ead:processinfo | ead:appraisal | ead:originalsloc" mode="dsc">
        <xsl:if test="child::*">
            <fo:block xsl:use-attribute-sets="smpDsc">
                <fo:inline text-decoration="underline"><xsl:value-of select="local:tagName(.)"/>:</fo:inline>
            <xsl:apply-templates select="child::*[not(ead:head)]"/>
            </fo:block>
        </xsl:if>
    </xsl:template>
    -->
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
                     <xsl:value-of select="concat(upper-case(substring(@label,1,1)),substring(@label,2))"></xsl:value-of>
                 </xsl:when>
                 <xsl:otherwise>
                     <xsl:value-of select="local:tagName(.)"/>
                 </xsl:otherwise>
             </xsl:choose></fo:inline>:
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
</xsl:stylesheet>
