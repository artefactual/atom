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
    <!-- Calls a stylesheet with local functions and lookup lists for languages and subject authorities -->
    <xsl:include href="{{ app_root }}/lib/task/pdf/helper-functions.xsl"/>
    <xsl:include href="{{ app_root }}/lib/task/pdf/ead-pdf-common.xsl"/>

    <!-- Institution logo on title page -->
    <xsl:template name="logo">
        <fo:block xsl:use-attribute-sets="h1">
            <fo:wrapper role="artifact">
                <fo:external-graphic src="{{ app_root }}/images/pdf-logo.png" width="3.5cm" content-width="scale-to-fit" content-height="scale-to-fit"/>
                <xsl:text> </xsl:text>
            </fo:wrapper>
            <xsl:apply-templates select="(//ead:repository/ead:corpname)[1]"/>
        </fo:block>
    </xsl:template>
    <!-- Build c-level bookmarks -->
    <xsl:template match="ead:dsc" mode="bookmarks">
        <xsl:if test="child::*">
            <fo:bookmark internal-destination="{local:buildID(.)}">
                <fo:bookmark-title>
                    <xsl:value-of select="local:tagName(.)"/>
                </fo:bookmark-title>
            </fo:bookmark>
        </xsl:if>
        <!--Creates a submenu for collections, record groups and series and fonds-->
        <xsl:for-each select="child::*[@level = 'collection'] | child::*[@level = 'recordgrp'] | child::*[@level = 'series'] | child::*[@level = 'fonds']">
            <fo:bookmark internal-destination="{local:buildID(.)}">
                <fo:bookmark-title>
                    <xsl:choose>
                        <xsl:when test="ead:head">
                            <xsl:value-of select="child::*/ead:head[1]"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:value-of select="child::*/ead:unittitle[1]"/>
                        </xsl:otherwise>
                    </xsl:choose>
                </fo:bookmark-title>
            </fo:bookmark>
            <!-- Creates a submenu for subfonds, subgrp or subseries -->
            <xsl:for-each select="child::*[@level = 'subfonds'] | child::*[@level = 'subgrp'] | child::*[@level = 'subseries']">
                <fo:bookmark internal-destination="{local:buildID(.)}">
                    <fo:bookmark-title>
                        <xsl:choose>
                            <xsl:when test="ead:head">
                                <xsl:value-of select="child::*/ead:head[1]"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="child::*/ead:unittitle[1]"/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </fo:bookmark-title>
                </fo:bookmark>
            </xsl:for-each>
        </xsl:for-each>
    </xsl:template>
    <!-- Build c-level table of contents menu and submenu -->
    <xsl:template match="ead:dsc" mode="toc">
        <xsl:if test="child::*">
            <fo:block text-align-last="justify">
                <fo:basic-link internal-destination="{local:buildID(.)}">
                    <xsl:value-of select="local:tagName(.)"/>
                </fo:basic-link>
                <xsl:text>  </xsl:text>
                <fo:leader leader-pattern="dots"/>
                <xsl:text>  </xsl:text>
                <fo:page-number-citation ref-id="{local:buildID(.)}"/>
            </fo:block>
        </xsl:if>
        <!--Creates a submenu for collections, record groups and series and fonds-->
        <xsl:for-each select="child::*[@level = 'collection'] | child::*[@level = 'recordgrp'] | child::*[@level = 'series'] | child::*[@level = 'fonds']">
            <fo:block text-align-last="justify" margin-left="8pt">
                <fo:basic-link internal-destination="{local:buildID(.)}">
                    <xsl:choose>
                        <xsl:when test="ead:head">
                            <xsl:apply-templates select="child::*/ead:head"/>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:if test="child::*/ead:unitid">
                                <xsl:value-of select="child::*/ead:unitid"/>,
                            </xsl:if>
                            <xsl:choose>
                                <xsl:when test="child::*/ead:unitdate">
                                    <xsl:apply-templates select="child::*/ead:unittitle"/>,
                                    <xsl:apply-templates select="child::*/ead:unitdate" mode="did"/>
                                </xsl:when>
                                <xsl:otherwise>
                                    <xsl:apply-templates select="child::*/ead:unittitle"/>
                                </xsl:otherwise>
                            </xsl:choose>
                        </xsl:otherwise>
                    </xsl:choose>
                </fo:basic-link>
                <xsl:text>  </xsl:text>
                <fo:leader leader-pattern="dots"/>
                <xsl:text>  </xsl:text>
                <fo:page-number-citation ref-id="{local:buildID(.)}"/>
            </fo:block>
            <!-- Creates a submenu for subfonds, subgrp or subseries -->
            <xsl:for-each select="child::*[@level = 'subfonds'] | child::*[@level = 'subgrp'] | child::*[@level = 'subseries']">
                <fo:block text-align-last="justify" margin-left="16pt">
                    <fo:basic-link internal-destination="{local:buildID(.)}">
                        <xsl:choose>
                            <xsl:when test="ead:head">
                                <xsl:apply-templates select="child::*/ead:head"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:if test="child::*/ead:unitid">
                                    <xsl:value-of select="child::*/ead:unitid"/>,
                                </xsl:if>
                                <xsl:apply-templates select="child::*/ead:unittitle"/>
                            </xsl:otherwise>
                        </xsl:choose>
                    </fo:basic-link>
                    <xsl:text>  </xsl:text>
                    <fo:leader leader-pattern="dots"/>
                    <xsl:text>  </xsl:text>
                    <fo:page-number-citation ref-id="{local:buildID(.)}"/>
                </fo:block>
            </xsl:for-each>
        </xsl:for-each>
    </xsl:template>
    <!-- Collection Inventory (dsc) templates -->
    <xsl:template match="ead:archdesc/ead:dsc">
        <xsl:if test="*">
            <fo:block xsl:use-attribute-sets="sectionTable">
                <fo:block role="H2" xsl:use-attribute-sets="h2ID">
                    <xsl:value-of select="local:tagName(.)"/>
                </fo:block>
                <fo:table table-layout="fixed" space-after="12pt" width="100%" font-size="10pt" border-bottom="1pt solid #000" border-top="1pt solid #000" border-left="1pt solid #000" border-right="1pt solid #000" text-align="left" border-after-width.length="1pt" border-after-width.conditionality="retain" border-before-width.length="1pt" border-before-width.conditionality="retain">
                    <fo:table-column xmlns:fox="http://xmlgraphics.apache.org/fop/extensions" fox:header="true" column-number="1" column-width="1.25in" border-right="1pt solid #000"/>
                    <fo:table-column column-number="2" column-width="2.75in"/>
                    <fo:table-column column-number="3" column-width="1.3in"/>
                    <fo:table-column column-number="4" column-width="2.1in"/>
                    <xsl:if test="child::*[@level][1][@level='item' or @level='file' or @level='otherlevel']">
                        <fo:table-header>
                            <xsl:call-template name="tableHeaders"/>
                        </fo:table-header>
                    </xsl:if>
                    <fo:table-body start-indent="0in">
                        <xsl:apply-templates select="*[not(self::ead:head)]"/>
                    </fo:table-body>
                </fo:table>
            </fo:block>
        </xsl:if>
    </xsl:template>
    <!--
        Calls the clevel template passes the calculates the level of current
        component in xml tree and passes it to clevel template via the level
        parameter. Adds a row to with a link to top if level series
    -->
    <xsl:template match="ead:c | ead:c01 | ead:c02 | ead:c03 | ead:c04 | ead:c05 | ead:c06 | ead:c07 | ead:c08 | ead:c09 | ead:c10 | ead:c11 | ead:c12">
        <xsl:variable name="findClevel" select="count(ancestor::*[not(ead:dsc | ead:archdesc | ead:ead)])"/>
        <xsl:call-template name="clevel">
            <xsl:with-param name="level" select="$findClevel"/>
        </xsl:call-template>
        <xsl:if test="@level='series'">
            <fo:table-row>
                <fo:table-cell number-columns-spanned="4">
                    <xsl:call-template name="toc"/>
                </fo:table-cell>
            </fo:table-row>
        </xsl:if>
    </xsl:template>
    <!-- This is a named template that processes all the components -->
    <xsl:template name="clevel">
        <!-- Establishes which level is being processed in order to provided indented displays. -->
        <xsl:param name="level"/>
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
            <!--Formats Series and Groups -->
            <xsl:when test="@level='subcollection' or @level='subgrp' or @level='series' or @level='subseries' or @level='collection' or @level='fonds' or @level='recordgrp' or @level='subfonds' or @level='class' or (@level='otherlevel' and not(parent::ead:c[@level='series']))">
                <fo:table-row background-color="#ffffff" border-top="1pt solid #000" text-align="left">
                    <fo:table-cell margin-left="{$clevelMargin}" padding-top="4pt" number-columns-spanned="4">
                        <xsl:apply-templates select="ead:did" mode="dscSeriesTitle"/>
                        <xsl:apply-templates select="ead:did" mode="dscSeries"/>
                        <xsl:value-of select="self::bioghist"/>
                    </fo:table-cell>
                </fo:table-row>
                <!-- Adds column headings if series/subseries is followed by an item -->
                <xsl:if test="child::*[@level][1][@level='item' or @level='file' or @level='otherlevel']">
                    <fo:table-row border-top="1px solid #000" border-bottom="1pt solid #000" margin-top="3pt" keep-with-next="always">
                        <fo:table-cell margin-left="{$clevelMargin}" padding-top="4pt" number-columns-spanned="4">
                            <fo:block role="H4" text-align="center" xsl:use-attribute-sets="h4">
                                File / item list
                            </fo:block>
                        </fo:table-cell>
                    </fo:table-row>
                    <xsl:call-template name="tableHeaders"/>
                </xsl:if>
            </xsl:when>
            <xsl:otherwise>
                <fo:table-row border-top="1px solid #000" padding-left="2pt" margin-left="2pt" keep-with-next="always" keep-together.within-page="always">
                    <fo:table-cell>
                        <fo:block>
                            <!-- To prevent long inventory numbers from bleeding across columns, add zero-width space characters to allow text wrap after hyphens -->
                            <xsl:for-each select="ead:did/ead:unitid">
                                <xsl:value-of select="replace(.,'-','-&#x200b;')"/>
                            </xsl:for-each>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block font-weight="bold">
                            <xsl:apply-templates select="ead:did" mode="dsc"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block>
                            <xsl:value-of select="ead:did/ead:unitdate"/>
                        </fo:block>
                    </fo:table-cell>
                    <fo:table-cell>
                        <fo:block>
                            <xsl:value-of select="ead:did/ead:physdesc"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
                <fo:table-row padding-left="2pt" margin-left="2pt" keep-together.within-page="always">
                    <fo:table-cell/>
                    <fo:table-cell number-columns-spanned="3">
                        <fo:block margin-top="6pt">
                            <xsl:apply-templates select="ead:did" mode="itemDsc"/>
                            <xsl:apply-templates select="ead:scopecontent" mode="itemDsc"/>
                            <xsl:apply-templates select="ead:accessrestrict" mode="itemDsc"/>
                            <xsl:apply-templates select="ead:userestrict" mode="itemDsc"/>
                        </fo:block>
                    </fo:table-cell>
                </fo:table-row>
            </xsl:otherwise>
        </xsl:choose>
        <!-- Calls child components -->
        <xsl:apply-templates select="ead:c | ead:c01 | ead:c02 | ead:c03 | ead:c04 | ead:c05 | ead:c06 | ead:c07 | ead:c08 | ead:c09 | ead:c10 | ead:c11 | ead:c12"/>
    </xsl:template>
    <!-- Named template to generate table headers -->
    <xsl:template name="tableHeaders">
        <fo:table-row background-color="#f7f7f9" padding-left="2pt" margin-left="2pt" role="TH" keep-with-next="always">
            <fo:table-cell>
                <fo:block>Reference code</fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block>Title</fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block>Dates</fo:block>
            </fo:table-cell>
            <fo:table-cell>
                <fo:block>Physical description</fo:block>
            </fo:table-cell>
        </fo:table-row>
    </xsl:template>
    <!-- Series titles -->
    <xsl:template match="ead:did" mode="dscSeriesTitle">
        <fo:block font-weight="bold" font-size="14" margin-bottom="0" margin-top="0" id="{local:buildID(parent::*)}">
            <xsl:if test="ead:unitid">
                <xsl:choose>
                    <xsl:when test="../@level='series'">Series </xsl:when>
                    <xsl:when test="../@level='subseries'">Subseries </xsl:when>
                    <xsl:when test="../@level='subsubseries'">Sub-Subseries </xsl:when>
                    <xsl:when test="../@level='collection'">Collection </xsl:when>
                    <xsl:when test="../@level='subcollection'">Subcollection </xsl:when>
                    <xsl:when test="../@level='fonds'">Fonds </xsl:when>
                    <xsl:when test="../@level='subfonds'">Subfonds </xsl:when>
                    <xsl:when test="../@level='recordgrp'">Record group </xsl:when>
                    <xsl:when test="../@level='subgrp'">Subgroup </xsl:when>
                    <xsl:when test="../@otherlevel">
                        <xsl:value-of select="local:ucfirst(../@otherlevel)"/>
                        <xsl:text> </xsl:text>
                    </xsl:when>
                </xsl:choose>
                <xsl:value-of select="ead:unitid"/>:
            </xsl:if>
            <xsl:apply-templates select="ead:unittitle"/>
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
            <xsl:apply-templates select="ead:container" mode="dsc"/>
            <xsl:apply-templates select="ead:langmaterial" mode="dsc"/>
            <xsl:apply-templates select="ead:materialspec" mode="dsc"/>
            <xsl:apply-templates select="ead:abstract" mode="dsc"/>
            <xsl:apply-templates select="ead:note" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:controlaccess" mode="dsc"/>
        </fo:block>
        <fo:block margin-left="2pt" margin-bottom="4pt" margin-top="0" font-size="9">
            <!-- Try to handle EAD tags in RAD order... -->
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
                <xsl:choose>
                    <xsl:when test="parent::ead:c/@otherlevel">
                        <xsl:value-of select="local:ucfirst(parent::ead:c/@otherlevel)"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:value-of select="local:ucfirst(parent::ead:c/@level)"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:if>
            - <xsl:apply-templates select="ead:unittitle"/>
        </fo:block>
    </xsl:template>
    <xsl:template match="ead:did" mode="itemDsc">
        <xsl:apply-templates select="ead:materialspec" mode="itemDsc"/>
        <xsl:apply-templates select="ead:origination" mode="itemDsc"/>
        <xsl:apply-templates select="ead:note" mode="itemDsc"/>
        <xsl:apply-templates select="ead:container" mode="itemDsc"/>
    </xsl:template>
    <!-- Formats unitdates -->
    <xsl:template match="ead:unitdate[@type = 'bulk']" mode="did">
        (<xsl:apply-templates/>)
    </xsl:template>
    <xsl:template match="ead:unitdate" mode="did">
        <xsl:apply-templates/>
    </xsl:template>
    <!-- Special formatting for Language of material -->
    <xsl:template match="ead:langmaterial" mode="dsc">
        <xsl:if test="ead:language">
            <fo:block xsl:use-attribute-sets="smpDsc">
                <fo:inline text-decoration="underline">
                    <xsl:value-of select="local:tagName(.)"/>
                </fo:inline>:
            </fo:block>
            <fo:list-block xsl:use-attribute-sets="smpDsc">
                <xsl:for-each select="ead:language">
                    <fo:list-item>
                        <fo:list-item-label end-indent="label-end()">
                            <fo:block>•</fo:block>
                        </fo:list-item-label>
                        <fo:list-item-body start-indent="body-start()">
                            <fo:block>
                                <xsl:value-of select="."/>
                            </fo:block>
                        </fo:list-item-body>
                    </fo:list-item>
                </xsl:for-each>
            </fo:list-block>
        </xsl:if>
    </xsl:template>
    <!-- Special formatting for series elements in the collection inventory list -->
    <xsl:template match="ead:repository | ead:origination | ead:unitdate | ead:unitid | ead:scopecontent | ead:physdesc | ead:materialspec | ead:abstract | ead:container | ead:note | ead:phystech | ead:acqinfo | ead:arrangement | ead:originalsloc | ead:altformavail | ead:accessrestrict | ead:userestrict | ead:otherfindaid | ead:relatedmaterial | ead:accruals | ead:odd" mode="dsc">
        <fo:block xsl:use-attribute-sets="smpDsc">
            <fo:inline text-decoration="underline">
                <xsl:apply-templates select="." mode="fieldLabel"/>
            </fo:inline>:
            <xsl:apply-templates select="." mode="fieldValue"/>
        </fo:block>
    </xsl:template>
    <!-- Special formatting for file/item table elements -->
    <xsl:template match="ead:origination | ead:scopecontent | ead:materialspec | ead:container | ead:note | ead:accessrestrict | ead:userestrict" mode="itemDsc">
        <fo:block>
            <fo:inline font-style="italic">
                <xsl:apply-templates select="." mode="fieldLabel"/>
            </fo:inline>:
        </fo:block>
        <fo:block margin="4pt 0 4pt 6pt">
            <xsl:apply-templates select="." mode="fieldValue"/>
        </fo:block>
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
</xsl:stylesheet>
