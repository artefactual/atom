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
                <fo:bookmark-title>Collection holdings</fo:bookmark-title>
            </fo:bookmark>
        </xsl:if>
        <!--Creates descendants bookmarks-->
        <!-- if not an item and not a file -->
        <xsl:for-each select="//ead:c">
            <xsl:if test="not(@level = 'item') and not(@level = 'file')">
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
            </xsl:if>
        </xsl:for-each>
    </xsl:template>
    <!-- Build c-level table of contents menu and submenu -->
    <xsl:template match="ead:dsc" mode="toc">
        <xsl:if test="child::*">
            <fo:block text-align-last="justify">
                <fo:basic-link internal-destination="{local:buildID(.)}">Collection holdings</fo:basic-link>
                <xsl:text>  </xsl:text>
                <fo:leader leader-pattern="dots"/>
                <xsl:text>  </xsl:text>
                <fo:page-number-citation ref-id="{local:buildID(.)}"/>
            </fo:block>
        </xsl:if>
        <!--Creates descendants hierarchy-->
        <xsl:for-each select="//ead:c">
            <xsl:if test="@level='series' or @level='subseries'">
                <xsl:variable name="indention" select="(count(ancestor-or-self::*) - 3) * 8"/>
                <fo:block text-align-last="justify" margin-left="{$indention}pt">
                    <fo:basic-link internal-destination="{local:buildID(.)}">
                        <xsl:choose>
                            <xsl:when test="ead:head">
                                <xsl:apply-templates select="child::*/ead:head"/>
                            </xsl:when>
                            <xsl:otherwise>
                                <xsl:value-of select="(child::*/ead:unitid)[1]"/>
                                <xsl:if test="(child::*/ead:unitid)[1] and (child::*/ead:unittitle[not(ead:bibseries)])[1]">
                                    <xsl:text>, </xsl:text>
                                </xsl:if>
                                <xsl:apply-templates select="(child::*/ead:unittitle[not(ead:bibseries)])[1]"/>
                                <xsl:if test="(child::*/ead:unitdate)[1]">
                                    (<xsl:apply-templates select="(child::*/ead:unitdate)[1]" mode="did"/>)
                                </xsl:if>
                            </xsl:otherwise>
                        </xsl:choose>
                    </fo:basic-link>
                    <xsl:text>  </xsl:text>
                    <fo:leader leader-pattern="dots"/>
                    <xsl:text>  </xsl:text>
                    <fo:page-number-citation ref-id="{local:buildID(.)}"/>
                </fo:block>
            </xsl:if>
        </xsl:for-each>
    </xsl:template>
    <!-- Collection Inventory (dsc) templates -->
    <xsl:template match="ead:archdesc/ead:dsc">
        <fo:block xsl:use-attribute-sets="sectionTable" margin-top="10pt">
            <fo:block role="H2" xsl:use-attribute-sets="h2ID">Collection holdings</fo:block>
            <xsl:apply-templates select="*[not(self::ead:head)]"/>
        </fo:block>
    </xsl:template>
    <!--
        Calls the clevel template passes the calculates the level of current
        component in xml tree and passes it to clevel template via the level
        parameter. Adds a row to with a link to top if level series
    -->
    <xsl:template match="ead:c | ead:c01 | ead:c02 | ead:c03 | ead:c04 | ead:c05 | ead:c06 | ead:c07 | ead:c08 | ead:c09 | ead:c10 | ead:c11 | ead:c12">
        <xsl:variable name="findClevel" select="count(ancestor::*[not(ead:dsc or ead:archdesc or ead:ead)])"/>
        <xsl:call-template name="clevel">
            <xsl:with-param name="level" select="$findClevel"/>
        </xsl:call-template>
    </xsl:template>
    <!--This is a named template that processes all the components -->
    <xsl:template name="clevel">
        <xsl:param name="level"/>
        <fo:block border-bottom="1pt dotted #333" keep-together.within-page="always">
            <fo:block margin-left="{($level - 1)*16+4}pt" font-size="10pt" text-align="left">
                <xsl:apply-templates select="ead:did" mode="dscSeriesTitle"/>
                <xsl:apply-templates select="ead:did" mode="dscSeries"/>
                <xsl:value-of select="self::bioghist"/>
            </fo:block>
        </fo:block>
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
            <fo:block margin="4pt 0 2pt 0" font-size="12">
                <xsl:value-of select="."/>
            </fo:block>
        </fo:table-cell>
    </xsl:template>
    <!-- Series titles -->
    <xsl:template match="ead:did" mode="dscSeriesTitle">
        <fo:block role="H3" font-weight="bold" font-size="14" margin-bottom="5pt" margin-top="20pt" id="{local:buildID(parent::*)}">
            <xsl:choose>
                <xsl:when test="../@level='series'">Series: </xsl:when>
                <xsl:when test="../@level='subseries'">Subseries: </xsl:when>
                <xsl:when test="../@level='subsubseries'">Sub-Subseries: </xsl:when>
                <xsl:when test="../@level='collection'">Collection: </xsl:when>
                <xsl:when test="../@level='subcollection'">Subcollection: </xsl:when>
                <xsl:when test="../@level='fonds'">Fonds: </xsl:when>
                <xsl:when test="../@level='subfonds'">Subfonds: </xsl:when>
                <xsl:when test="../@level='recordgrp'">Record group: </xsl:when>
                <xsl:when test="../@level='subgrp'">Subgroup: </xsl:when>
                <xsl:when test="../@level='file'">File: </xsl:when>
                <xsl:when test="../@level='item'">Item: </xsl:when>
                <xsl:otherwise>
                    <xsl:if test="../@otherlevel">
                        <xsl:call-template name="ucfirst">
                            <xsl:with-param name="value" select="../@otherlevel"/>
                        </xsl:call-template>
                        <xsl:text>: </xsl:text>
                    </xsl:if>
                </xsl:otherwise>
            </xsl:choose>
            <xsl:if test="ead:unitid">
                <xsl:value-of select="(ead:unitid)[1]"/>
                <xsl:text> - </xsl:text>
            </xsl:if>
            <xsl:apply-templates select="(ead:unittitle[not(ead:bibseries)])[1]"/>
            <!--<xsl:if test="(string-length(ead:unittitle[1]) &gt; 1) and (string-length(ead:unitdate[1]) &gt; 1)"></xsl:if>
            <xsl:apply-templates select="ead:unitdate" mode="did"/>-->
        </fo:block>
    </xsl:template>
    <!-- Series child elements -->
    <xsl:template match="ead:did" mode="dscSeries">
        <fo:block margin-left="2pt" margin-bottom="4pt" font-size="9">
            <!--Atom: <xsl:apply-templates select="ead:repository" mode="dsc"/> -->
            <xsl:apply-templates select="ead:origination" mode="dsc"/>
            <xsl:apply-templates select="ead:unittitle[not(ead:bibseries)]" mode="dsc"/>
            <xsl:apply-templates select="ead:unitid" mode="dsc"/>
            <xsl:apply-templates select="ead:unitdate" mode="dsc"/>
            <xsl:apply-templates select="following-sibling::ead:scopecontent[1]" mode="dsc"/>
            <xsl:apply-templates select="ead:physdesc" mode="dsc"/>
            <xsl:apply-templates select="ead:physloc" mode="dsc"/>
            <xsl:apply-templates select="ead:langmaterial" mode="dsc"/>
            <xsl:apply-templates select="ead:materialspec" mode="dsc"/>
            <xsl:apply-templates select="ead:abstract" mode="dsc"/>
            <xsl:apply-templates select="ead:note" mode="dsc"/>
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
            <xsl:apply-templates select="following-sibling::ead:controlaccess" mode="dsc"/>
        </fo:block>
    </xsl:template>
    <!-- Single row unittitles and all other clevel elements -->
    <xsl:template match="ead:did" mode="dsc">
        <fo:block margin-bottom="0"><xsl:if test="parent::ead:c[@level]"><xsl:call-template name="ucfirst"><xsl:with-param name="value" select="parent::ead:c/@level"/></xsl:call-template></xsl:if>
            - <xsl:apply-templates select="ead:unittitle"/>
        </fo:block>
        <fo:block margin-bottom="0pt" margin-top="0" font-size="12">
            <xsl:apply-templates select="ead:origination" mode="dsc"/>
            <xsl:apply-templates select="ead:physloc" mode="dsc"/>
            <xsl:apply-templates select="ead:langmaterial" mode="dsc"/>
            <xsl:apply-templates select="ead:materialspec" mode="dsc"/>
            <xsl:apply-templates select="ead:abstract" mode="dsc"/>
            <xsl:apply-templates select="ead:note" mode="dsc"/>
        </fo:block>
    </xsl:template>
    <!-- Formats unitdates -->
    <xsl:template match="ead:unitdate[@type = 'bulk']" mode="did">
        (<xsl:apply-templates/>)
    </xsl:template>
    <xsl:template match="ead:unitdate" mode="did">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="ead:langmaterial" mode="dsc">
        <fo:block xsl:use-attribute-sets="smpDsc"><fo:inline text-decoration="underline"><xsl:value-of select="local:tagName(.)"/></fo:inline>:<fo:block/><xsl:for-each select="ead:language"><fo:block margin="4pt"><xsl:value-of select="."/></fo:block></xsl:for-each></fo:block>
    </xsl:template>
    <!-- Special formatting for elements in the collection inventory list -->
    <xsl:template match="ead:repository | ead:origination | ead:unittitle | ead:unitdate | ead:unitid | ead:scopecontent | ead:physdesc | ead:physloc | ead:materialspec | ead:container | ead:abstract | ead:note | ead:phystech | ead:acqinfo | ead:arrangement | ead:originalsloc | ead:altformavail | ead:accessrestrict | ead:userestrict | ead:otherfindaid | ead:relatedmaterial | ead:accruals | ead:odd" mode="dsc">
        <fo:block xsl:use-attribute-sets="smpDsc">
            <fo:inline text-decoration="underline">
                <xsl:choose><!-- Test for label attribute used by origination element -->
                    <xsl:when test="@label">
                        <xsl:value-of select="concat(upper-case(substring(@label,1,1)),substring(@label,2))"/>
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
                        <xsl:if test="@type"> [<xsl:value-of select="local:typeLabel(.)"/>]</xsl:if>
                    </xsl:otherwise>
                </xsl:choose>
            </fo:inline>:
            <xsl:apply-templates/>
            <xsl:if test="@datechar"> (<xsl:value-of select="@datechar"/>)</xsl:if>
            <xsl:if test="name()='unitdate'"> (date of creation)</xsl:if>
        </fo:block>
    </xsl:template>
</xsl:stylesheet>
