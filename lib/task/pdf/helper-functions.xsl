<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ns2="http://www.w3.org/1999/xlink" xmlns:local="http://www.yoursite.org/namespace" xmlns:ead="urn:isbn:1-931666-22-9" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="2.0">
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
        * ABOUT:        This file has been created for use with           *
        *               EAD xml files exported from the                   *
        *               ArchivesSpace web application.                    *
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
    <xsl:param name="smallcase" select="'abcdefghijklmnopqrstuvwxyzàèìòùáéíóúýâêîôûãñõäëïöüÿåæœçðø'"/>
    <xsl:param name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZÀÈÌÒÙÁÉÍÓÚÝÂÊÎÔÛÃÑÕÄËÏÖÜŸÅÆŒÇÐØ'"/>
    <xsl:template name="uppercase">
        <xsl:param name="value"/>
        <xsl:value-of select="translate($value, $smallcase, $uppercase)"/>
    </xsl:template>
    <xsl:template name="lowercase">
        <xsl:param name="value"/>
        <xsl:value-of select="translate($value, $uppercase, $smallcase)"/>
    </xsl:template>
    <xsl:template name="ucfirst">
        <xsl:param name="value"/>
        <xsl:call-template name="uppercase">
            <xsl:with-param name="value" select="substring($value, 1, 1)"/>
        </xsl:call-template>
        <xsl:call-template name="lowercase">
            <xsl:with-param name="value" select="substring($value, 2)"/>
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
            <xsl:when test="$elementNode/ead:head">
                <xsl:value-of select="$elementNode/ead:head"/>
            </xsl:when>
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
            <xsl:when test="$tag = 'container'">Physical storage</xsl:when>
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
            <xsl:when test="$tag = 'unitid'">Reference code</xsl:when>
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
    <!-- Uppercase the first letter of a string-->
    <xsl:function name="local:ucfirst">
        <xsl:param name="string"/>
        <xsl:value-of select="upper-case(substring($string,1,1))"/>
        <xsl:value-of select="substring($string,2)"/>
    </xsl:function>
    <!-- Map @type value to a human-readable string -->
    <xsl:function name="local:typeLabel">
        <xsl:param name="node"/>
        <xsl:choose>
            <!-- Title (ead:unittitle) type labels-->
            <xsl:when test="$node[@type='otherInfo']">Other title information</xsl:when>
            <!-- Note (ead:note) type labels -->
            <xsl:when test="$node[@type='sourcesDescription']">Sources</xsl:when>
            <xsl:when test="$node[@type='generalNote']">General</xsl:when>
            <xsl:otherwise><xsl:value-of select="$node/@type"/></xsl:otherwise>
        </xsl:choose>
    </xsl:function>
    <!-- Lookup ead:odd type labels (except RAD title notes) -->
    <xsl:function name="local:oddLabel">
        <xsl:param name="node"/>
        <xsl:choose>
            <xsl:when test="$node[@type='levelOfDetail']">Level of detail</xsl:when>
            <xsl:when test="$node[@type='statusDescription']">Status description</xsl:when>
            <xsl:when test="$node[@type='descriptionIdentifier']">Description identifier</xsl:when>
            <xsl:when test="$node[@type='institutionIdentifier']">Institution identifier</xsl:when>
            <xsl:when test="$node[@type='edition']">Edition</xsl:when>
            <xsl:when test="$node[@type='physDesc']">Physical description</xsl:when>
            <xsl:when test="$node[@type='conservation']">Conservation</xsl:when>
            <xsl:when test="$node[@type='material']">Accompanying material</xsl:when>
            <xsl:when test="$node[@type='alphanumericDesignation']">Alpha-numeric designations</xsl:when>
            <xsl:when test="$node[@type='bibSeries']">Publisher's series</xsl:when>
            <xsl:when test="$node[@type='rights']">Rights</xsl:when>
            <xsl:when test="$node[@type='publicationStatus']">Publication status</xsl:when>
        </xsl:choose>
    </xsl:function>
    <!-- RAD title note (ead:odd) type labels -->
    <xsl:function name="local:titleNoteLabel">
        <xsl:param name="node"/>
        <xsl:choose>
            <xsl:when test="$node[@type='titleVariation']">Variations in title</xsl:when>
            <xsl:when test="$node[@type='titleAttributions']">Attributions and conjectures</xsl:when>
            <xsl:when test="$node[@type='titleContinuation']">Continuation of title</xsl:when>
            <xsl:when test="$node[@type='titleStatRep']">Statements of responsibility</xsl:when>
            <xsl:when test="$node[@type='titleParallel']">Parallel titles and other title information</xsl:when>
            <xsl:when test="$node[@type='titleSource']">Source of title proper</xsl:when>
            <xsl:otherwise><xsl:value-of select="$node/@type"/></xsl:otherwise>
        </xsl:choose>
    </xsl:function>
    <!-- 
        Language code look-up map
        List based on the ISO 639 sets 1,2 and 3 language codes variants 
        (https://www.loc.gov/standards/iso639-2/php/code_list.php and https://iso639-3.sil.org/code_tables/639/data).
        Used for printing full language name and from converting between 2- and 3-letter codes.
    -->
    <xsl:variable name="languages">
        <languages>
            <language name="Afar" iso639-1="aa" iso639-2T="aar" iso639-2B="aar" iso639-3="aar"/>
            <language name="Abkhazian" iso639-1="ab" iso639-2T="abk" iso639-2B="abk" iso639-3="abk"/>
            <language name="Achinese"  iso639-2T="ace" iso639-2B="ace" iso639-3="ace"/>
            <language name="Acoli"  iso639-2T="ach" iso639-2B="ach" iso639-3="ach"/>
            <language name="Adangme"  iso639-2T="ada" iso639-2B="ada" iso639-3="ada"/>
            <language name="Adyghe; Adygei"  iso639-2T="ady" iso639-2B="ady" iso639-3="ady"/>
            <language name="Afro-Asiatic languages"  iso639-2T="afa" iso639-2B="afa" />
            <language name="Afrihili"  iso639-2T="afh" iso639-2B="afh" iso639-3="afh"/>
            <language name="Afrikaans" iso639-1="af" iso639-2T="afr" iso639-2B="afr" iso639-3="afr"/>
            <language name="Ainu"  iso639-2T="ain" iso639-2B="ain" iso639-3="ain"/>
            <language name="Akan" iso639-1="ak" iso639-2T="aka" iso639-2B="aka" iso639-3="aka"/>
            <language name="Akkadian"  iso639-2T="akk" iso639-2B="akk" iso639-3="akk"/>
            <language name="Aleut"  iso639-2T="ale" iso639-2B="ale" iso639-3="ale"/>
            <language name="Algonquian languages"  iso639-2T="alg" iso639-2B="alg" />
            <language name="Southern Altai"  iso639-2T="alt" iso639-2B="alt" iso639-3="alt"/>
            <language name="Amharic" iso639-1="am" iso639-2T="amh" iso639-2B="amh" iso639-3="amh"/>
            <language name="English, Old (ca.450–1100)"  iso639-2T="ang" iso639-2B="ang" iso639-3="ang"/>
            <language name="Angika"  iso639-2T="anp" iso639-2B="anp" iso639-3="anp"/>
            <language name="Apache languages"  iso639-2T="apa" iso639-2B="apa" />
            <language name="Arabic" iso639-1="ar" iso639-2T="ara" iso639-2B="ara" iso639-3="ara"/>
            <language name="Official Aramaic (700–300 BCE); Imperial Aramaic (700–300 BCE)"  iso639-2T="arc" iso639-2B="arc" iso639-3="arc"/>
            <language name="Aragonese" iso639-1="an" iso639-2T="arg" iso639-2B="arg" iso639-3="arg"/>
            <language name="Mapudungun; Mapuche"  iso639-2T="arn" iso639-2B="arn" iso639-3="arn"/>
            <language name="Arapaho"  iso639-2T="arp" iso639-2B="arp" iso639-3="arp"/>
            <language name="Artificial languages"  iso639-2T="art" iso639-2B="art" />
            <language name="Arawak"  iso639-2T="arw" iso639-2B="arw" iso639-3="arw"/>
            <language name="Assamese" iso639-1="as" iso639-2T="asm" iso639-2B="asm" iso639-3="asm"/>
            <language name="Asturian; Bable; Leonese; Asturleonese"  iso639-2T="ast" iso639-2B="ast" iso639-3="ast"/>
            <language name="Athapascan languages"  iso639-2T="ath" iso639-2B="ath" />
            <language name="Australian languages"  iso639-2T="aus" iso639-2B="aus" />
            <language name="Avaric" iso639-1="av" iso639-2T="ava" iso639-2B="ava" iso639-3="ava"/>
            <language name="Avestan" iso639-1="ae" iso639-2T="ave" iso639-2B="ave" iso639-3="ave"/>
            <language name="Awadhi"  iso639-2T="awa" iso639-2B="awa" iso639-3="awa"/>
            <language name="Aymara" iso639-1="ay" iso639-2T="aym" iso639-2B="aym" iso639-3="aym"/>
            <language name="Azerbaijani" iso639-1="az" iso639-2T="aze" iso639-2B="aze" iso639-3="aze"/>
            <language name="Banda languages"  iso639-2T="bad" iso639-2B="bad" />
            <language name="Bamileke languages"  iso639-2T="bai" iso639-2B="bai" />
            <language name="Bashkir" iso639-1="ba" iso639-2T="bak" iso639-2B="bak" iso639-3="bak"/>
            <language name="Baluchi"  iso639-2T="bal" iso639-2B="bal" iso639-3="bal"/>
            <language name="Bambara" iso639-1="bm" iso639-2T="bam" iso639-2B="bam" iso639-3="bam"/>
            <language name="Balinese"  iso639-2T="ban" iso639-2B="ban" iso639-3="ban"/>
            <language name="Basa"  iso639-2T="bas" iso639-2B="bas" iso639-3="bas"/>
            <language name="Baltic languages"  iso639-2T="bat" iso639-2B="bat" />
            <language name="Beja; Bedawiyet"  iso639-2T="bej" iso639-2B="bej" iso639-3="bej"/>
            <language name="Belarusian" iso639-1="be" iso639-2T="bel" iso639-2B="bel" iso639-3="bel"/>
            <language name="Bemba"  iso639-2T="bem" iso639-2B="bem" iso639-3="bem"/>
            <language name="Bengali" iso639-1="bn" iso639-2T="ben" iso639-2B="ben" iso639-3="ben"/>
            <language name="Berber languages"  iso639-2T="ber" iso639-2B="ber" />
            <language name="Bhojpuri"  iso639-2T="bho" iso639-2B="bho" iso639-3="bho"/>
            <language name="Bihari languages"  iso639-2T="bih" iso639-2B="bih" />
            <language name="Bikol"  iso639-2T="bik" iso639-2B="bik" iso639-3="bik"/>
            <language name="Bini; Edo"  iso639-2T="bin" iso639-2B="bin" iso639-3="bin"/>
            <language name="Bislama" iso639-1="bi" iso639-2T="bis" iso639-2B="bis" iso639-3="bis"/>
            <language name="Siksika"  iso639-2T="bla" iso639-2B="bla" iso639-3="bla"/>
            <language name="Bantu languages"  iso639-2T="bnt" iso639-2B="bnt" />
            <language name="Tibetan" iso639-1="bo" iso639-2T="bod" iso639-2B="tib" iso639-3="bod"/>
            <language name="Bosnian" iso639-1="bs" iso639-2T="bos" iso639-2B="bos" iso639-3="bos"/>
            <language name="Braj"  iso639-2T="bra" iso639-2B="bra" iso639-3="bra"/>
            <language name="Breton" iso639-1="br" iso639-2T="bre" iso639-2B="bre" iso639-3="bre"/>
            <language name="Batak languages"  iso639-2T="btk" iso639-2B="btk" />
            <language name="Buriat"  iso639-2T="bua" iso639-2B="bua" iso639-3="bua"/>
            <language name="Buginese"  iso639-2T="bug" iso639-2B="bug" iso639-3="bug"/>
            <language name="Bulgarian" iso639-1="bg" iso639-2T="bul" iso639-2B="bul" iso639-3="bul"/>
            <language name="Blin; Bilin"  iso639-2T="byn" iso639-2B="byn" iso639-3="byn"/>
            <language name="Caddo"  iso639-2T="cad" iso639-2B="cad" iso639-3="cad"/>
            <language name="Central American Indian languages"  iso639-2T="cai" iso639-2B="cai" />
            <language name="Galibi Carib"  iso639-2T="car" iso639-2B="car" iso639-3="car"/>
            <language name="Catalan; Valencian" iso639-1="ca" iso639-2T="cat" iso639-2B="cat" iso639-3="cat"/>
            <language name="Caucasian languages"  iso639-2T="cau" iso639-2B="cau" />
            <language name="Cebuano"  iso639-2T="ceb" iso639-2B="ceb" iso639-3="ceb"/>
            <language name="Celtic languages"  iso639-2T="cel" iso639-2B="cel" />
            <language name="Czech" iso639-1="cs" iso639-2T="ces" iso639-2B="cze" iso639-3="ces"/>
            <language name="Chamorro" iso639-1="ch" iso639-2T="cha" iso639-2B="cha" iso639-3="cha"/>
            <language name="Chibcha"  iso639-2T="chb" iso639-2B="chb" iso639-3="chb"/>
            <language name="Chechen" iso639-1="ce" iso639-2T="che" iso639-2B="che" iso639-3="che"/>
            <language name="Chagatai"  iso639-2T="chg" iso639-2B="chg" iso639-3="chg"/>
            <language name="Chuukese"  iso639-2T="chk" iso639-2B="chk" iso639-3="chk"/>
            <language name="Mari"  iso639-2T="chm" iso639-2B="chm" iso639-3="chm"/>
            <language name="Chinook jargon"  iso639-2T="chn" iso639-2B="chn" iso639-3="chn"/>
            <language name="Choctaw"  iso639-2T="cho" iso639-2B="cho" iso639-3="cho"/>
            <language name="Chipewyan; Dene Suline"  iso639-2T="chp" iso639-2B="chp" iso639-3="chp"/>
            <language name="Cherokee"  iso639-2T="chr" iso639-2B="chr" iso639-3="chr"/>
            <language name="Church Slavic; Old Slavonic; Church Slavonic; Old Bulgarian; Old Church Slavonic" iso639-1="cu" iso639-2T="chu" iso639-2B="chu" iso639-3="chu"/>
            <language name="Chuvash" iso639-1="cv" iso639-2T="chv" iso639-2B="chv" iso639-3="chv"/>
            <language name="Cheyenne"  iso639-2T="chy" iso639-2B="chy" iso639-3="chy"/>
            <language name="Chamic languages"  iso639-2T="cmc" iso639-2B="cmc" />
            <language name="Montenegrin"  iso639-2T="cnr" iso639-2B="cnr" iso639-3="cnr"/>
            <language name="Coptic"  iso639-2T="cop" iso639-2B="cop" iso639-3="cop"/>
            <language name="Cornish" iso639-1="kw" iso639-2T="cor" iso639-2B="cor" iso639-3="cor"/>
            <language name="Corsican" iso639-1="co" iso639-2T="cos" iso639-2B="cos" iso639-3="cos"/>
            <language name="Creoles and pidgins, English based"  iso639-2T="cpe" iso639-2B="cpe" />
            <language name="Creoles and pidgins, French-based"  iso639-2T="cpf" iso639-2B="cpf" />
            <language name="Creoles and pidgins, Portuguese-based"  iso639-2T="cpp" iso639-2B="cpp" />
            <language name="Cree" iso639-1="cr" iso639-2T="cre" iso639-2B="cre" iso639-3="cre"/>
            <language name="Crimean Tatar; Crimean Turkish"  iso639-2T="crh" iso639-2B="crh" iso639-3="crh"/>
            <language name="Creoles and pidgins"  iso639-2T="crp" iso639-2B="crp" />
            <language name="Kashubian"  iso639-2T="csb" iso639-2B="csb" iso639-3="csb"/>
            <language name="Cushitic languages"  iso639-2T="cus" iso639-2B="cus" />
            <language name="Welsh" iso639-1="cy" iso639-2T="cym" iso639-2B="wel" iso639-3="cym"/>
            <language name="Dakota"  iso639-2T="dak" iso639-2B="dak" iso639-3="dak"/>
            <language name="Danish" iso639-1="da" iso639-2T="dan" iso639-2B="dan" iso639-3="dan"/>
            <language name="Dargwa"  iso639-2T="dar" iso639-2B="dar" iso639-3="dar"/>
            <language name="Land Dayak languages"  iso639-2T="day" iso639-2B="day" />
            <language name="Delaware"  iso639-2T="del" iso639-2B="del" iso639-3="del"/>
            <language name="Slave (Athapascan)"  iso639-2T="den" iso639-2B="den" iso639-3="den"/>
            <language name="German" iso639-1="de" iso639-2T="deu" iso639-2B="ger" iso639-3="deu"/>
            <language name="Dogrib"  iso639-2T="dgr" iso639-2B="dgr" iso639-3="dgr"/>
            <language name="Dinka"  iso639-2T="din" iso639-2B="din" iso639-3="din"/>
            <language name="Divehi; Dhivehi; Maldivian" iso639-1="dv" iso639-2T="div" iso639-2B="div" iso639-3="div"/>
            <language name="Dogri"  iso639-2T="doi" iso639-2B="doi" iso639-3="doi"/>
            <language name="Dravidian languages"  iso639-2T="dra" iso639-2B="dra" />
            <language name="Lower Sorbian"  iso639-2T="dsb" iso639-2B="dsb" iso639-3="dsb"/>
            <language name="Duala"  iso639-2T="dua" iso639-2B="dua" iso639-3="dua"/>
            <language name="Dutch, Middle (ca. 1050–1350)"  iso639-2T="dum" iso639-2B="dum" iso639-3="dum"/>
            <language name="Dyula"  iso639-2T="dyu" iso639-2B="dyu" iso639-3="dyu"/>
            <language name="Dzongkha" iso639-1="dz" iso639-2T="dzo" iso639-2B="dzo" iso639-3="dzo"/>
            <language name="Efik"  iso639-2T="efi" iso639-2B="efi" iso639-3="efi"/>
            <language name="Egyptian (Ancient)"  iso639-2T="egy" iso639-2B="egy" iso639-3="egy"/>
            <language name="Ekajuk"  iso639-2T="eka" iso639-2B="eka" iso639-3="eka"/>
            <language name="Greek, Modern (1453–)" iso639-1="el" iso639-2T="ell" iso639-2B="gre" iso639-3="ell"/>
            <language name="Elamite"  iso639-2T="elx" iso639-2B="elx" iso639-3="elx"/>
            <language name="English" iso639-1="en" iso639-2T="eng" iso639-2B="eng" iso639-3="eng"/>
            <language name="English, Middle (1100–1500)"  iso639-2T="enm" iso639-2B="enm" iso639-3="enm"/>
            <language name="Esperanto" iso639-1="eo" iso639-2T="epo" iso639-2B="epo" iso639-3="epo"/>
            <language name="Estonian" iso639-1="et" iso639-2T="est" iso639-2B="est" iso639-3="est"/>
            <language name="Basque" iso639-1="eu" iso639-2T="eus" iso639-2B="baq" iso639-3="eus"/>
            <language name="Ewe" iso639-1="ee" iso639-2T="ewe" iso639-2B="ewe" iso639-3="ewe"/>
            <language name="Ewondo"  iso639-2T="ewo" iso639-2B="ewo" iso639-3="ewo"/>
            <language name="Fang"  iso639-2T="fan" iso639-2B="fan" iso639-3="fan"/>
            <language name="Faroese" iso639-1="fo" iso639-2T="fao" iso639-2B="fao" iso639-3="fao"/>
            <language name="Persian" iso639-1="fa" iso639-2T="fas" iso639-2B="per" iso639-3="fas"/>
            <language name="Fanti"  iso639-2T="fat" iso639-2B="fat" iso639-3="fat"/>
            <language name="Fijian" iso639-1="fj" iso639-2T="fij" iso639-2B="fij" iso639-3="fij"/>
            <language name="Filipino; Pilipino"  iso639-2T="fil" iso639-2B="fil" iso639-3="fil"/>
            <language name="Finnish" iso639-1="fi" iso639-2T="fin" iso639-2B="fin" iso639-3="fin"/>
            <language name="Finno-Ugrian languages"  iso639-2T="fiu" iso639-2B="fiu" />
            <language name="Fon"  iso639-2T="fon" iso639-2B="fon" iso639-3="fon"/>
            <language name="French" iso639-1="fr" iso639-2T="fra" iso639-2B="fre" iso639-3="fra"/>
            <language name="French, Middle (ca. 1400–1600)"  iso639-2T="frm" iso639-2B="frm" iso639-3="frm"/>
            <language name="French, Old (842–ca. 1400)"  iso639-2T="fro" iso639-2B="fro" iso639-3="fro"/>
            <language name="Northern Frisian"  iso639-2T="frr" iso639-2B="frr" iso639-3="frr"/>
            <language name="East Frisian Low Saxon"  iso639-2T="frs" iso639-2B="frs" iso639-3="frs"/>
            <language name="Western Frisian" iso639-1="fy" iso639-2T="fry" iso639-2B="fry" iso639-3="fry"/>
            <language name="Fulah" iso639-1="ff" iso639-2T="ful" iso639-2B="ful" iso639-3="ful"/>
            <language name="Friulian"  iso639-2T="fur" iso639-2B="fur" iso639-3="fur"/>
            <language name="Ga"  iso639-2T="gaa" iso639-2B="gaa" iso639-3="gaa"/>
            <language name="Gayo"  iso639-2T="gay" iso639-2B="gay" iso639-3="gay"/>
            <language name="Gbaya"  iso639-2T="gba" iso639-2B="gba" iso639-3="gba"/>
            <language name="Germanic languages"  iso639-2T="gem" iso639-2B="gem" />
            <language name="Geez"  iso639-2T="gez" iso639-2B="gez" iso639-3="gez"/>
            <language name="Gilbertese"  iso639-2T="gil" iso639-2B="gil" iso639-3="gil"/>
            <language name="Gaelic; Scottish Gaelic" iso639-1="gd" iso639-2T="gla" iso639-2B="gla" iso639-3="gla"/>
            <language name="Irish" iso639-1="ga" iso639-2T="gle" iso639-2B="gle" iso639-3="gle"/>
            <language name="Galician" iso639-1="gl" iso639-2T="glg" iso639-2B="glg" iso639-3="glg"/>
            <language name="Manx" iso639-1="gv" iso639-2T="glv" iso639-2B="glv" iso639-3="glv"/>
            <language name="German, Middle High (ca. 1050–1500)"  iso639-2T="gmh" iso639-2B="gmh" iso639-3="gmh"/>
            <language name="German, Old High (ca. 750–1050)"  iso639-2T="goh" iso639-2B="goh" iso639-3="goh"/>
            <language name="Gondi"  iso639-2T="gon" iso639-2B="gon" iso639-3="gon"/>
            <language name="Gorontalo"  iso639-2T="gor" iso639-2B="gor" iso639-3="gor"/>
            <language name="Gothic"  iso639-2T="got" iso639-2B="got" iso639-3="got"/>
            <language name="Grebo"  iso639-2T="grb" iso639-2B="grb" iso639-3="grb"/>
            <language name="Greek, Ancient (to 1453)"  iso639-2T="grc" iso639-2B="grc" iso639-3="grc"/>
            <language name="Guarani" iso639-1="gn" iso639-2T="grn" iso639-2B="grn" iso639-3="grn"/>
            <language name="Swiss German; Alemannic; Alsatian"  iso639-2T="gsw" iso639-2B="gsw" iso639-3="gsw"/>
            <language name="Gujarati" iso639-1="gu" iso639-2T="guj" iso639-2B="guj" iso639-3="guj"/>
            <language name="Gwich'in"  iso639-2T="gwi" iso639-2B="gwi" iso639-3="gwi"/>
            <language name="Haida"  iso639-2T="hai" iso639-2B="hai" iso639-3="hai"/>
            <language name="Haitian; Haitian Creole" iso639-1="ht" iso639-2T="hat" iso639-2B="hat" iso639-3="hat"/>
            <language name="Hausa" iso639-1="ha" iso639-2T="hau" iso639-2B="hau" iso639-3="hau"/>
            <language name="Hawaiian"  iso639-2T="haw" iso639-2B="haw" iso639-3="haw"/>
            <language name="Hebrew" iso639-1="he" iso639-2T="heb" iso639-2B="heb" iso639-3="heb"/>
            <language name="Herero" iso639-1="hz" iso639-2T="her" iso639-2B="her" iso639-3="her"/>
            <language name="Hiligaynon"  iso639-2T="hil" iso639-2B="hil" iso639-3="hil"/>
            <language name="Himachali languages; Pahari languages"  iso639-2T="him" iso639-2B="him" />
            <language name="Hindi" iso639-1="hi" iso639-2T="hin" iso639-2B="hin" iso639-3="hin"/>
            <language name="Hittite"  iso639-2T="hit" iso639-2B="hit" iso639-3="hit"/>
            <language name="Hmong; Mong"  iso639-2T="hmn" iso639-2B="hmn" iso639-3="hmn"/>
            <language name="Hiri Motu" iso639-1="ho" iso639-2T="hmo" iso639-2B="hmo" iso639-3="hmo"/>
            <language name="Croatian" iso639-1="hr" iso639-2T="hrv" iso639-2B="hrv" iso639-3="hrv"/>
            <language name="Upper Sorbian"  iso639-2T="hsb" iso639-2B="hsb" iso639-3="hsb"/>
            <language name="Hungarian" iso639-1="hu" iso639-2T="hun" iso639-2B="hun" iso639-3="hun"/>
            <language name="Hupa"  iso639-2T="hup" iso639-2B="hup" iso639-3="hup"/>
            <language name="Armenian" iso639-1="hy" iso639-2T="hye" iso639-2B="arm" iso639-3="hye"/>
            <language name="Iban"  iso639-2T="iba" iso639-2B="iba" iso639-3="iba"/>
            <language name="Igbo" iso639-1="ig" iso639-2T="ibo" iso639-2B="ibo" iso639-3="ibo"/>
            <language name="Ido" iso639-1="io" iso639-2T="ido" iso639-2B="ido" iso639-3="ido"/>
            <language name="Sichuan Yi; Nuosu" iso639-1="ii" iso639-2T="iii" iso639-2B="iii" iso639-3="iii"/>
            <language name="Ijo languages"  iso639-2T="ijo" iso639-2B="ijo" />
            <language name="Inuktitut" iso639-1="iu" iso639-2T="iku" iso639-2B="iku" iso639-3="iku"/>
            <language name="Interlingue; Occidental" iso639-1="ie" iso639-2T="ile" iso639-2B="ile" iso639-3="ile"/>
            <language name="Iloko"  iso639-2T="ilo" iso639-2B="ilo" iso639-3="ilo"/>
            <language name="Interlingua (International Auxiliary Language Association)" iso639-1="ia" iso639-2T="ina" iso639-2B="ina" iso639-3="ina"/>
            <language name="Indo-Aryan languages"  iso639-2T="inc" iso639-2B="inc" />
            <language name="Indonesian" iso639-1="id" iso639-2T="ind" iso639-2B="ind" iso639-3="ind"/>
            <language name="Indo-European languages"  iso639-2T="ine" iso639-2B="ine" />
            <language name="Ingush"  iso639-2T="inh" iso639-2B="inh" iso639-3="inh"/>
            <language name="Inupiaq" iso639-1="ik" iso639-2T="ipk" iso639-2B="ipk" iso639-3="ipk"/>
            <language name="Iranian languages"  iso639-2T="ira" iso639-2B="ira" />
            <language name="Iroquoian languages"  iso639-2T="iro" iso639-2B="iro" />
            <language name="Icelandic" iso639-1="is" iso639-2T="isl" iso639-2B="ice" iso639-3="isl"/>
            <language name="Italian" iso639-1="it" iso639-2T="ita" iso639-2B="ita" iso639-3="ita"/>
            <language name="Javanese" iso639-1="jv" iso639-2T="jav" iso639-2B="jav" iso639-3="jav"/>
            <language name="Lojban"  iso639-2T="jbo" iso639-2B="jbo" iso639-3="jbo"/>
            <language name="Japanese" iso639-1="ja" iso639-2T="jpn" iso639-2B="jpn" iso639-3="jpn"/>
            <language name="Judeo-Persian"  iso639-2T="jpr" iso639-2B="jpr" iso639-3="jpr"/>
            <language name="Judeo-Arabic"  iso639-2T="jrb" iso639-2B="jrb" iso639-3="jrb"/>
            <language name="Kara-Kalpak"  iso639-2T="kaa" iso639-2B="kaa" iso639-3="kaa"/>
            <language name="Kabyle"  iso639-2T="kab" iso639-2B="kab" iso639-3="kab"/>
            <language name="Kachin; Jingpho"  iso639-2T="kac" iso639-2B="kac" iso639-3="kac"/>
            <language name="Kalaallisut; Greenlandic" iso639-1="kl" iso639-2T="kal" iso639-2B="kal" iso639-3="kal"/>
            <language name="Kamba"  iso639-2T="kam" iso639-2B="kam" iso639-3="kam"/>
            <language name="Kannada" iso639-1="kn" iso639-2T="kan" iso639-2B="kan" iso639-3="kan"/>
            <language name="Karen languages"  iso639-2T="kar" iso639-2B="kar" />
            <language name="Kashmiri" iso639-1="ks" iso639-2T="kas" iso639-2B="kas" iso639-3="kas"/>
            <language name="Georgian" iso639-1="ka" iso639-2T="kat" iso639-2B="geo" iso639-3="kat"/>
            <language name="Kanuri" iso639-1="kr" iso639-2T="kau" iso639-2B="kau" iso639-3="kau"/>
            <language name="Kawi"  iso639-2T="kaw" iso639-2B="kaw" iso639-3="kaw"/>
            <language name="Kazakh" iso639-1="kk" iso639-2T="kaz" iso639-2B="kaz" iso639-3="kaz"/>
            <language name="Kabardian"  iso639-2T="kbd" iso639-2B="kbd" iso639-3="kbd"/>
            <language name="Khasi"  iso639-2T="kha" iso639-2B="kha" iso639-3="kha"/>
            <language name="Khoisan languages"  iso639-2T="khi" iso639-2B="khi" />
            <language name="Central Khmer" iso639-1="km" iso639-2T="khm" iso639-2B="khm" iso639-3="khm"/>
            <language name="Khotanese; Sakan"  iso639-2T="kho" iso639-2B="kho" iso639-3="kho"/>
            <language name="Kikuyu; Gikuyu" iso639-1="ki" iso639-2T="kik" iso639-2B="kik" iso639-3="kik"/>
            <language name="Kinyarwanda" iso639-1="rw" iso639-2T="kin" iso639-2B="kin" iso639-3="kin"/>
            <language name="Kirghiz; Kyrgyz" iso639-1="ky" iso639-2T="kir" iso639-2B="kir" iso639-3="kir"/>
            <language name="Kimbundu"  iso639-2T="kmb" iso639-2B="kmb" iso639-3="kmb"/>
            <language name="Konkani"  iso639-2T="kok" iso639-2B="kok" iso639-3="kok"/>
            <language name="Komi" iso639-1="kv" iso639-2T="kom" iso639-2B="kom" iso639-3="kom"/>
            <language name="Kongo" iso639-1="kg" iso639-2T="kon" iso639-2B="kon" iso639-3="kon"/>
            <language name="Korean" iso639-1="ko" iso639-2T="kor" iso639-2B="kor" iso639-3="kor"/>
            <language name="Kosraean"  iso639-2T="kos" iso639-2B="kos" iso639-3="kos"/>
            <language name="Kpelle"  iso639-2T="kpe" iso639-2B="kpe" iso639-3="kpe"/>
            <language name="Karachay-Balkar"  iso639-2T="krc" iso639-2B="krc" iso639-3="krc"/>
            <language name="Karelian"  iso639-2T="krl" iso639-2B="krl" iso639-3="krl"/>
            <language name="Kru languages"  iso639-2T="kro" iso639-2B="kro" />
            <language name="Kurukh"  iso639-2T="kru" iso639-2B="kru" iso639-3="kru"/>
            <language name="Kuanyama; Kwanyama" iso639-1="kj" iso639-2T="kua" iso639-2B="kua" iso639-3="kua"/>
            <language name="Kumyk"  iso639-2T="kum" iso639-2B="kum" iso639-3="kum"/>
            <language name="Kurdish" iso639-1="ku" iso639-2T="kur" iso639-2B="kur" iso639-3="kur"/>
            <language name="Kutenai"  iso639-2T="kut" iso639-2B="kut" iso639-3="kut"/>
            <language name="Ladino"  iso639-2T="lad" iso639-2B="lad" iso639-3="lad"/>
            <language name="Lahnda"  iso639-2T="lah" iso639-2B="lah" iso639-3="lah"/>
            <language name="Lamba"  iso639-2T="lam" iso639-2B="lam" iso639-3="lam"/>
            <language name="Lao" iso639-1="lo" iso639-2T="lao" iso639-2B="lao" iso639-3="lao"/>
            <language name="Latin" iso639-1="la" iso639-2T="lat" iso639-2B="lat" iso639-3="lat"/>
            <language name="Latvian" iso639-1="lv" iso639-2T="lav" iso639-2B="lav" iso639-3="lav"/>
            <language name="Lezghian"  iso639-2T="lez" iso639-2B="lez" iso639-3="lez"/>
            <language name="Limburgan; Limburger; Limburgish" iso639-1="li" iso639-2T="lim" iso639-2B="lim" iso639-3="lim"/>
            <language name="Lingala" iso639-1="ln" iso639-2T="lin" iso639-2B="lin" iso639-3="lin"/>
            <language name="Lithuanian" iso639-1="lt" iso639-2T="lit" iso639-2B="lit" iso639-3="lit"/>
            <language name="Mongo"  iso639-2T="lol" iso639-2B="lol" iso639-3="lol"/>
            <language name="Lozi"  iso639-2T="loz" iso639-2B="loz" iso639-3="loz"/>
            <language name="Luxembourgish; Letzeburgesch" iso639-1="lb" iso639-2T="ltz" iso639-2B="ltz" iso639-3="ltz"/>
            <language name="Luba-Lulua"  iso639-2T="lua" iso639-2B="lua" iso639-3="lua"/>
            <language name="Luba-Katanga" iso639-1="lu" iso639-2T="lub" iso639-2B="lub" iso639-3="lub"/>
            <language name="Ganda" iso639-1="lg" iso639-2T="lug" iso639-2B="lug" iso639-3="lug"/>
            <language name="Luiseno"  iso639-2T="lui" iso639-2B="lui" iso639-3="lui"/>
            <language name="Lunda"  iso639-2T="lun" iso639-2B="lun" iso639-3="lun"/>
            <language name="Luo (Kenya and Tanzania)"  iso639-2T="luo" iso639-2B="luo" iso639-3="luo"/>
            <language name="Lushai"  iso639-2T="lus" iso639-2B="lus" iso639-3="lus"/>
            <language name="Madurese"  iso639-2T="mad" iso639-2B="mad" iso639-3="mad"/>
            <language name="Magahi"  iso639-2T="mag" iso639-2B="mag" iso639-3="mag"/>
            <language name="Marshallese" iso639-1="mh" iso639-2T="mah" iso639-2B="mah" iso639-3="mah"/>
            <language name="Maithili"  iso639-2T="mai" iso639-2B="mai" iso639-3="mai"/>
            <language name="Makasar"  iso639-2T="mak" iso639-2B="mak" iso639-3="mak"/>
            <language name="Malayalam" iso639-1="ml" iso639-2T="mal" iso639-2B="mal" iso639-3="mal"/>
            <language name="Mandingo"  iso639-2T="man" iso639-2B="man" iso639-3="man"/>
            <language name="Austronesian languages"  iso639-2T="map" iso639-2B="map" />
            <language name="Marathi" iso639-1="mr" iso639-2T="mar" iso639-2B="mar" iso639-3="mar"/>
            <language name="Masai"  iso639-2T="mas" iso639-2B="mas" iso639-3="mas"/>
            <language name="Moksha"  iso639-2T="mdf" iso639-2B="mdf" iso639-3="mdf"/>
            <language name="Mandar"  iso639-2T="mdr" iso639-2B="mdr" iso639-3="mdr"/>
            <language name="Mende"  iso639-2T="men" iso639-2B="men" iso639-3="men"/>
            <language name="Irish, Middle (900–1200)"  iso639-2T="mga" iso639-2B="mga" iso639-3="mga"/>
            <language name="Mi'kmaq; Micmac"  iso639-2T="mic" iso639-2B="mic" iso639-3="mic"/>
            <language name="Minangkabau"  iso639-2T="min" iso639-2B="min" iso639-3="min"/>
            <language name="Uncoded languages"  iso639-2T="mis" iso639-2B="mis" iso639-3="mis"/>
            <language name="Macedonian" iso639-1="mk" iso639-2T="mkd" iso639-2B="mac" iso639-3="mkd"/>
            <language name="Mon-Khmer languages"  iso639-2T="mkh" iso639-2B="mkh" />
            <language name="Malagasy" iso639-1="mg" iso639-2T="mlg" iso639-2B="mlg" iso639-3="mlg"/>
            <language name="Maltese" iso639-1="mt" iso639-2T="mlt" iso639-2B="mlt" iso639-3="mlt"/>
            <language name="Manchu"  iso639-2T="mnc" iso639-2B="mnc" iso639-3="mnc"/>
            <language name="Manipuri"  iso639-2T="mni" iso639-2B="mni" iso639-3="mni"/>
            <language name="Manobo languages"  iso639-2T="mno" iso639-2B="mno" />
            <language name="Mohawk"  iso639-2T="moh" iso639-2B="moh" iso639-3="moh"/>
            <language name="Mongolian" iso639-1="mn" iso639-2T="mon" iso639-2B="mon" iso639-3="mon"/>
            <language name="Mossi"  iso639-2T="mos" iso639-2B="mos" iso639-3="mos"/>
            <language name="Māori" iso639-1="mi" iso639-2T="mri" iso639-2B="mao" iso639-3="mri"/>
            <language name="Malay" iso639-1="ms" iso639-2T="msa" iso639-2B="may" iso639-3="msa"/>
            <language name="Multiple languages"  iso639-2T="mul" iso639-2B="mul" iso639-3="mul"/>
            <language name="Munda languages"  iso639-2T="mun" iso639-2B="mun" />
            <language name="Creek"  iso639-2T="mus" iso639-2B="mus" iso639-3="mus"/>
            <language name="Mirandese"  iso639-2T="mwl" iso639-2B="mwl" iso639-3="mwl"/>
            <language name="Marwari"  iso639-2T="mwr" iso639-2B="mwr" iso639-3="mwr"/>
            <language name="Burmese" iso639-1="my" iso639-2T="mya" iso639-2B="bur" iso639-3="mya"/>
            <language name="Mayan languages"  iso639-2T="myn" iso639-2B="myn" />
            <language name="Erzya"  iso639-2T="myv" iso639-2B="myv" iso639-3="myv"/>
            <language name="Nahuatl languages"  iso639-2T="nah" iso639-2B="nah" />
            <language name="North American Indian languages"  iso639-2T="nai" iso639-2B="nai" />
            <language name="Neapolitan"  iso639-2T="nap" iso639-2B="nap" iso639-3="nap"/>
            <language name="Nauru" iso639-1="na" iso639-2T="nau" iso639-2B="nau" iso639-3="nau"/>
            <language name="Navajo; Navaho" iso639-1="nv" iso639-2T="nav" iso639-2B="nav" iso639-3="nav"/>
            <language name="Ndebele, South; South Ndebele" iso639-1="nr" iso639-2T="nbl" iso639-2B="nbl" iso639-3="nbl"/>
            <language name="Ndebele, North; North Ndebele" iso639-1="nd" iso639-2T="nde" iso639-2B="nde" iso639-3="nde"/>
            <language name="Ndonga" iso639-1="ng" iso639-2T="ndo" iso639-2B="ndo" iso639-3="ndo"/>
            <language name="Low German; Low Saxon; German, Low; Saxon, Low"  iso639-2T="nds" iso639-2B="nds" iso639-3="nds"/>
            <language name="Nepali" iso639-1="ne" iso639-2T="nep" iso639-2B="nep" iso639-3="nep"/>
            <language name="Nepal Bhasa; Newari"  iso639-2T="new" iso639-2B="new" iso639-3="new"/>
            <language name="Nias"  iso639-2T="nia" iso639-2B="nia" iso639-3="nia"/>
            <language name="Niger-Kordofanian languages"  iso639-2T="nic" iso639-2B="nic" />
            <language name="Niuean"  iso639-2T="niu" iso639-2B="niu" iso639-3="niu"/>
            <language name="Dutch; Flemish" iso639-1="nl" iso639-2T="nld" iso639-2B="dut" iso639-3="nld"/>
            <language name="Norwegian Nynorsk; Nynorsk, Norwegian" iso639-1="nn" iso639-2T="nno" iso639-2B="nno" iso639-3="nno"/>
            <language name="Bokmål, Norwegian; Norwegian Bokmål" iso639-1="nb" iso639-2T="nob" iso639-2B="nob" iso639-3="nob"/>
            <language name="Nogai"  iso639-2T="nog" iso639-2B="nog" iso639-3="nog"/>
            <language name="Norse, Old"  iso639-2T="non" iso639-2B="non" iso639-3="non"/>
            <language name="Norwegian" iso639-1="no" iso639-2T="nor" iso639-2B="nor" iso639-3="nor"/>
            <language name="N'Ko"  iso639-2T="nqo" iso639-2B="nqo" iso639-3="nqo"/>
            <language name="Pedi; Sepedi; Northern Sotho"  iso639-2T="nso" iso639-2B="nso" iso639-3="nso"/>
            <language name="Nubian languages"  iso639-2T="nub" iso639-2B="nub" />
            <language name="Classical Newari; Old Newari; Classical Nepal Bhasa"  iso639-2T="nwc" iso639-2B="nwc" iso639-3="nwc"/>
            <language name="Chichewa; Chewa; Nyanja" iso639-1="ny" iso639-2T="nya" iso639-2B="nya" iso639-3="nya"/>
            <language name="Nyamwezi"  iso639-2T="nym" iso639-2B="nym" iso639-3="nym"/>
            <language name="Nyankole"  iso639-2T="nyn" iso639-2B="nyn" iso639-3="nyn"/>
            <language name="Nyoro"  iso639-2T="nyo" iso639-2B="nyo" iso639-3="nyo"/>
            <language name="Nzima"  iso639-2T="nzi" iso639-2B="nzi" iso639-3="nzi"/>
            <language name="Occitan (post 1500)" iso639-1="oc" iso639-2T="oci" iso639-2B="oci" iso639-3="oci"/>
            <language name="Ojibwa" iso639-1="oj" iso639-2T="oji" iso639-2B="oji" iso639-3="oji"/>
            <language name="Oriya" iso639-1="or" iso639-2T="ori" iso639-2B="ori" iso639-3="ori"/>
            <language name="Oromo" iso639-1="om" iso639-2T="orm" iso639-2B="orm" iso639-3="orm"/>
            <language name="Osage"  iso639-2T="osa" iso639-2B="osa" iso639-3="osa"/>
            <language name="Ossetian; Ossetic" iso639-1="os" iso639-2T="oss" iso639-2B="oss" iso639-3="oss"/>
            <language name="Turkish, Ottoman (1500–1928)"  iso639-2T="ota" iso639-2B="ota" iso639-3="ota"/>
            <language name="Otomian languages"  iso639-2T="oto" iso639-2B="oto" />
            <language name="Papuan languages"  iso639-2T="paa" iso639-2B="paa" />
            <language name="Pangasinan"  iso639-2T="pag" iso639-2B="pag" iso639-3="pag"/>
            <language name="Pahlavi"  iso639-2T="pal" iso639-2B="pal" iso639-3="pal"/>
            <language name="Pampanga; Kapampangan"  iso639-2T="pam" iso639-2B="pam" iso639-3="pam"/>
            <language name="Panjabi; Punjabi" iso639-1="pa" iso639-2T="pan" iso639-2B="pan" iso639-3="pan"/>
            <language name="Papiamento"  iso639-2T="pap" iso639-2B="pap" iso639-3="pap"/>
            <language name="Palauan"  iso639-2T="pau" iso639-2B="pau" iso639-3="pau"/>
            <language name="Persian, Old (c. 600–400 B.C.)"  iso639-2T="peo" iso639-2B="peo" iso639-3="peo"/>
            <language name="Philippine languages"  iso639-2T="phi" iso639-2B="phi" />
            <language name="Phoenician"  iso639-2T="phn" iso639-2B="phn" iso639-3="phn"/>
            <language name="Pali" iso639-1="pi" iso639-2T="pli" iso639-2B="pli" iso639-3="pli"/>
            <language name="Polish" iso639-1="pl" iso639-2T="pol" iso639-2B="pol" iso639-3="pol"/>
            <language name="Pohnpeian"  iso639-2T="pon" iso639-2B="pon" iso639-3="pon"/>
            <language name="Portuguese" iso639-1="pt" iso639-2T="por" iso639-2B="por" iso639-3="por"/>
            <language name="Prakrit languages"  iso639-2T="pra" iso639-2B="pra" />
            <language name="Provençal, Old (to 1500); Old Occitan (to 1500)"  iso639-2T="pro" iso639-2B="pro" iso639-3="pro"/>
            <language name="Pushto; Pashto" iso639-1="ps" iso639-2T="pus" iso639-2B="pus" iso639-3="pus"/>
            <language name="Reserved for local use"  iso639-2T="qaa-qtz" iso639-2B="qaa-qtz" iso639-3="qaa-qtz"/>
            <language name="Quechua" iso639-1="qu" iso639-2T="que" iso639-2B="que" iso639-3="que"/>
            <language name="Rajasthani"  iso639-2T="raj" iso639-2B="raj" iso639-3="raj"/>
            <language name="Rapanui"  iso639-2T="rap" iso639-2B="rap" iso639-3="rap"/>
            <language name="Rarotongan; Cook Islands Māori"  iso639-2T="rar" iso639-2B="rar" iso639-3="rar"/>
            <language name="Romance languages"  iso639-2T="roa" iso639-2B="roa" />
            <language name="Romansh" iso639-1="rm" iso639-2T="roh" iso639-2B="roh" iso639-3="roh"/>
            <language name="Romany"  iso639-2T="rom" iso639-2B="rom" iso639-3="rom"/>
            <language name="Romanian; Moldavian; Moldovan" iso639-1="ro" iso639-2T="ron" iso639-2B="rum" iso639-3="ron"/>
            <language name="Rundi" iso639-1="rn" iso639-2T="run" iso639-2B="run" iso639-3="run"/>
            <language name="Aromanian; Arumanian; Macedo-Romanian[b]"  iso639-2T="rup" iso639-2B="rup" iso639-3="rup"/>
            <language name="Russian" iso639-1="ru" iso639-2T="rus" iso639-2B="rus" iso639-3="rus"/>
            <language name="Sandawe"  iso639-2T="sad" iso639-2B="sad" iso639-3="sad"/>
            <language name="Sango" iso639-1="sg" iso639-2T="sag" iso639-2B="sag" iso639-3="sag"/>
            <language name="Yakut"  iso639-2T="sah" iso639-2B="sah" iso639-3="sah"/>
            <language name="South American Indian languages"  iso639-2T="sai" iso639-2B="sai" />
            <language name="Salishan languages"  iso639-2T="sal" iso639-2B="sal" />
            <language name="Samaritan Aramaic"  iso639-2T="sam" iso639-2B="sam" iso639-3="sam"/>
            <language name="Sanskrit" iso639-1="sa" iso639-2T="san" iso639-2B="san" iso639-3="san"/>
            <language name="Sasak"  iso639-2T="sas" iso639-2B="sas" iso639-3="sas"/>
            <language name="Santali"  iso639-2T="sat" iso639-2B="sat" iso639-3="sat"/>
            <language name="Sicilian"  iso639-2T="scn" iso639-2B="scn" iso639-3="scn"/>
            <language name="Scots"  iso639-2T="sco" iso639-2B="sco" iso639-3="sco"/>
            <language name="Selkup"  iso639-2T="sel" iso639-2B="sel" iso639-3="sel"/>
            <language name="Semitic languages"  iso639-2T="sem" iso639-2B="sem" />
            <language name="Irish, Old (to 900)"  iso639-2T="sga" iso639-2B="sga" iso639-3="sga"/>
            <language name="Sign Languages"  iso639-2T="sgn" iso639-2B="sgn" />
            <language name="Shan"  iso639-2T="shn" iso639-2B="shn" iso639-3="shn"/>
            <language name="Sidamo"  iso639-2T="sid" iso639-2B="sid" iso639-3="sid"/>
            <language name="Sinhala; Sinhalese" iso639-1="si" iso639-2T="sin" iso639-2B="sin" iso639-3="sin"/>
            <language name="Siouan languages"  iso639-2T="sio" iso639-2B="sio" />
            <language name="Sino-Tibetan languages"  iso639-2T="sit" iso639-2B="sit" />
            <language name="Slavic languages"  iso639-2T="sla" iso639-2B="sla" />
            <language name="Slovak" iso639-1="sk" iso639-2T="slk" iso639-2B="slo" iso639-3="slk"/>
            <language name="Slovenian" iso639-1="sl" iso639-2T="slv" iso639-2B="slv" iso639-3="slv"/>
            <language name="Southern Sami"  iso639-2T="sma" iso639-2B="sma" iso639-3="sma"/>
            <language name="Northern Sami" iso639-1="se" iso639-2T="sme" iso639-2B="sme" iso639-3="sme"/>
            <language name="Sami languages"  iso639-2T="smi" iso639-2B="smi" />
            <language name="Lule Sami"  iso639-2T="smj" iso639-2B="smj" iso639-3="smj"/>
            <language name="Inari Sami"  iso639-2T="smn" iso639-2B="smn" iso639-3="smn"/>
            <language name="Samoan" iso639-1="sm" iso639-2T="smo" iso639-2B="smo" iso639-3="smo"/>
            <language name="Skolt Sami"  iso639-2T="sms" iso639-2B="sms" iso639-3="sms"/>
            <language name="Shona" iso639-1="sn" iso639-2T="sna" iso639-2B="sna" iso639-3="sna"/>
            <language name="Sindhi" iso639-1="sd" iso639-2T="snd" iso639-2B="snd" iso639-3="snd"/>
            <language name="Soninke"  iso639-2T="snk" iso639-2B="snk" iso639-3="snk"/>
            <language name="Sogdian"  iso639-2T="sog" iso639-2B="sog" iso639-3="sog"/>
            <language name="Somali" iso639-1="so" iso639-2T="som" iso639-2B="som" iso639-3="som"/>
            <language name="Songhai languages"  iso639-2T="son" iso639-2B="son" />
            <language name="Sotho, Southern" iso639-1="st" iso639-2T="sot" iso639-2B="sot" iso639-3="sot"/>
            <language name="Spanish; Castilian" iso639-1="es" iso639-2T="spa" iso639-2B="spa" iso639-3="spa"/>
            <language name="Albanian" iso639-1="sq" iso639-2T="sqi" iso639-2B="alb" iso639-3="sqi"/>
            <language name="Sardinian" iso639-1="sc" iso639-2T="srd" iso639-2B="srd" iso639-3="srd"/>
            <language name="Sranan Tongo"  iso639-2T="srn" iso639-2B="srn" iso639-3="srn"/>
            <language name="Serbian" iso639-1="sr" iso639-2T="srp" iso639-2B="srp" iso639-3="srp"/>
            <language name="Serer"  iso639-2T="srr" iso639-2B="srr" iso639-3="srr"/>
            <language name="Nilo-Saharan languages"  iso639-2T="ssa" iso639-2B="ssa" />
            <language name="Swati" iso639-1="ss" iso639-2T="ssw" iso639-2B="ssw" iso639-3="ssw"/>
            <language name="Sukuma"  iso639-2T="suk" iso639-2B="suk" iso639-3="suk"/>
            <language name="Sundanese" iso639-1="su" iso639-2T="sun" iso639-2B="sun" iso639-3="sun"/>
            <language name="Susu"  iso639-2T="sus" iso639-2B="sus" iso639-3="sus"/>
            <language name="Sumerian"  iso639-2T="sux" iso639-2B="sux" iso639-3="sux"/>
            <language name="Swahili" iso639-1="sw" iso639-2T="swa" iso639-2B="swa" iso639-3="swa"/>
            <language name="Swedish" iso639-1="sv" iso639-2T="swe" iso639-2B="swe" iso639-3="swe"/>
            <language name="Classical Syriac"  iso639-2T="syc" iso639-2B="syc" iso639-3="syc"/>
            <language name="Syriac"  iso639-2T="syr" iso639-2B="syr" iso639-3="syr"/>
            <language name="Tahitian" iso639-1="ty" iso639-2T="tah" iso639-2B="tah" iso639-3="tah"/>
            <language name="Tai languages"  iso639-2T="tai" iso639-2B="tai" />
            <language name="Tamil" iso639-1="ta" iso639-2T="tam" iso639-2B="tam" iso639-3="tam"/>
            <language name="Tatar" iso639-1="tt" iso639-2T="tat" iso639-2B="tat" iso639-3="tat"/>
            <language name="Telugu" iso639-1="te" iso639-2T="tel" iso639-2B="tel" iso639-3="tel"/>
            <language name="Timne"  iso639-2T="tem" iso639-2B="tem" iso639-3="tem"/>
            <language name="Tereno"  iso639-2T="ter" iso639-2B="ter" iso639-3="ter"/>
            <language name="Tetum"  iso639-2T="tet" iso639-2B="tet" iso639-3="tet"/>
            <language name="Tajik" iso639-1="tg" iso639-2T="tgk" iso639-2B="tgk" iso639-3="tgk"/>
            <language name="Tagalog" iso639-1="tl" iso639-2T="tgl" iso639-2B="tgl" iso639-3="tgl"/>
            <language name="Thai" iso639-1="th" iso639-2T="tha" iso639-2B="tha" iso639-3="tha"/>
            <language name="Tigre"  iso639-2T="tig" iso639-2B="tig" iso639-3="tig"/>
            <language name="Tigrinya" iso639-1="ti" iso639-2T="tir" iso639-2B="tir" iso639-3="tir"/>
            <language name="Tiv"  iso639-2T="tiv" iso639-2B="tiv" iso639-3="tiv"/>
            <language name="Tokelau"  iso639-2T="tkl" iso639-2B="tkl" iso639-3="tkl"/>
            <language name="Klingon; tlhIngan-Hol"  iso639-2T="tlh" iso639-2B="tlh" iso639-3="tlh"/>
            <language name="Tlingit"  iso639-2T="tli" iso639-2B="tli" iso639-3="tli"/>
            <language name="Tamashek"  iso639-2T="tmh" iso639-2B="tmh" iso639-3="tmh"/>
            <language name="Tonga (Nyasa)"  iso639-2T="tog" iso639-2B="tog" iso639-3="tog"/>
            <language name="Tonga (Tonga Islands)" iso639-1="to" iso639-2T="ton" iso639-2B="ton" iso639-3="ton"/>
            <language name="Tok Pisin"  iso639-2T="tpi" iso639-2B="tpi" iso639-3="tpi"/>
            <language name="Tsimshian"  iso639-2T="tsi" iso639-2B="tsi" iso639-3="tsi"/>
            <language name="Tswana" iso639-1="tn" iso639-2T="tsn" iso639-2B="tsn" iso639-3="tsn"/>
            <language name="Tsonga" iso639-1="ts" iso639-2T="tso" iso639-2B="tso" iso639-3="tso"/>
            <language name="Turkmen" iso639-1="tk" iso639-2T="tuk" iso639-2B="tuk" iso639-3="tuk"/>
            <language name="Tumbuka"  iso639-2T="tum" iso639-2B="tum" iso639-3="tum"/>
            <language name="Tupi languages"  iso639-2T="tup" iso639-2B="tup" />
            <language name="Turkish" iso639-1="tr" iso639-2T="tur" iso639-2B="tur" iso639-3="tur"/>
            <language name="Altaic languages"  iso639-2T="tut" iso639-2B="tut" />
            <language name="Tuvalu"  iso639-2T="tvl" iso639-2B="tvl" iso639-3="tvl"/>
            <language name="Twi" iso639-1="tw" iso639-2T="twi" iso639-2B="twi" iso639-3="twi"/>
            <language name="Tuvinian"  iso639-2T="tyv" iso639-2B="tyv" iso639-3="tyv"/>
            <language name="Udmurt"  iso639-2T="udm" iso639-2B="udm" iso639-3="udm"/>
            <language name="Ugaritic"  iso639-2T="uga" iso639-2B="uga" iso639-3="uga"/>
            <language name="Uighur; Uyghur" iso639-1="ug" iso639-2T="uig" iso639-2B="uig" iso639-3="uig"/>
            <language name="Ukrainian" iso639-1="uk" iso639-2T="ukr" iso639-2B="ukr" iso639-3="ukr"/>
            <language name="Umbundu"  iso639-2T="umb" iso639-2B="umb" iso639-3="umb"/>
            <language name="Undetermined"  iso639-2T="und" iso639-2B="und" iso639-3="und"/>
            <language name="Urdu" iso639-1="ur" iso639-2T="urd" iso639-2B="urd" iso639-3="urd"/>
            <language name="Uzbek" iso639-1="uz" iso639-2T="uzb" iso639-2B="uzb" iso639-3="uzb"/>
            <language name="Vai"  iso639-2T="vai" iso639-2B="vai" iso639-3="vai"/>
            <language name="Venda" iso639-1="ve" iso639-2T="ven" iso639-2B="ven" iso639-3="ven"/>
            <language name="Vietnamese" iso639-1="vi" iso639-2T="vie" iso639-2B="vie" iso639-3="vie"/>
            <language name="Volapük" iso639-1="vo" iso639-2T="vol" iso639-2B="vol" iso639-3="vol"/>
            <language name="Votic"  iso639-2T="vot" iso639-2B="vot" iso639-3="vot"/>
            <language name="Wakashan languages"  iso639-2T="wak" iso639-2B="wak" />
            <language name="Wolaitta; Wolaytta"  iso639-2T="wal" iso639-2B="wal" iso639-3="wal"/>
            <language name="Waray"  iso639-2T="war" iso639-2B="war" iso639-3="war"/>
            <language name="Washo"  iso639-2T="was" iso639-2B="was" iso639-3="was"/>
            <language name="Sorbian languages"  iso639-2T="wen" iso639-2B="wen" />
            <language name="Walloon" iso639-1="wa" iso639-2T="wln" iso639-2B="wln" iso639-3="wln"/>
            <language name="Wolof" iso639-1="wo" iso639-2T="wol" iso639-2B="wol" iso639-3="wol"/>
            <language name="Kalmyk; Oirat"  iso639-2T="xal" iso639-2B="xal" iso639-3="xal"/>
            <language name="Xhosa" iso639-1="xh" iso639-2T="xho" iso639-2B="xho" iso639-3="xho"/>
            <language name="Yao"  iso639-2T="yao" iso639-2B="yao" iso639-3="yao"/>
            <language name="Yapese"  iso639-2T="yap" iso639-2B="yap" iso639-3="yap"/>
            <language name="Yiddish" iso639-1="yi" iso639-2T="yid" iso639-2B="yid" iso639-3="yid"/>
            <language name="Yoruba" iso639-1="yo" iso639-2T="yor" iso639-2B="yor" iso639-3="yor"/>
            <language name="Yupik languages"  iso639-2T="ypk" iso639-2B="ypk" />
            <language name="Zapotec"  iso639-2T="zap" iso639-2B="zap" iso639-3="zap"/>
            <language name="Blissymbols; Blissymbolics; Bliss"  iso639-2T="zbl" iso639-2B="zbl" iso639-3="zbl"/>
            <language name="Zenaga"  iso639-2T="zen" iso639-2B="zen" iso639-3="zen"/>
            <language name="Standard Moroccan Tamazight"  iso639-2T="zgh" iso639-2B="zgh" iso639-3="zgh"/>
            <language name="Zhuang; Chuang" iso639-1="za" iso639-2T="zha" iso639-2B="zha" iso639-3="zha"/>
            <language name="Chinese" iso639-1="zh" iso639-2T="zho" iso639-2B="chi" iso639-3="zho"/>
            <language name="Zande languages"  iso639-2T="znd" iso639-2B="znd" />
            <language name="Zulu" iso639-1="zu" iso639-2T="zul" iso639-2B="zul" iso639-3="zul"/>
            <language name="Zuni"  iso639-2T="zun" iso639-2B="zun" iso639-3="zun"/>
            <language name="No linguistic content; Not applicable"  iso639-2T="zxx" iso639-2B="zxx" iso639-3="zxx"/>
            <language name="Zaza; Dimili; Dimli; Kirdki; Kirmanjki; Zazaki"  iso639-2T="zza" iso639-2B="zza" iso639-3="zza"/>
        </languages>
    </xsl:variable>
    <!-- Prints out full language name from abbreviation. -->
    <xsl:key name="languageCode" match="language" use="@iso639-2B"/>
    <xsl:template match="ead:language">
        <xsl:param name="prefix"/>
        <fo:block linefeed-treatment="preserve">
            <xsl:variable name="lod" select="'Language of description: '"/>
            <xsl:variable name="break">
</xsl:variable>
            <xsl:choose>
                <xsl:when test="@langcode = 'No_linguistic_content'">No linguistic content</xsl:when>
                <xsl:when test="@langcode = 'und'">
                    <xsl:value-of select="concat($lod, 'Undetermined', $break)"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="concat($lod, key('languageCode', @langcode, $languages)/@name, $break)"/>
                </xsl:otherwise>
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
