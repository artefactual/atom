<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ns2="http://www.w3.org/1999/xlink" xmlns:local="http://www.yoursite.org/namespace" xmlns:ead="urn:isbn:1-931666-22-9" version="2.0" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <!--
        *******************************************************************
        *                                                                 *
        * VERSION:          1.0                                           *
        *                                                                 *
        * AUTHOR:           Winona Salesky                                *
        *                   wsalesky@gmail.com                            *
        * MODIFIED BY:      mikeg@artefactual.com                         *
        *                                                                 *
        * DATE:             2013-08-14                                    *
        *                                                                 *
        * ABOUT:            This file has been created for use with       *
        *                   EAD xml files exported from the               *
        *                   ArchivesSpace web application.                *
        *                                                                 *
        *******************************************************************
    -->
    <xsl:strip-space elements="*"/>
    <xsl:output encoding="utf-8" indent="yes"/>
    <!-- A local function to check for element ids and generate an id if no id exists -->
    <xsl:function name="local:buildID">
        <xsl:param name="element"/>
        <xsl:choose>
            <xsl:when test="$element/@id">
                <xsl:value-of select="$element/@id"/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="generate-id($element)"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:function>

    <xsl:param name="smallcase" select="'abcdefghijklmnopqrstuvwxyzàèìòùáéíóúýâêîôûãñõäëïöüÿåæœçðø'" />
    <xsl:param name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZÀÈÌÒÙÁÉÍÓÚÝÂÊÎÔÛÃÑÕÄËÏÖÜŸÅÆŒÇÐØ'" />
    <xsl:template name="uppercase">
        <xsl:param name="value" />
        <xsl:value-of select="translate($value, $smallcase, $uppercase)" />
    </xsl:template>

    <xsl:template name="lowercase">
        <xsl:param name="value" />
        <xsl:value-of select="translate($value, $uppercase, $smallcase)" />
    </xsl:template>

    <xsl:template name="ucfirst">
        <xsl:param name="value" />
        <xsl:call-template name="uppercase">
            <xsl:with-param name="value" select="substring($value, 1, 1)" />
        </xsl:call-template>
        <xsl:call-template name="lowercase">
            <xsl:with-param name="value" select="substring($value, 2)" />
        </xsl:call-template>
    </xsl:template>

    <xsl:function name="local:oddTitleNoteHeadings">
        <xsl:param name="type"/>

        <xsl:if test="$type = 'titleContinuation'">
            <xsl:text>Continuation of title</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleStatRep'">
            <xsl:text>Statements of responsibility</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleParallel'">
            <xsl:text>Parallel titles and other title info</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleSource'">
            <xsl:text>Source of title proper</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleVariation'">
            <xsl:text>Variations in title</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleAttributions'">
            <xsl:text>Attributions and conjectures</xsl:text>
        </xsl:if>
    </xsl:function>

    <!--
        A local function to name all child elements with no head tag.
        Tag names addapted from EAD tag library (http://www.loc.gov/ead/tglib/element_index.html)
    -->
    <xsl:function name="local:tagName">
        <!-- element node as parameter -->
        <xsl:param name="elementNode"/>
        <!-- Name of element -->
        <xsl:variable name="tag" select="name($elementNode)"/>
        <!-- Find element name -->
        <xsl:choose>
            <xsl:when test="$elementNode/ead:head"><xsl:value-of select="$elementNode/ead:head"/></xsl:when>
            <xsl:when test="$tag = 'did'">Summary information</xsl:when>
            <xsl:when test="$tag = 'abstract'">Abstract</xsl:when>
            <xsl:when test="$tag = 'accruals'">Accruals</xsl:when>
            <xsl:when test="$tag = 'acqinfo'">Immediate source of acquisition</xsl:when>
            <xsl:when test="$tag = 'address'">Address</xsl:when>
            <xsl:when test="$tag = 'altformavail'">Alternative form available</xsl:when>
            <xsl:when test="$tag = 'appraisal'">Appraisal information</xsl:when>
            <xsl:when test="$tag = 'arc'">Arc</xsl:when>
            <xsl:when test="$tag = 'archref'">Archival reference</xsl:when>
            <xsl:when test="$tag = 'arrangement'">Arrangement</xsl:when>
            <xsl:when test="$tag = 'author'">Author</xsl:when>
            <xsl:when test="$tag = 'bibref'">Bibliographic reference</xsl:when>
            <xsl:when test="$tag = 'bibseries'">Bibliographic series</xsl:when>
            <xsl:when test="$tag = 'bibliography'">Bibliography</xsl:when>

            <!-- AtoM: Test if the bioghist is from a person/family/corp, set heading accordingly -->
            <xsl:when test="$tag = 'bioghist'">Administrative history / Biographical sketch</xsl:when>

            <xsl:when test="$tag = 'change'">Change</xsl:when>
            <xsl:when test="$tag = 'chronlist'">Chronology list</xsl:when>
            <xsl:when test="$tag = 'accessrestrict'">Restrictions on access</xsl:when>
            <xsl:when test="$tag = 'userestrict'">Conditions governing use</xsl:when>
            <xsl:when test="$tag = 'controlaccess'">Access points</xsl:when>
            <xsl:when test="$tag = 'corpname'">Corporate name</xsl:when>
            <xsl:when test="$tag = 'creation'">Creation</xsl:when>
            <xsl:when test="$tag = 'custodhist'">Custodial history</xsl:when>
            <xsl:when test="$tag = 'date'">Date</xsl:when>
            <xsl:when test="$tag = 'descgrp'">Description group</xsl:when>
            <xsl:when test="$tag = 'dsc'">Series descriptions</xsl:when>
            <xsl:when test="$tag = 'descrules'">Descriptive rules</xsl:when>
            <xsl:when test="$tag = 'dao'">Digital object</xsl:when>
            <xsl:when test="$tag = 'daodesc'">Digital object description</xsl:when>
            <xsl:when test="$tag = 'daogrp'">Digital object group</xsl:when>
            <xsl:when test="$tag = 'daoloc'">Digital object location</xsl:when>
            <xsl:when test="$tag = 'dimensions'">Dimensions</xsl:when>
            <xsl:when test="$tag = 'edition'">Edition</xsl:when>
            <xsl:when test="$tag = 'editionstmt'">Edition statement</xsl:when>
            <xsl:when test="$tag = 'event'">Event</xsl:when>
            <xsl:when test="$tag = 'eventgrp'">Event group</xsl:when>
            <xsl:when test="$tag = 'expan'">Expansion</xsl:when>
            <xsl:when test="$tag = 'extptr'">Extended pointer</xsl:when>
            <xsl:when test="$tag = 'extptrloc'">Extended pointer location</xsl:when>
            <xsl:when test="$tag = 'extref'">Extended reference</xsl:when>
            <xsl:when test="$tag = 'extrefloc'">Extended reference location</xsl:when>
            <xsl:when test="$tag = 'extent'">Extent</xsl:when>
            <xsl:when test="$tag = 'famname'">Family name</xsl:when>
            <xsl:when test="$tag = 'filedesc'">File description</xsl:when>
            <xsl:when test="$tag = 'fileplan'">File plan</xsl:when>
            <xsl:when test="$tag = 'frontmatter'">Front matter</xsl:when>
            <xsl:when test="$tag = 'function'">Function</xsl:when>
            <xsl:when test="$tag = 'genreform'">Genre/Physical characteristic</xsl:when>
            <xsl:when test="$tag = 'geogname'">Geographic name</xsl:when>
            <xsl:when test="$tag = 'imprint'">Imprint</xsl:when>
            <xsl:when test="$tag = 'index'">Index</xsl:when>
            <xsl:when test="$tag = 'indexentry'">Index entry</xsl:when>
            <xsl:when test="$tag = 'item'">Item</xsl:when>
            <xsl:when test="$tag = 'language'">Language</xsl:when>
            <xsl:when test="$tag = 'langmaterial'">Language of the material</xsl:when>
            <xsl:when test="$tag = 'langusage'">Language usage</xsl:when>
            <xsl:when test="$tag = 'legalstatus'">Legal status</xsl:when>
            <xsl:when test="$tag = 'linkgrp'">Linking group</xsl:when>
            <xsl:when test="$tag = 'originalsloc'">Location of originals</xsl:when>
            <xsl:when test="$tag = 'materialspec'">Material specific details</xsl:when>
            <xsl:when test="$tag = 'name'">Name</xsl:when>
            <xsl:when test="$tag = 'namegrp'">Name group</xsl:when>
            <xsl:when test="$tag = 'note'">Note</xsl:when>
            <xsl:when test="$tag = 'notestmt'">Note statement</xsl:when>
            <xsl:when test="$tag = 'occupation'">Occupation</xsl:when>
            <xsl:when test="$tag = 'origination'">Creator</xsl:when>
            <xsl:when test="$tag = 'odd'">
                <!-- Atom: Choose prefix to note: -->
                <xsl:choose>
                    <xsl:when test="$elementNode[@type='publicationStatus']">Publication status</xsl:when>
                    <xsl:otherwise>Other descriptive data</xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="$tag = 'otherfindaid'">Finding aids</xsl:when>
            <xsl:when test="$tag = 'persname'">Personal name</xsl:when>
            <xsl:when test="$tag = 'phystech'">Physical condition</xsl:when>
            <xsl:when test="$tag = 'physdesc'">Physical description</xsl:when>
            <xsl:when test="$tag = 'physfacet'">Physical facet</xsl:when>
            <xsl:when test="$tag = 'physloc'">Physical location</xsl:when>
            <xsl:when test="$tag = 'ptr'">Pointer</xsl:when>
            <xsl:when test="$tag = 'ptrgrp'">Pointer group</xsl:when>
            <xsl:when test="$tag = 'ptrloc'">Pointer location</xsl:when>
            <xsl:when test="$tag = 'prefercite'">Preferred citation</xsl:when>
            <xsl:when test="$tag = 'processinfo'">Processing information</xsl:when>
            <xsl:when test="$tag = 'profiledesc'">Profile description</xsl:when>
            <xsl:when test="$tag = 'publicationstmt'">Publication statement</xsl:when>
            <xsl:when test="$tag = 'publisher'">Publisher</xsl:when>
            <xsl:when test="$tag = 'ref'">Reference</xsl:when>
            <xsl:when test="$tag = 'refloc'">Reference location</xsl:when>
            <xsl:when test="$tag = 'relatedmaterial'">Related material</xsl:when>
            <xsl:when test="$tag = 'repository'">Repository</xsl:when>
            <xsl:when test="$tag = 'resource'">Resource</xsl:when>
            <xsl:when test="$tag = 'revisiondesc'">Revision description</xsl:when>
            <xsl:when test="$tag = 'runner'">Runner</xsl:when>
            <xsl:when test="$tag = 'scopecontent'">Scope and content</xsl:when>
            <xsl:when test="$tag = 'separatedmaterial'">Separated material</xsl:when>
            <xsl:when test="$tag = 'seriesstmt'">Series statement</xsl:when>
            <xsl:when test="$tag = 'sponsor'">Sponsor</xsl:when>
            <xsl:when test="$tag = 'subject'">Subject</xsl:when>
            <xsl:when test="$tag = 'subarea'">Subordinate area</xsl:when>
            <xsl:when test="$tag = 'subtitle'">Subtitle</xsl:when>
            <xsl:when test="$tag = 'div'">Text division</xsl:when>
            <xsl:when test="$tag = 'title'">Title</xsl:when>
            <xsl:when test="$tag = 'unittitle'">Title</xsl:when>
            <xsl:when test="$tag = 'unitdate'">Date</xsl:when>
            <xsl:when test="$tag = 'unitid'">ID</xsl:when>
            <xsl:when test="$tag = 'titlepage'">Title page</xsl:when>
            <xsl:when test="$tag = 'titleproper'">Title proper of the finding aid</xsl:when>
            <xsl:when test="$tag = 'titlestmt'">Title statement</xsl:when>
            <!-- eac-cpf fields -->
            <xsl:when test="$tag = 'identity'">Name(s)</xsl:when>
            <xsl:when test="$tag = 'description'">Description</xsl:when>
            <xsl:when test="$tag = 'relations'">Relations</xsl:when>
            <xsl:when test="$tag = 'structureOrGenealogy'">Structure or genealogy</xsl:when>
            <xsl:when test="$tag = 'localDescription'">Local description</xsl:when>
            <xsl:when test="$tag= 'generalContext'">General context</xsl:when>
            <xsl:when test="$tag= 'alternativeSet'">Alternative set</xsl:when>
            <xsl:when test="$tag= 'functions'">Functions</xsl:when>
            <xsl:when test="$tag= 'biogHist'">Biography or history</xsl:when>

        </xsl:choose>
    </xsl:function>

    <!--
        A local function to parse ISO dates into more readable dates.
        Takes a date formatted like this: 2009-11-18T10:16-0500
        Returns: November 18, 2009
    -->
    <xsl:function name="local:parseDate">
        <xsl:param name="dateString"/>
        <xsl:variable name="month">
            <xsl:choose>
                <xsl:when test="substring($dateString,6,2) = '01'">January</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '02'">February</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '03'">March</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '04'">April</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '05'">May</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '06'">June</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '07'">July</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '08'">August</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '09'">September</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '10'">October</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '11'">November</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '12'">December</xsl:when>
            </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="concat($month,' ',substring($dateString,9,2),', ',substring($dateString,1,4))"/>
    </xsl:function>

    <!--
        Prints out full language name from abbreviation.
        List based on the ISO 639-2b three-letter language codes (http://www.loc.gov/standards/iso639-2/php/code_list.php).
    -->
    <xsl:template match="ead:language">
        <xsl:param name="prefix"/>
        <fo:block linefeed-treatment="preserve">
            <xsl:variable name="lod" select="'Language of description: '"/>
            <xsl:variable name="break">&#10;</xsl:variable>
            <xsl:choose>
                <xsl:when test="@langcode = 'No_linguistic_content'">No linguistic content</xsl:when>
                <xsl:when test="@langcode = 'und'"><xsl:value-of select="concat($lod, 'Undetermined', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'abk'"><xsl:value-of select="concat($lod, 'Abkhaz', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ace'"><xsl:value-of select="concat($lod, 'Achinese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ach'"><xsl:value-of select="concat($lod, 'Acoli', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ada'"><xsl:value-of select="concat($lod, 'Adangme', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ady'"><xsl:value-of select="concat($lod, 'Adygei', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aar'"><xsl:value-of select="concat($lod, 'Afar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'afh'"><xsl:value-of select="concat($lod, 'Afrihili', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'afr'"><xsl:value-of select="concat($lod, 'Afrikaans', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'afa'"><xsl:value-of select="concat($lod, 'Afroasiatic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aka'"><xsl:value-of select="concat($lod, 'Akan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'akk'"><xsl:value-of select="concat($lod, 'Akkadian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'alb'"><xsl:value-of select="concat($lod, 'Albanian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ale'"><xsl:value-of select="concat($lod, 'Aleut', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'alg'"><xsl:value-of select="concat($lod, 'Algonquian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tut'"><xsl:value-of select="concat($lod, 'Altaic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'amh'"><xsl:value-of select="concat($lod, 'Amharic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'apa'"><xsl:value-of select="concat($lod, 'Apache languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ara'"><xsl:value-of select="concat($lod, 'Arabic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arg'"><xsl:value-of select="concat($lod, 'Aragonese Spanish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arc'"><xsl:value-of select="concat($lod, 'Aramaic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arp'"><xsl:value-of select="concat($lod, 'Arapaho', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arw'"><xsl:value-of select="concat($lod, 'Arawak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arm'"><xsl:value-of select="concat($lod, 'Armenian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'art'"><xsl:value-of select="concat($lod, 'Artificial (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'asm'"><xsl:value-of select="concat($lod, 'Assamese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ath'"><xsl:value-of select="concat($lod, 'Athapascan (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aus'"><xsl:value-of select="concat($lod, 'Australian languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'map'"><xsl:value-of select="concat($lod, 'Austronesian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ava'"><xsl:value-of select="concat($lod, 'Avaric', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ave'"><xsl:value-of select="concat($lod, 'Avestan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'awa'"><xsl:value-of select="concat($lod, 'Awadhi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aym'"><xsl:value-of select="concat($lod, 'Aymara', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aze'"><xsl:value-of select="concat($lod, 'Azerbaijani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ast'"><xsl:value-of select="concat($lod, 'Bable', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ban'"><xsl:value-of select="concat($lod, 'Balinese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bat'"><xsl:value-of select="concat($lod, 'Baltic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bal'"><xsl:value-of select="concat($lod, 'Baluchi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bam'"><xsl:value-of select="concat($lod, 'Bambara', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bai'"><xsl:value-of select="concat($lod, 'Bamileke languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bad'"><xsl:value-of select="concat($lod, 'Banda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bnt'"><xsl:value-of select="concat($lod, 'Bantu (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bas'"><xsl:value-of select="concat($lod, 'Basa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bak'"><xsl:value-of select="concat($lod, 'Bashkir', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'baq'"><xsl:value-of select="concat($lod, 'Basque', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'btk'"><xsl:value-of select="concat($lod, 'Batak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bej'"><xsl:value-of select="concat($lod, 'Beja', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bel'"><xsl:value-of select="concat($lod, 'Belarusian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bem'"><xsl:value-of select="concat($lod, 'Bemba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ben'"><xsl:value-of select="concat($lod, 'Bengali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ber'"><xsl:value-of select="concat($lod, 'Berber (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bho'"><xsl:value-of select="concat($lod, 'Bhojpuri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bih'"><xsl:value-of select="concat($lod, 'Bihari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bik'"><xsl:value-of select="concat($lod, 'Bikol', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bis'"><xsl:value-of select="concat($lod, 'Bislama', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bos'"><xsl:value-of select="concat($lod, 'Bosnian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bra'"><xsl:value-of select="concat($lod, 'Braj', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bre'"><xsl:value-of select="concat($lod, 'Breton', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bug'"><xsl:value-of select="concat($lod, 'Bugis', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bul'"><xsl:value-of select="concat($lod, 'Bulgarian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bua'"><xsl:value-of select="concat($lod, 'Buriat', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bur'"><xsl:value-of select="concat($lod, 'Burmese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cad'"><xsl:value-of select="concat($lod, 'Caddo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'car'"><xsl:value-of select="concat($lod, 'Carib', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cat'"><xsl:value-of select="concat($lod, 'Catalan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cau'"><xsl:value-of select="concat($lod, 'Caucasian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ceb'"><xsl:value-of select="concat($lod, 'Cebuano', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cel'"><xsl:value-of select="concat($lod, 'Celtic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cai'"><xsl:value-of select="concat($lod, 'Central American Indian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chg'"><xsl:value-of select="concat($lod, 'Chagatai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cmc'"><xsl:value-of select="concat($lod, 'Chamic languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cha'"><xsl:value-of select="concat($lod, 'Chamorro', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'che'"><xsl:value-of select="concat($lod, 'Chechen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chr'"><xsl:value-of select="concat($lod, 'Cherokee', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chy'"><xsl:value-of select="concat($lod, 'Cheyenne', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chb'"><xsl:value-of select="concat($lod, 'Chibcha', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chi'"><xsl:value-of select="concat($lod, 'Chinese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chn'"><xsl:value-of select="concat($lod, 'Chinook jargon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chp'"><xsl:value-of select="concat($lod, 'Chipewyan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cho'"><xsl:value-of select="concat($lod, 'Choctaw', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chu'"><xsl:value-of select="concat($lod, 'Church Slavic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chv'"><xsl:value-of select="concat($lod, 'Chuvash', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cop'"><xsl:value-of select="concat($lod, 'Coptic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cor'"><xsl:value-of select="concat($lod, 'Cornish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cos'"><xsl:value-of select="concat($lod, 'Corsican', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cre'"><xsl:value-of select="concat($lod, 'Cree', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mus'"><xsl:value-of select="concat($lod, 'Creek', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'crp'"><xsl:value-of select="concat($lod, 'Creoles and Pidgins(Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cpe'"><xsl:value-of select="concat($lod, 'Creoles and Pidgins, English-based (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cpf'"><xsl:value-of select="concat($lod, 'Creoles and Pidgins, French-based (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cpp'"><xsl:value-of select="concat($lod, 'Creoles and Pidgins, Portuguese-based (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'crh'"><xsl:value-of select="concat($lod, 'Crimean Tatar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'scr'"><xsl:value-of select="concat($lod, 'Croatian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cus'"><xsl:value-of select="concat($lod, 'Cushitic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cze'"><xsl:value-of select="concat($lod, 'Czech', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dak'"><xsl:value-of select="concat($lod, 'Dakota', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dan'"><xsl:value-of select="concat($lod, 'Danish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dar'"><xsl:value-of select="concat($lod, 'Dargwa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'day'"><xsl:value-of select="concat($lod, 'Dayak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'del'"><xsl:value-of select="concat($lod, 'Delaware', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'din'"><xsl:value-of select="concat($lod, 'Dinka', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'div'"><xsl:value-of select="concat($lod, 'Divehi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'doi'"><xsl:value-of select="concat($lod, 'Dogri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dgr'"><xsl:value-of select="concat($lod, 'Dogrib', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dra'"><xsl:value-of select="concat($lod, 'Dravidian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dua'"><xsl:value-of select="concat($lod, 'Duala', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dut'"><xsl:value-of select="concat($lod, 'Dutch', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dum'"><xsl:value-of select="concat($lod, 'Dutch, Middle (ca. 1050-1350)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dyu'"><xsl:value-of select="concat($lod, 'Dyula', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dzo'"><xsl:value-of select="concat($lod, 'Dzongkha', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bin'"><xsl:value-of select="concat($lod, 'Edo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'efi'"><xsl:value-of select="concat($lod, 'Efik', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'egy'"><xsl:value-of select="concat($lod, 'Egyptian (Ancient)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'eka'"><xsl:value-of select="concat($lod, 'Ekajuk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'elx'"><xsl:value-of select="concat($lod, 'Elamite', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'eng'"><xsl:value-of select="concat($lod, 'English', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'enm'"><xsl:value-of select="concat($lod, 'English, Middle (1100-1500)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ang'"><xsl:value-of select="concat($lod, 'English, Old (ca.450-1100)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'epo'"><xsl:value-of select="concat($lod, 'Esperanto', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'est'"><xsl:value-of select="concat($lod, 'Estonian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gez'"><xsl:value-of select="concat($lod, 'Ethiopic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ewe'"><xsl:value-of select="concat($lod, 'Ewe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ewo'"><xsl:value-of select="concat($lod, 'Ewondo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fan'"><xsl:value-of select="concat($lod, 'Fang', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fat'"><xsl:value-of select="concat($lod, 'Fanti', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fao'"><xsl:value-of select="concat($lod, 'Faroese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fij'"><xsl:value-of select="concat($lod, 'Fijian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fin'"><xsl:value-of select="concat($lod, 'Finnish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fiu'"><xsl:value-of select="concat($lod, 'Finno-Ugrian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fon'"><xsl:value-of select="concat($lod, 'Fon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fre'"><xsl:value-of select="concat($lod, 'French', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'frm'"><xsl:value-of select="concat($lod, 'French, Middle (ca.1400-1600)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fro'"><xsl:value-of select="concat($lod, 'French, Old (ca.842-1400)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fry'"><xsl:value-of select="concat($lod, 'Frisian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fur'"><xsl:value-of select="concat($lod, 'Friulian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ful'"><xsl:value-of select="concat($lod, 'Fula', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gaa'"><xsl:value-of select="concat($lod, 'Gã', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'glg'"><xsl:value-of select="concat($lod, 'Galician', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lug'"><xsl:value-of select="concat($lod, 'Ganda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gay'"><xsl:value-of select="concat($lod, 'Gayo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gba'"><xsl:value-of select="concat($lod, 'Gbaya', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'geo'"><xsl:value-of select="concat($lod, 'Georgian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ger'"><xsl:value-of select="concat($lod, 'German', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gmh'"><xsl:value-of select="concat($lod, 'German, Middle High (ca.1050-1500)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'goh'"><xsl:value-of select="concat($lod, 'German, Old High (ca.750-1050)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gem'"><xsl:value-of select="concat($lod, 'Germanic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gil'"><xsl:value-of select="concat($lod, 'Gilbertese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gon'"><xsl:value-of select="concat($lod, 'Gondi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gor'"><xsl:value-of select="concat($lod, 'Gorontalo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'got'"><xsl:value-of select="concat($lod, 'Gothic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'grb'"><xsl:value-of select="concat($lod, 'Grebo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'grc'"><xsl:value-of select="concat($lod, 'Greek, Ancient (to 1453)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gre'"><xsl:value-of select="concat($lod, 'Greek, Modern (1453-)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'grn'"><xsl:value-of select="concat($lod, 'Guarani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'guj'"><xsl:value-of select="concat($lod, 'Gujarati', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gwi'"><xsl:value-of select="concat($lod, 'Gwichin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hai'"><xsl:value-of select="concat($lod, 'Haida', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hat'"><xsl:value-of select="concat($lod, 'Haitian French Creole', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hau'"><xsl:value-of select="concat($lod, 'Hausa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'haw'"><xsl:value-of select="concat($lod, 'Hawaiian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'heb'"><xsl:value-of select="concat($lod, 'Hebrew', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'her'"><xsl:value-of select="concat($lod, 'Herero', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hil'"><xsl:value-of select="concat($lod, 'Hiligaynon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'him'"><xsl:value-of select="concat($lod, 'Himachali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hin'"><xsl:value-of select="concat($lod, 'Hindi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hmo'"><xsl:value-of select="concat($lod, 'Hiri Motu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hit'"><xsl:value-of select="concat($lod, 'Hittite', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hmn'"><xsl:value-of select="concat($lod, 'Hmong', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hun'"><xsl:value-of select="concat($lod, 'Hungarian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hup'"><xsl:value-of select="concat($lod, 'Hupa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iba'"><xsl:value-of select="concat($lod, 'Iban', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ice'"><xsl:value-of select="concat($lod, 'Icelandic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ido'"><xsl:value-of select="concat($lod, 'Ido', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ibo'"><xsl:value-of select="concat($lod, 'Igbo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ijo'"><xsl:value-of select="concat($lod, 'Ijo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ilo'"><xsl:value-of select="concat($lod, 'Iloko', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smn'"><xsl:value-of select="concat($lod, 'Inari Sami', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'inc'"><xsl:value-of select="concat($lod, 'Indic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ine'"><xsl:value-of select="concat($lod, 'Indo-European (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ind'"><xsl:value-of select="concat($lod, 'Indonesian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'inh'"><xsl:value-of select="concat($lod, 'Ingush', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ina'"><xsl:value-of select="concat($lod, 'Interlingua (International Auxiliary Language Association)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ile'"><xsl:value-of select="concat($lod, 'Interlingue', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iku'"><xsl:value-of select="concat($lod, 'Inuktitut', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ipk'"><xsl:value-of select="concat($lod, 'Inupiaq', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ira'"><xsl:value-of select="concat($lod, 'Iranian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gle'"><xsl:value-of select="concat($lod, 'Irish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mga'"><xsl:value-of select="concat($lod, 'Irish, Middle (ca.1110-1550)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sga'"><xsl:value-of select="concat($lod, 'Irish, Old (to 1100)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iro'"><xsl:value-of select="concat($lod, 'Iroquoian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ita'"><xsl:value-of select="concat($lod, 'Italian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jpn'"><xsl:value-of select="concat($lod, 'Japanese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jav'"><xsl:value-of select="concat($lod, 'Javanese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jrb'"><xsl:value-of select="concat($lod, 'Judeo-Arabic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jpr'"><xsl:value-of select="concat($lod, 'Judeo-Persian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kbd'"><xsl:value-of select="concat($lod, 'Kabardian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kab'"><xsl:value-of select="concat($lod, 'Kabyle', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kac'"><xsl:value-of select="concat($lod, 'Kachin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kal'"><xsl:value-of select="concat($lod, 'Kalâtdlisut', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'xal'"><xsl:value-of select="concat($lod, 'Kalmyk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kam'"><xsl:value-of select="concat($lod, 'Kamba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kan'"><xsl:value-of select="concat($lod, 'Kannada', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kau'"><xsl:value-of select="concat($lod, 'Kanuri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kaa'"><xsl:value-of select="concat($lod, 'Kara-Kalpak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kar'"><xsl:value-of select="concat($lod, 'Karen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kas'"><xsl:value-of select="concat($lod, 'Kashmiri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kaw'"><xsl:value-of select="concat($lod, 'Kawi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kaz'"><xsl:value-of select="concat($lod, 'Kazakh', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kha'"><xsl:value-of select="concat($lod, 'Khasi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'khm'"><xsl:value-of select="concat($lod, 'Khmer', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'khi'"><xsl:value-of select="concat($lod, 'Khoisan (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kho'"><xsl:value-of select="concat($lod, 'Khotanese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kik'"><xsl:value-of select="concat($lod, 'Kikuyu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kmb'"><xsl:value-of select="concat($lod, 'Kimbundu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kin'"><xsl:value-of select="concat($lod, 'Kinyarwanda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kom'"><xsl:value-of select="concat($lod, 'Komi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kon'"><xsl:value-of select="concat($lod, 'Kongo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kok'"><xsl:value-of select="concat($lod, 'Konkani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kor'"><xsl:value-of select="concat($lod, 'Korean', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kpe'"><xsl:value-of select="concat($lod, 'Kpelle', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kro'"><xsl:value-of select="concat($lod, 'Kru (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kua'"><xsl:value-of select="concat($lod, 'Kuanyama', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kum'"><xsl:value-of select="concat($lod, 'Kumyk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kur'"><xsl:value-of select="concat($lod, 'Kurdish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kru'"><xsl:value-of select="concat($lod, 'Kurukh', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kos'"><xsl:value-of select="concat($lod, 'Kusaie', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kut'"><xsl:value-of select="concat($lod, 'Kutenai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kir'"><xsl:value-of select="concat($lod, 'Kyrgyz', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lad'"><xsl:value-of select="concat($lod, 'Ladino', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lah'"><xsl:value-of select="concat($lod, 'Lahnda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lam'"><xsl:value-of select="concat($lod, 'Lamba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lao'"><xsl:value-of select="concat($lod, 'Lao', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lat'"><xsl:value-of select="concat($lod, 'Latin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lav'"><xsl:value-of select="concat($lod, 'Latvian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ltz'"><xsl:value-of select="concat($lod, 'Letzeburgesch', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lez'"><xsl:value-of select="concat($lod, 'Lezgian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lim'"><xsl:value-of select="concat($lod, 'Limburgish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lin'"><xsl:value-of select="concat($lod, 'Lingala', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lit'"><xsl:value-of select="concat($lod, 'Lithuanian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nds'"><xsl:value-of select="concat($lod, 'Low German', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'loz'"><xsl:value-of select="concat($lod, 'Lozi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lub'"><xsl:value-of select="concat($lod, 'Luba-Katanga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lua'"><xsl:value-of select="concat($lod, 'Luba-Lulua', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lui'"><xsl:value-of select="concat($lod, 'Luiseño', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smj'"><xsl:value-of select="concat($lod, 'Lule Sami', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lun'"><xsl:value-of select="concat($lod, 'Lunda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'luo'"><xsl:value-of select="concat($lod, 'Luo (Kenya and Tanzania)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lus'"><xsl:value-of select="concat($lod, 'Lushai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mac'"><xsl:value-of select="concat($lod, 'Macedonian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mad'"><xsl:value-of select="concat($lod, 'Madurese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mag'"><xsl:value-of select="concat($lod, 'Magahi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mai'"><xsl:value-of select="concat($lod, 'Maithili', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mak'"><xsl:value-of select="concat($lod, 'Makasar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mlg'"><xsl:value-of select="concat($lod, 'Malagasy', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'may'"><xsl:value-of select="concat($lod, 'Malay', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mal'"><xsl:value-of select="concat($lod, 'Malayalam', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mlt'"><xsl:value-of select="concat($lod, 'Maltese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mnc'"><xsl:value-of select="concat($lod, 'Manchu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mdr'"><xsl:value-of select="concat($lod, 'Mandar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'man'"><xsl:value-of select="concat($lod, 'Mandingo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mni'"><xsl:value-of select="concat($lod, 'Manipuri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mno'"><xsl:value-of select="concat($lod, 'Manobo languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'glv'"><xsl:value-of select="concat($lod, 'Manx', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mao'"><xsl:value-of select="concat($lod, 'Maori', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arn'"><xsl:value-of select="concat($lod, 'Mapuche', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mar'"><xsl:value-of select="concat($lod, 'Marathi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chm'"><xsl:value-of select="concat($lod, 'Mari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mah'"><xsl:value-of select="concat($lod, 'Marshallese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mwr'"><xsl:value-of select="concat($lod, 'Marwari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mas'"><xsl:value-of select="concat($lod, 'Masai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'myn'"><xsl:value-of select="concat($lod, 'Mayan languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'men'"><xsl:value-of select="concat($lod, 'Mende', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mic'"><xsl:value-of select="concat($lod, 'Micmac', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'min'"><xsl:value-of select="concat($lod, 'Minangkabau', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mis'"><xsl:value-of select="concat($lod, 'Miscellaneous languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'moh'"><xsl:value-of select="concat($lod, 'Mohawk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mol'"><xsl:value-of select="concat($lod, 'Moldavian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mkh'"><xsl:value-of select="concat($lod, 'Mon-Khmer (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lol'"><xsl:value-of select="concat($lod, 'Mongo-Nkundu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mon'"><xsl:value-of select="concat($lod, 'Mongolian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mos'"><xsl:value-of select="concat($lod, 'Mooré', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mul'"><xsl:value-of select="concat($lod, 'Multiple languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mun'"><xsl:value-of select="concat($lod, 'Munda (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nah'"><xsl:value-of select="concat($lod, 'Nahuatl', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nau'"><xsl:value-of select="concat($lod, 'Nauru', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nav'"><xsl:value-of select="concat($lod, 'Navajo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nbl'"><xsl:value-of select="concat($lod, 'Ndebele (South Africa)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nde'"><xsl:value-of select="concat($lod, 'Ndebele (Zimbabwe)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ndo'"><xsl:value-of select="concat($lod, 'Ndonga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nap'"><xsl:value-of select="concat($lod, 'Neapolitan Italian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nep'"><xsl:value-of select="concat($lod, 'Nepali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'new'"><xsl:value-of select="concat($lod, 'Newari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nia'"><xsl:value-of select="concat($lod, 'Nias', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nic'"><xsl:value-of select="concat($lod, 'Niger-Kordofanian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ssa'"><xsl:value-of select="concat($lod, 'Nilo-Saharan (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'niu'"><xsl:value-of select="concat($lod, 'Niuean', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nog'"><xsl:value-of select="concat($lod, 'Nogai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nai'"><xsl:value-of select="concat($lod, 'North American Indian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sme'"><xsl:value-of select="concat($lod, 'Northern Sami', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nso'"><xsl:value-of select="concat($lod, 'Northern Sotho', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nor'"><xsl:value-of select="concat($lod, 'Norwegian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nob'"><xsl:value-of select="concat($lod, 'Norwegian Bokmål', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nno'"><xsl:value-of select="concat($lod, 'Norwegian Nynorsk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nub'"><xsl:value-of select="concat($lod, 'Nubian languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nym'"><xsl:value-of select="concat($lod, 'Nyamwezi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nya'"><xsl:value-of select="concat($lod, 'Nyanja', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nyn'"><xsl:value-of select="concat($lod, 'Nyankole', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nyo'"><xsl:value-of select="concat($lod, 'Nyoro', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nzi'"><xsl:value-of select="concat($lod, 'Nzima', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oci'"><xsl:value-of select="concat($lod, 'Occitan (post-1500)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oji'"><xsl:value-of select="concat($lod, 'Ojibwa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'non'"><xsl:value-of select="concat($lod, 'Old Norse', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'peo'"><xsl:value-of select="concat($lod, 'Old Persian (ca.600-400 B.C.)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ori'"><xsl:value-of select="concat($lod, 'Oriya', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'orm'"><xsl:value-of select="concat($lod, 'Oromo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'osa'"><xsl:value-of select="concat($lod, 'Osage', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oss'"><xsl:value-of select="concat($lod, 'Ossetic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oto'"><xsl:value-of select="concat($lod, 'Otomian languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pal'"><xsl:value-of select="concat($lod, 'Pahlavi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pau'"><xsl:value-of select="concat($lod, 'Palauan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pli'"><xsl:value-of select="concat($lod, 'Pali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pam'"><xsl:value-of select="concat($lod, 'Pampanga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pag'"><xsl:value-of select="concat($lod, 'Pangasinan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pan'"><xsl:value-of select="concat($lod, 'Panjabi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pap'"><xsl:value-of select="concat($lod, 'Papiamento', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'paa'"><xsl:value-of select="concat($lod, 'Papuan (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'per'"><xsl:value-of select="concat($lod, 'Persian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'phi'"><xsl:value-of select="concat($lod, 'Philippine (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'phn'"><xsl:value-of select="concat($lod, 'Phoenician', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pol'"><xsl:value-of select="concat($lod, 'Polish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pon'"><xsl:value-of select="concat($lod, 'Ponape', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'por'"><xsl:value-of select="concat($lod, 'Portuguese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pra'"><xsl:value-of select="concat($lod, 'Prakrit languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pro'"><xsl:value-of select="concat($lod, 'Provençal (to 1500)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pus'"><xsl:value-of select="concat($lod, 'Pushto', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'que'"><xsl:value-of select="concat($lod, 'Quechua', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'roh'"><xsl:value-of select="concat($lod, 'Raeto-Romance', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'raj'"><xsl:value-of select="concat($lod, 'Rajasthani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rap'"><xsl:value-of select="concat($lod, 'Rapanui', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rar'"><xsl:value-of select="concat($lod, 'Rarotongan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'qaa-qtz'"><xsl:value-of select="concat($lod, 'Reserved for local user', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'roa'"><xsl:value-of select="concat($lod, 'Romance (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rom'"><xsl:value-of select="concat($lod, 'Romani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rum'"><xsl:value-of select="concat($lod, 'Romanian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'run'"><xsl:value-of select="concat($lod, 'Rundi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rus'"><xsl:value-of select="concat($lod, 'Russian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sal'"><xsl:value-of select="concat($lod, 'Salishan languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sam'"><xsl:value-of select="concat($lod, 'Samaritan Aramaic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smi'"><xsl:value-of select="concat($lod, 'Sami', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smo'"><xsl:value-of select="concat($lod, 'Samoan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sad'"><xsl:value-of select="concat($lod, 'Sandawe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sag'"><xsl:value-of select="concat($lod, 'Sango (Ubangi Creole)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'san'"><xsl:value-of select="concat($lod, 'Sanskrit', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sat'"><xsl:value-of select="concat($lod, 'Santali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'srd'"><xsl:value-of select="concat($lod, 'Sardinian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sas'"><xsl:value-of select="concat($lod, 'Sasak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sco'"><xsl:value-of select="concat($lod, 'Scots', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gla'"><xsl:value-of select="concat($lod, 'Scottish Gaelic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sel'"><xsl:value-of select="concat($lod, 'Selkup', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sem'"><xsl:value-of select="concat($lod, 'Semitic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'scc'"><xsl:value-of select="concat($lod, 'Serbian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'srr'"><xsl:value-of select="concat($lod, 'Serer', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'shn'"><xsl:value-of select="concat($lod, 'Shan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sna'"><xsl:value-of select="concat($lod, 'Shona', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iii'"><xsl:value-of select="concat($lod, 'Sichuan Yi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sid'"><xsl:value-of select="concat($lod, 'Sidamo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sgn'"><xsl:value-of select="concat($lod, 'Sign languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bla'"><xsl:value-of select="concat($lod, 'Siksika', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'snd'"><xsl:value-of select="concat($lod, 'Sindhi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sin'"><xsl:value-of select="concat($lod, 'Sinhalese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sit'"><xsl:value-of select="concat($lod, 'Sino-Tibetan (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sio'"><xsl:value-of select="concat($lod, 'Siouan (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sms'"><xsl:value-of select="concat($lod, 'Skolt Sami', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'den'"><xsl:value-of select="concat($lod, 'Slave', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sla'"><xsl:value-of select="concat($lod, 'Slavic (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'slo'"><xsl:value-of select="concat($lod, 'Slovak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'slv'"><xsl:value-of select="concat($lod, 'Slovenian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sog'"><xsl:value-of select="concat($lod, 'Sogdian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'som'"><xsl:value-of select="concat($lod, 'Somali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'son'"><xsl:value-of select="concat($lod, 'Songhai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'snk'"><xsl:value-of select="concat($lod, 'Soninke', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wen'"><xsl:value-of select="concat($lod, 'Sorbian languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sot'"><xsl:value-of select="concat($lod, 'Sotho', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sai'"><xsl:value-of select="concat($lod, 'South American Indian (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sma'"><xsl:value-of select="concat($lod, 'Southern Sami', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'spa'"><xsl:value-of select="concat($lod, 'Spanish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'suk'"><xsl:value-of select="concat($lod, 'Sukuma', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sux'"><xsl:value-of select="concat($lod, 'Sumerian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sun'"><xsl:value-of select="concat($lod, 'Sundanese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sus'"><xsl:value-of select="concat($lod, 'Susu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'swa'"><xsl:value-of select="concat($lod, 'Swahili', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ssw'"><xsl:value-of select="concat($lod, 'Swazi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'swe'"><xsl:value-of select="concat($lod, 'Swedish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'syr'"><xsl:value-of select="concat($lod, 'Syriac', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tgl'"><xsl:value-of select="concat($lod, 'Tagalog', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tah'"><xsl:value-of select="concat($lod, 'Tahitian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tai'"><xsl:value-of select="concat($lod, 'Tai (Other)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tgk'"><xsl:value-of select="concat($lod, 'Tajik', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tmh'"><xsl:value-of select="concat($lod, 'Tamashek', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tam'"><xsl:value-of select="concat($lod, 'Tamil', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tat'"><xsl:value-of select="concat($lod, 'Tatar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tel'"><xsl:value-of select="concat($lod, 'Telugu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tem'"><xsl:value-of select="concat($lod, 'Temne', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ter'"><xsl:value-of select="concat($lod, 'Terena', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tet'"><xsl:value-of select="concat($lod, 'Tetum', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tha'"><xsl:value-of select="concat($lod, 'Thai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tib'"><xsl:value-of select="concat($lod, 'Tibetan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tig'"><xsl:value-of select="concat($lod, 'Tigré', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tir'"><xsl:value-of select="concat($lod, 'Tigrinya', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tiv'"><xsl:value-of select="concat($lod, 'Tiv', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tli'"><xsl:value-of select="concat($lod, 'Tlingit', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tpi'"><xsl:value-of select="concat($lod, 'Tok Pisin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tkl'"><xsl:value-of select="concat($lod, 'Tokelauan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tog'"><xsl:value-of select="concat($lod, 'Tonga (Nyasa)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ton'"><xsl:value-of select="concat($lod, 'Tongan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chk'"><xsl:value-of select="concat($lod, 'Truk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tsi'"><xsl:value-of select="concat($lod, 'Tsimshian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tso'"><xsl:value-of select="concat($lod, 'Tsonga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tsn'"><xsl:value-of select="concat($lod, 'Tswana', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tum'"><xsl:value-of select="concat($lod, 'Tumbuka', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tup'"><xsl:value-of select="concat($lod, 'Tupi languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tur'"><xsl:value-of select="concat($lod, 'Turkish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ota'"><xsl:value-of select="concat($lod, 'Turkish, Ottoman', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tuk'"><xsl:value-of select="concat($lod, 'Turkmen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tvl'"><xsl:value-of select="concat($lod, 'Tuvaluan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tyv'"><xsl:value-of select="concat($lod, 'Tuvinian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'twi'"><xsl:value-of select="concat($lod, 'Twi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'udm'"><xsl:value-of select="concat($lod, 'Udmurt', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'uga'"><xsl:value-of select="concat($lod, 'Ugaritic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'uig'"><xsl:value-of select="concat($lod, 'Uighur', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ukr'"><xsl:value-of select="concat($lod, 'Ukrainian', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'umb'"><xsl:value-of select="concat($lod, 'Umbundu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'und'"><xsl:value-of select="concat($lod, 'Undetermined', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'urd'"><xsl:value-of select="concat($lod, 'Urdu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'uzb'"><xsl:value-of select="concat($lod, 'Uzbek', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vai'"><xsl:value-of select="concat($lod, 'Vai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ven'"><xsl:value-of select="concat($lod, 'Venda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vie'"><xsl:value-of select="concat($lod, 'Vietnamese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vol'"><xsl:value-of select="concat($lod, 'Volapük', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vot'"><xsl:value-of select="concat($lod, 'Votic', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wak'"><xsl:value-of select="concat($lod, 'Wakashan languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wal'"><xsl:value-of select="concat($lod, 'Walamo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wln'"><xsl:value-of select="concat($lod, 'Walloon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'war'"><xsl:value-of select="concat($lod, 'Waray', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'was'"><xsl:value-of select="concat($lod, 'Washo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wel'"><xsl:value-of select="concat($lod, 'Welsh', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wol'"><xsl:value-of select="concat($lod, 'Wolof', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'xho'"><xsl:value-of select="concat($lod, 'Xhosa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sah'"><xsl:value-of select="concat($lod, 'Yakut', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yao'"><xsl:value-of select="concat($lod, 'Yao (Africa)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yap'"><xsl:value-of select="concat($lod, 'Yapese', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yid'"><xsl:value-of select="concat($lod, 'Yiddish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yor'"><xsl:value-of select="concat($lod, 'Yoruba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ypk'"><xsl:value-of select="concat($lod, 'Yupik languages', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'znd'"><xsl:value-of select="concat($lod, 'Zande', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zap'"><xsl:value-of select="concat($lod, 'Zapotec', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zen'"><xsl:value-of select="concat($lod, 'Zenaga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zha'"><xsl:value-of select="concat($lod, 'Zhuang', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zul'"><xsl:value-of select="concat($lod, 'Zulu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zun'"><xsl:value-of select="concat($lod, 'Zuni', $break)"/></xsl:when>
            </xsl:choose>
        </fo:block>
    </xsl:template>

    <!-- Prnts full subject authority names -->
    <xsl:template name="subjectSource">
        <xsl:choose>
            <xsl:when test="@source = 'aat'"> [Source: Art &amp; Architecture Thesaurus]</xsl:when>
            <xsl:when test="@source = 'dot'"> [Source:Dictionary of Occupational Titles]</xsl:when>
            <xsl:when test="@source = 'rbgenr'"> [Source:Genre Terms: A Thesaurus for Use in Rare Book and Special Collections Cataloging]</xsl:when>
            <xsl:when test="@source = 'georeft'"> [Source:GeoRef Thesaurus]</xsl:when>
            <xsl:when test="@source = 'tgn'"> [Source:Getty Thesaurus of Geographic Names]</xsl:when>
            <xsl:when test="@source = 'lcsh'"> [Source:Library of Congress Subject Headings]</xsl:when>
            <xsl:when test="@source = 'local'"> [Source:Local sources]</xsl:when>
            <xsl:when test="@source = 'mesh'"> [Source:Medical Subject Headings]</xsl:when>
            <xsl:when test="@source = 'gmgpc'"> [Source:Thesaurus for Graphic Materials]</xsl:when>
            <xsl:when test="@source = 'ingest'"/>
            <xsl:otherwise> [Source:<xsl:value-of select="@source"/>]</xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>
