<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:ns2="http://www.w3.org/1999/xlink" xmlns:local="http://www.yoursite.org/namespace" xmlns:ead="urn:isbn:1-931666-22-9" version="2.0" xmlns:fo="http://www.w3.org/1999/XSL/Format">
    <!--
        *******************************************************************
        *                                                                 *
        * VERSION:          2.6.2                                         *
        *                                                                 *
        * AUTHOR:           Winona Salesky                                *
        *                   wsalesky@gmail.com                            *
        *                                                                 *
        * MODIFIED BY:      mikeg@artefactual.com                         *
        *                   djjuhasz@gmail.com                            *
        *                                                                 *
        * DATE:             2021-03-10                                    *
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
            <xsl:text>Compléments de titre</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleStatRep'">
            <xsl:text>Mentions de responsabilité</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleParallel'">
            <xsl:text>Titres parallèles</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleSource'">
            <xsl:text>Source du titre propre</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleVariation'">
            <xsl:text>Variantes de titre (n)</xsl:text>
        </xsl:if>
        <xsl:if test="$type = 'titleAttributions'">
            <xsl:text>Attributions et conjectures</xsl:text>
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
            <xsl:when test="$tag = 'did'">Information sommaire</xsl:when>
            <xsl:when test="$tag = 'abstract'">Résumé</xsl:when>
            <xsl:when test="$tag = 'accruals'">Versements complémentaires</xsl:when>
            <xsl:when test="$tag = 'acqinfo'">Source immédiate d’acquisition</xsl:when>
            <xsl:when test="$tag = 'address'">Adresse</xsl:when>
            <xsl:when test="$tag = 'altformavail'">Autres formats</xsl:when>
            <xsl:when test="$tag = 'appraisal'">Informations sur l'évaluation</xsl:when>
            <xsl:when test="$tag = 'arc'">Arc</xsl:when>
            <xsl:when test="$tag = 'archref'">Référence à d'autres documents d'archives</xsl:when>
            <xsl:when test="$tag = 'arrangement'">Classement</xsl:when>
            <xsl:when test="$tag = 'author'">Auteur de l'instrument de recherche</xsl:when>
            <xsl:when test="$tag = 'bibref'">Référence bibliographique</xsl:when>
            <xsl:when test="$tag = 'bibseries'">Collection bibliographique</xsl:when>
            <xsl:when test="$tag = 'bibliography'">Bibliographie</xsl:when>

            <!-- AtoM: Test if the bioghist is from a person/family/corp, set heading accordingly -->
            <xsl:when test="$tag = 'bioghist'">Histoire administrative / Notice biographique</xsl:when>

            <xsl:when test="$tag = 'change'">Modification</xsl:when>
            <xsl:when test="$tag = 'chronlist'">Liste chronologique</xsl:when>
            <xsl:when test="$tag = 'accessrestrict'">Restrictions à la consultationlike 2</xsl:when>
            <xsl:when test="$tag = 'userestrict'">Conditions d’utilisation, de reproduction et de publication</xsl:when>
            <xsl:when test="$tag = 'controlaccess'">Mots-clés</xsl:when>
            <xsl:when test="$tag = 'corpname'">Collectivité</xsl:when>
            <xsl:when test="$tag = 'creation'">Création</xsl:when>
            <xsl:when test="$tag = 'custodhist'">Historique de la conservation</xsl:when>
            <xsl:when test="$tag = 'date'">Date(s)</xsl:when>
            <xsl:when test="$tag = 'descgrp'">Groupe d'éléments de description</xsl:when>
            <xsl:when test="$tag = 'dsc'">Descriptions des collections</xsl:when>
            <xsl:when test="$tag = 'descrules'">Règles de description</xsl:when>
            <xsl:when test="$tag = 'dao'">Document numérique</xsl:when>
            <xsl:when test="$tag = 'daodesc'">Description du document numérique</xsl:when>
            <xsl:when test="$tag = 'daogrp'">Groupe de documents numériques</xsl:when>
            <xsl:when test="$tag = 'daoloc'">Localisation du document numérique</xsl:when>
            <xsl:when test="$tag = 'dimensions'">Dimensions</xsl:when>
            <xsl:when test="$tag = 'edition'">Édition</xsl:when>
            <xsl:when test="$tag = 'editionstmt'">Mention d'édition</xsl:when>
            <xsl:when test="$tag = 'event'">Événement</xsl:when>
            <xsl:when test="$tag = 'eventgrp'">Groupe d'événements</xsl:when>
            <xsl:when test="$tag = 'expan'">Forme développée</xsl:when>
            <xsl:when test="$tag = 'extptr'">Pointeur externe</xsl:when>
            <xsl:when test="$tag = 'extptrloc'">Localisation de pointeur externe</xsl:when>
            <xsl:when test="$tag = 'extref'">Référence externe</xsl:when>
            <xsl:when test="$tag = 'extrefloc'">Localisation d'une référence externe</xsl:when>
            <xsl:when test="$tag = 'extent'">Importance matérielle</xsl:when>
            <xsl:when test="$tag = 'famname'">Nom de famille</xsl:when>
            <xsl:when test="$tag = 'filedesc'">Description du fichier</xsl:when>
            <xsl:when test="$tag = 'fileplan'">Plan de classement</xsl:when>
            <xsl:when test="$tag = 'frontmatter'">Préliminaires</xsl:when>
            <xsl:when test="$tag = 'function'">Activité</xsl:when>
            <xsl:when test="$tag = 'genreform'">Genre et caractéristiques matérielles</xsl:when>
            <xsl:when test="$tag = 'geogname'">Nom géographique</xsl:when>
            <xsl:when test="$tag = 'imprint'">Adresse bibliographique</xsl:when>
            <xsl:when test="$tag = 'index'">Index</xsl:when>
            <xsl:when test="$tag = 'indexentry'">Entrée d'index</xsl:when>
            <xsl:when test="$tag = 'item'">Pièce</xsl:when>
            <xsl:when test="$tag = 'language'">Langue</xsl:when>
            <xsl:when test="$tag = 'langmaterial'">Langue des documents</xsl:when>
            <xsl:when test="$tag = 'langusage'">Langue utilisée</xsl:when>
            <xsl:when test="$tag = 'legalstatus'">Statut juridique</xsl:when>
            <xsl:when test="$tag = 'linkgrp'">Groupe de liens</xsl:when>
            <xsl:when test="$tag = 'originalsloc'">Emplacement des originaux</xsl:when>
            <xsl:when test="$tag = 'materialspec'">Mention spécifique</xsl:when>
            <xsl:when test="$tag = 'name'">Nom</xsl:when>
            <xsl:when test="$tag = 'namegrp'">Groupe de noms</xsl:when>
            <xsl:when test="$tag = 'note'">Note</xsl:when>
            <xsl:when test="$tag = 'notestmt'">Mention de note</xsl:when>
            <xsl:when test="$tag = 'occupation'">Fonction</xsl:when>
            <xsl:when test="$tag = 'origination'">Créateur</xsl:when>
            <xsl:when test="$tag = 'odd'">
                <!-- Atom: Choose prefix to note: -->
                <xsl:choose>
                    <xsl:when test="$elementNode[@type='publicationStatus']">Statut de la notice</xsl:when>
                    <xsl:otherwise>Autres données derscriptives</xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="$tag = 'otherfindaid'">Instruments de recherche</xsl:when>
            <xsl:when test="$tag = 'persname'">Nom de personne</xsl:when>
            <xsl:when test="$tag = 'phystech'">État de conservation</xsl:when>
            <xsl:when test="$tag = 'physdesc'">Description matérielle</xsl:when>
            <xsl:when test="$tag = 'physfacet'">Particularité matérielle</xsl:when>
            <xsl:when test="$tag = 'physloc'">Localisation physique</xsl:when>
            <xsl:when test="$tag = 'ptr'">Pointeur</xsl:when>
            <xsl:when test="$tag = 'ptrgrp'">Groupe de pointeurs</xsl:when>
            <xsl:when test="$tag = 'ptrloc'">Localisation de pointeurs</xsl:when>
            <xsl:when test="$tag = 'prefercite'">Mention conseillée</xsl:when>
            <xsl:when test="$tag = 'processinfo'">Informations sur le traitement</xsl:when>
            <xsl:when test="$tag = 'profiledesc'">Description du profil</xsl:when>
            <xsl:when test="$tag = 'publicationstmt'">Mention de publication</xsl:when>
            <xsl:when test="$tag = 'publisher'">Éditeur</xsl:when>
            <xsl:when test="$tag = 'ref'">Référence</xsl:when>
            <xsl:when test="$tag = 'refloc'">Localisation de référence</xsl:when>
            <xsl:when test="$tag = 'relatedmaterial'">Groupe de documents reliés</xsl:when>
            <xsl:when test="$tag = 'repository'">Institution de conservation</xsl:when>
            <xsl:when test="$tag = 'resource'">Ressource</xsl:when>
            <xsl:when test="$tag = 'revisiondesc'">Description des révisions</xsl:when>
            <xsl:when test="$tag = 'runner'">Titre courant ou filigrane</xsl:when>
            <xsl:when test="$tag = 'scopecontent'">Portée et contenu</xsl:when>
            <xsl:when test="$tag = 'separatedmaterial'">Documents séparés</xsl:when>
            <xsl:when test="$tag = 'seriesstmt'">Mention de collection</xsl:when>
            <xsl:when test="$tag = 'sponsor'">Commanditaire</xsl:when>
            <xsl:when test="$tag = 'subject'">Sujet</xsl:when>
            <xsl:when test="$tag = 'subarea'">Subdivision</xsl:when>
            <xsl:when test="$tag = 'subtitle'">Sous-titre de l'instrument de recherche</xsl:when>
            <xsl:when test="$tag = 'div'">Division du texte</xsl:when>
            <xsl:when test="$tag = 'title'">Titre</xsl:when>
            <xsl:when test="$tag = 'unittitle'">Titre</xsl:when>
            <xsl:when test="$tag = 'unitdate'">Date(s)</xsl:when>
            <xsl:when test="$tag = 'unitid'">Cote</xsl:when>
            <xsl:when test="$tag = 'titlepage'">Page de titre</xsl:when>
            <xsl:when test="$tag = 'titleproper'">Titre propre de l'instrument de recherche</xsl:when>
            <xsl:when test="$tag = 'titlestmt'">Mention de titre</xsl:when>
            <!-- eac-cpf fields -->
            <xsl:when test="$tag = 'identity'">Nom(s)</xsl:when>
            <xsl:when test="$tag = 'description'">Description</xsl:when>
            <xsl:when test="$tag = 'relations'">Liens</xsl:when>
            <xsl:when test="$tag = 'structureOrGenealogy'">Structure / généalogie</xsl:when>
            <xsl:when test="$tag = 'localDescription'">Description locale</xsl:when>
            <xsl:when test="$tag= 'generalContext'">Contexte général</xsl:when>
            <xsl:when test="$tag= 'alternativeSet'">Documents de substitution</xsl:when>
            <xsl:when test="$tag= 'functions'">Activités</xsl:when>
            <xsl:when test="$tag= 'biogHist'">Biographie ou histoire</xsl:when>

        </xsl:choose>
    </xsl:function>

    <!--
        A local function to parse ISO dates into more readable dates.
        Takes a date formatted like this: 2009-11-18T10:16-0500
        Returns: 18 novembre 2009
    -->
    <xsl:function name="local:parseDate">
        <xsl:param name="dateString"/>
        <xsl:variable name="month">
            <xsl:choose>
                <xsl:when test="substring($dateString,6,2) = '01'">janvier</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '02'">février</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '03'">mars</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '04'">avril</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '05'">mai</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '06'">juin</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '07'">juillet</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '08'">août</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '09'">septembre</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '10'">octobre</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '11'">novembre</xsl:when>
                <xsl:when test="substring($dateString,6,2) = '12'">décembre</xsl:when>
            </xsl:choose>
        </xsl:variable>
        <xsl:variable name="day">
            <xsl:choose>
                <xsl:when test="substring($dateString,9,1) = '0'">
                    <xsl:value-of select="substring($dateString,10,1)"/>
                </xsl:when>
                <xsl:otherwise>
                    <xsl:value-of select="substring($dateString,9,2)"/>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
        <xsl:value-of select="concat($day,' ',$month,' ',substring($dateString,1,4))"/>
    </xsl:function>

    <!--
        Prints out full language name from abbreviation.
        List based on the ISO 639-2b three-letter language codes (http://www.loc.gov/standards/iso639-2/php/code_list.php).
    -->
    <xsl:template match="ead:language">
        <xsl:param name="prefix"/>
        <fo:block linefeed-treatment="preserve">
            <xsl:variable name="lod" select="'Langue de la description: '"/>
            <xsl:variable name="break">&#10;</xsl:variable>
            <xsl:choose>
                <xsl:when test="@langcode = 'No_linguistic_content'">Pas de contenu linguistique</xsl:when>
                <xsl:when test="@langcode = 'aar'"><xsl:value-of select="concat($lod, 'afar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'abk'"><xsl:value-of select="concat($lod, 'abkhaze', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ace'"><xsl:value-of select="concat($lod, 'aceh', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ach'"><xsl:value-of select="concat($lod, 'acoli', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ada'"><xsl:value-of select="concat($lod, 'adangmé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ady'"><xsl:value-of select="concat($lod, 'adyghé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'afa'"><xsl:value-of select="concat($lod, 'langues afro-asiatiques', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'afh'"><xsl:value-of select="concat($lod, 'afrihili', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'afr'"><xsl:value-of select="concat($lod, 'afrikaans', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ain'"><xsl:value-of select="concat($lod, 'aïnou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aka'"><xsl:value-of select="concat($lod, 'akan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'akk'"><xsl:value-of select="concat($lod, 'akkadien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'alb'"><xsl:value-of select="concat($lod, 'albanais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ale'"><xsl:value-of select="concat($lod, 'aléoute', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'alg'"><xsl:value-of select="concat($lod, 'langues algonquiennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'alt'"><xsl:value-of select="concat($lod, 'altaï du Sud', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'amh'"><xsl:value-of select="concat($lod, 'amharique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ang'"><xsl:value-of select="concat($lod, 'anglo-saxon (environ 450-1100), ancien anglais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'anp'"><xsl:value-of select="concat($lod, 'angika', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'apa'"><xsl:value-of select="concat($lod, 'langues apaches', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ara'"><xsl:value-of select="concat($lod, 'arabe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arc'"><xsl:value-of select="concat($lod, 'araméen d&#8217;empire (700-300 BCE)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arg'"><xsl:value-of select="concat($lod, 'aragonais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arm'"><xsl:value-of select="concat($lod, 'arménien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arn'"><xsl:value-of select="concat($lod, 'mapudungun, mapouche, mapuce', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arp'"><xsl:value-of select="concat($lod, 'arapaho', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'art'"><xsl:value-of select="concat($lod, 'langues artificielles', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'arw'"><xsl:value-of select="concat($lod, 'arawak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'asm'"><xsl:value-of select="concat($lod, 'assamais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ast'"><xsl:value-of select="concat($lod, 'asturien, bable, léonais, asturoléonais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ath'"><xsl:value-of select="concat($lod, 'langues athapascanes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aus'"><xsl:value-of select="concat($lod, 'langues australiennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ava'"><xsl:value-of select="concat($lod, 'avar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ave'"><xsl:value-of select="concat($lod, 'avestique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'awa'"><xsl:value-of select="concat($lod, 'awadhi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aym'"><xsl:value-of select="concat($lod, 'aymará', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'aze'"><xsl:value-of select="concat($lod, 'azéri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bad'"><xsl:value-of select="concat($lod, 'langues banda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bai'"><xsl:value-of select="concat($lod, 'langues bamilékées', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bak'"><xsl:value-of select="concat($lod, 'bachkir', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bal'"><xsl:value-of select="concat($lod, 'baloutche', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bam'"><xsl:value-of select="concat($lod, 'bambara', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ban'"><xsl:value-of select="concat($lod, 'balinais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'baq'"><xsl:value-of select="concat($lod, 'basque', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bas'"><xsl:value-of select="concat($lod, 'basa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bat'"><xsl:value-of select="concat($lod, 'langues baltes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bej'"><xsl:value-of select="concat($lod, 'bedja', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bel'"><xsl:value-of select="concat($lod, 'biélorusse', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bem'"><xsl:value-of select="concat($lod, 'bemba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ben'"><xsl:value-of select="concat($lod, 'bengalî', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ber'"><xsl:value-of select="concat($lod, 'langues berbères', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bho'"><xsl:value-of select="concat($lod, 'bhodjpouri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bih'"><xsl:value-of select="concat($lod, 'bihari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bik'"><xsl:value-of select="concat($lod, 'bikol central', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bin'"><xsl:value-of select="concat($lod, 'bini, edo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bis'"><xsl:value-of select="concat($lod, 'bichelamar, bêche-de-mer', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bla'"><xsl:value-of select="concat($lod, 'blackfoot (siksika)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bnt'"><xsl:value-of select="concat($lod, 'langues bantoues', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bos'"><xsl:value-of select="concat($lod, 'bosniaque', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bra'"><xsl:value-of select="concat($lod, 'braj basha', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bre'"><xsl:value-of select="concat($lod, 'breton', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'btk'"><xsl:value-of select="concat($lod, 'langues batak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bua'"><xsl:value-of select="concat($lod, 'bouriate', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bug'"><xsl:value-of select="concat($lod, 'bugi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bul'"><xsl:value-of select="concat($lod, 'bulgare', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'bur'"><xsl:value-of select="concat($lod, 'birman', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'byn'"><xsl:value-of select="concat($lod, 'blin, bilen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cad'"><xsl:value-of select="concat($lod, 'caddo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cai'"><xsl:value-of select="concat($lod, 'langues indiennes d&#8217;Amérique centrale', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'car'"><xsl:value-of select="concat($lod, 'galibi, carib, karib', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cat'"><xsl:value-of select="concat($lod, 'catalan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cau'"><xsl:value-of select="concat($lod, 'langues caucasiennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ceb'"><xsl:value-of select="concat($lod, 'cébouano', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cel'"><xsl:value-of select="concat($lod, 'langues celtiques', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cha'"><xsl:value-of select="concat($lod, 'chamorro', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chb'"><xsl:value-of select="concat($lod, 'chibcha', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'che'"><xsl:value-of select="concat($lod, 'tchétchène', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chg'"><xsl:value-of select="concat($lod, 'djaghataï', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chi'"><xsl:value-of select="concat($lod, 'Chinois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chk'"><xsl:value-of select="concat($lod, 'chuuk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chm'"><xsl:value-of select="concat($lod, 'mari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chn'"><xsl:value-of select="concat($lod, 'chinook jargon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cho'"><xsl:value-of select="concat($lod, 'choctaw', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chp'"><xsl:value-of select="concat($lod, 'chipewyan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chr'"><xsl:value-of select="concat($lod, 'cherokee', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chu'"><xsl:value-of select="concat($lod, 'slavon d&#8217;église, vieux slave, slavon liturgique, vieux bulgare', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chv'"><xsl:value-of select="concat($lod, 'tchouvache', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'chy'"><xsl:value-of select="concat($lod, 'cheyenne', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cmc'"><xsl:value-of select="concat($lod, 'langues chames', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cop'"><xsl:value-of select="concat($lod, 'copte', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cor'"><xsl:value-of select="concat($lod, 'cornique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cos'"><xsl:value-of select="concat($lod, 'corse', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cpe'"><xsl:value-of select="concat($lod, 'créoles et pidgins basés sur l&#8217;anglais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cpf'"><xsl:value-of select="concat($lod, 'créoles et pidgins basés sur le français', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cpp'"><xsl:value-of select="concat($lod, 'créoles et pidgins basés sur le portugais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cre'"><xsl:value-of select="concat($lod, 'cree', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'crh'"><xsl:value-of select="concat($lod, 'tatar de Crimée', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'crp'"><xsl:value-of select="concat($lod, 'créoles et pidgins', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'csb'"><xsl:value-of select="concat($lod, 'cachoube', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cus'"><xsl:value-of select="concat($lod, 'langues couchitiques', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'cze'"><xsl:value-of select="concat($lod, 'tchèque', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dak'"><xsl:value-of select="concat($lod, 'dakota', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dan'"><xsl:value-of select="concat($lod, 'danois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dar'"><xsl:value-of select="concat($lod, 'dargwa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'day'"><xsl:value-of select="concat($lod, 'langues Land Dayak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'del'"><xsl:value-of select="concat($lod, 'delaware', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'den'"><xsl:value-of select="concat($lod, 'slavey (langue)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dgr'"><xsl:value-of select="concat($lod, 'dogrib', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'din'"><xsl:value-of select="concat($lod, 'dinka', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'div'"><xsl:value-of select="concat($lod, 'maldivien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'doi'"><xsl:value-of select="concat($lod, 'dogri (kangri)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dra'"><xsl:value-of select="concat($lod, 'langues dravidiennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dsb'"><xsl:value-of select="concat($lod, 'bas-sorabe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dua'"><xsl:value-of select="concat($lod, 'douala', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dum'"><xsl:value-of select="concat($lod, 'Néerlandais, moyen (environ 1050-1350)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dut'"><xsl:value-of select="concat($lod, 'néerlandais, flamand', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dyu'"><xsl:value-of select="concat($lod, 'dioula', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'dzo'"><xsl:value-of select="concat($lod, 'dzongkha', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'efi'"><xsl:value-of select="concat($lod, 'éfik', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'egy'"><xsl:value-of select="concat($lod, 'égyptien ancien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'eka'"><xsl:value-of select="concat($lod, 'ekajuk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'elx'"><xsl:value-of select="concat($lod, 'élamite', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'eng'"><xsl:value-of select="concat($lod, 'anglais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'enm'"><xsl:value-of select="concat($lod, 'Anglais, moyen (1100-1500)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'epo'"><xsl:value-of select="concat($lod, 'espéranto', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'est'"><xsl:value-of select="concat($lod, 'estonien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ewe'"><xsl:value-of select="concat($lod, 'éwé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ewo'"><xsl:value-of select="concat($lod, 'éwondo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fan'"><xsl:value-of select="concat($lod, 'fang', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fao'"><xsl:value-of select="concat($lod, 'féroïen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fat'"><xsl:value-of select="concat($lod, 'fanti', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fij'"><xsl:value-of select="concat($lod, 'fidjien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fil'"><xsl:value-of select="concat($lod, 'filipino', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fin'"><xsl:value-of select="concat($lod, 'finnois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fiu'"><xsl:value-of select="concat($lod, 'langues finno-ougriennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fon'"><xsl:value-of select="concat($lod, 'fon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fre'"><xsl:value-of select="concat($lod, 'français', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'frm'"><xsl:value-of select="concat($lod, 'moyen français (1400-1600)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fro'"><xsl:value-of select="concat($lod, 'ancien français (842-environ 1400)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'frr'"><xsl:value-of select="concat($lod, 'frison septentrional', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'frs'"><xsl:value-of select="concat($lod, 'frison oriental', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fry'"><xsl:value-of select="concat($lod, 'frison occidental', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ful'"><xsl:value-of select="concat($lod, 'peul', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'fur'"><xsl:value-of select="concat($lod, 'frioulan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gaa'"><xsl:value-of select="concat($lod, 'ga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gay'"><xsl:value-of select="concat($lod, 'gayo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gba'"><xsl:value-of select="concat($lod, 'gbaya', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gem'"><xsl:value-of select="concat($lod, 'langues germaniques', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'geo'"><xsl:value-of select="concat($lod, 'géorgien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ger'"><xsl:value-of select="concat($lod, 'allemand', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gez'"><xsl:value-of select="concat($lod, 'guèze', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gil'"><xsl:value-of select="concat($lod, 'gilbertin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gla'"><xsl:value-of select="concat($lod, 'gaélique écossais, Gaélique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gle'"><xsl:value-of select="concat($lod, 'irlandais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'glg'"><xsl:value-of select="concat($lod, 'galicien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'glv'"><xsl:value-of select="concat($lod, 'mannois, manx', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gmh'"><xsl:value-of select="concat($lod, 'moyen haut-allemand (environ 1050-1500)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'goh'"><xsl:value-of select="concat($lod, 'vieux haut allemand (environ 750-1050)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gon'"><xsl:value-of select="concat($lod, 'gondî', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gor'"><xsl:value-of select="concat($lod, 'gorontalo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'got'"><xsl:value-of select="concat($lod, 'gotique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'grb'"><xsl:value-of select="concat($lod, 'grébo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'grc'"><xsl:value-of select="concat($lod, 'grec ancien (jusqu&#8217;à 1453)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gre'"><xsl:value-of select="concat($lod, 'grec moderne (après 1453)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'grn'"><xsl:value-of select="concat($lod, 'Guarani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gsw'"><xsl:value-of select="concat($lod, 'alémanique, suisse alémanique, alsacien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'guj'"><xsl:value-of select="concat($lod, 'goudjrati', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'gwi'"><xsl:value-of select="concat($lod, 'gwich&apos;'in', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hai'"><xsl:value-of select="concat($lod, 'haïda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hat'"><xsl:value-of select="concat($lod, 'haïtien, créole haïtien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hau'"><xsl:value-of select="concat($lod, 'haoussa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'haw'"><xsl:value-of select="concat($lod, 'hawaïen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'heb'"><xsl:value-of select="concat($lod, 'hébreu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'her'"><xsl:value-of select="concat($lod, 'héréro', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hil'"><xsl:value-of select="concat($lod, 'hiligaïnon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'him'"><xsl:value-of select="concat($lod, 'himachali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hin'"><xsl:value-of select="concat($lod, 'hindi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hit'"><xsl:value-of select="concat($lod, 'hittite', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hmn'"><xsl:value-of select="concat($lod, 'hmong', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hmo'"><xsl:value-of select="concat($lod, 'hiri motou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hrv'"><xsl:value-of select="concat($lod, 'croate', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hsb'"><xsl:value-of select="concat($lod, 'haut-sorabe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hun'"><xsl:value-of select="concat($lod, 'hongrois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'hup'"><xsl:value-of select="concat($lod, 'houpa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iba'"><xsl:value-of select="concat($lod, 'iban', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ibo'"><xsl:value-of select="concat($lod, 'igbo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ice'"><xsl:value-of select="concat($lod, 'islandais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ido'"><xsl:value-of select="concat($lod, 'ido', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iii'"><xsl:value-of select="concat($lod, 'yi de Sichuan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ijo'"><xsl:value-of select="concat($lod, 'langues ijo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iku'"><xsl:value-of select="concat($lod, 'inuktitut', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ile'"><xsl:value-of select="concat($lod, 'interlingue', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ilo'"><xsl:value-of select="concat($lod, 'ilocano', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ina'"><xsl:value-of select="concat($lod, 'interlingua (langue auxiliaire internationale)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'inc'"><xsl:value-of select="concat($lod, 'langues indo-aryennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ind'"><xsl:value-of select="concat($lod, 'indonésien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ine'"><xsl:value-of select="concat($lod, 'langues indo-européennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'inh'"><xsl:value-of select="concat($lod, 'ingouche', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ipk'"><xsl:value-of select="concat($lod, 'inupiaq', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ira'"><xsl:value-of select="concat($lod, 'langues iraniennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'iro'"><xsl:value-of select="concat($lod, 'langues iroquoises', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ita'"><xsl:value-of select="concat($lod, 'italien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jav'"><xsl:value-of select="concat($lod, 'javanais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jbo'"><xsl:value-of select="concat($lod, 'lojban', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jpn'"><xsl:value-of select="concat($lod, 'japonais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jpr'"><xsl:value-of select="concat($lod, 'judéo-persan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'jrb'"><xsl:value-of select="concat($lod, 'judéo-arabe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kaa'"><xsl:value-of select="concat($lod, 'karakalpak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kab'"><xsl:value-of select="concat($lod, 'kabyle', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kac'"><xsl:value-of select="concat($lod, 'jinghpo, kachin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kal'"><xsl:value-of select="concat($lod, 'groenlandais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kam'"><xsl:value-of select="concat($lod, 'kamba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kan'"><xsl:value-of select="concat($lod, 'kannada', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kar'"><xsl:value-of select="concat($lod, 'langues karens', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kas'"><xsl:value-of select="concat($lod, 'kashmiri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kau'"><xsl:value-of select="concat($lod, 'kanouri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kaw'"><xsl:value-of select="concat($lod, 'kawi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kaz'"><xsl:value-of select="concat($lod, 'kazakh', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kbd'"><xsl:value-of select="concat($lod, 'kabarde', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kha'"><xsl:value-of select="concat($lod, 'khasi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'khi'"><xsl:value-of select="concat($lod, 'langues khoïsan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'khm'"><xsl:value-of select="concat($lod, 'khmer central', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kho'"><xsl:value-of select="concat($lod, 'khotanais, sakan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kik'"><xsl:value-of select="concat($lod, 'kikouyou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kin'"><xsl:value-of select="concat($lod, 'rwanda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kir'"><xsl:value-of select="concat($lod, 'kirghize', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kmb'"><xsl:value-of select="concat($lod, 'kimboundou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kok'"><xsl:value-of select="concat($lod, 'konkani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kom'"><xsl:value-of select="concat($lod, 'kom', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kon'"><xsl:value-of select="concat($lod, 'kongo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kor'"><xsl:value-of select="concat($lod, 'coréen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kos'"><xsl:value-of select="concat($lod, 'kosraéen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kpe'"><xsl:value-of select="concat($lod, 'kpellé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'krc'"><xsl:value-of select="concat($lod, 'karatchaï-balkar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'krl'"><xsl:value-of select="concat($lod, 'carélien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kro'"><xsl:value-of select="concat($lod, 'langues krou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kru'"><xsl:value-of select="concat($lod, 'kouroukh', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kua'"><xsl:value-of select="concat($lod, 'kuanyama, kwanyama', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kum'"><xsl:value-of select="concat($lod, 'koumyk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kur'"><xsl:value-of select="concat($lod, 'kurde', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'kut'"><xsl:value-of select="concat($lod, 'koutenai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lad'"><xsl:value-of select="concat($lod, 'judéo-espagnol', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lah'"><xsl:value-of select="concat($lod, 'lahnda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lam'"><xsl:value-of select="concat($lod, 'lamba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lao'"><xsl:value-of select="concat($lod, 'lao', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lat'"><xsl:value-of select="concat($lod, 'latin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lav'"><xsl:value-of select="concat($lod, 'letton', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lez'"><xsl:value-of select="concat($lod, 'lezguien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lim'"><xsl:value-of select="concat($lod, 'limbourgeois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lin'"><xsl:value-of select="concat($lod, 'lingala', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lit'"><xsl:value-of select="concat($lod, 'lituanien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lol'"><xsl:value-of select="concat($lod, 'mongo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'loz'"><xsl:value-of select="concat($lod, 'lozi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ltz'"><xsl:value-of select="concat($lod, 'luxembourgeois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lua'"><xsl:value-of select="concat($lod, 'luba-Lulua', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lub'"><xsl:value-of select="concat($lod, 'luba-Katanga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lug'"><xsl:value-of select="concat($lod, 'ganda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lui'"><xsl:value-of select="concat($lod, 'luiseño', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lun'"><xsl:value-of select="concat($lod, 'lounda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'luo'"><xsl:value-of select="concat($lod, 'luo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'lus'"><xsl:value-of select="concat($lod, 'lousaï', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mac'"><xsl:value-of select="concat($lod, 'macédonien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mad'"><xsl:value-of select="concat($lod, 'madurais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mag'"><xsl:value-of select="concat($lod, 'magahi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mah'"><xsl:value-of select="concat($lod, 'marshallais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mai'"><xsl:value-of select="concat($lod, 'maïthili', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mak'"><xsl:value-of select="concat($lod, 'makassar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mal'"><xsl:value-of select="concat($lod, 'malayalam', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'man'"><xsl:value-of select="concat($lod, 'mandingue', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mao'"><xsl:value-of select="concat($lod, 'māori', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'map'"><xsl:value-of select="concat($lod, 'langues austronésiennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mar'"><xsl:value-of select="concat($lod, 'marathe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mas'"><xsl:value-of select="concat($lod, 'massaï,', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'may'"><xsl:value-of select="concat($lod, 'malais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mdf'"><xsl:value-of select="concat($lod, 'mokcha', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mdr'"><xsl:value-of select="concat($lod, 'mandar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'men'"><xsl:value-of select="concat($lod, 'mendé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mga'"><xsl:value-of select="concat($lod, 'moyen irlandais (900-1200)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mic'"><xsl:value-of select="concat($lod, 'mi&#8217;kmaq, micmac', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'min'"><xsl:value-of select="concat($lod, 'minangkabao', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mis'"><xsl:value-of select="concat($lod, 'langues non codées', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mkh'"><xsl:value-of select="concat($lod, 'langues môn-khmer', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mlg'"><xsl:value-of select="concat($lod, 'malgache', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mlt'"><xsl:value-of select="concat($lod, 'maltais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mnc'"><xsl:value-of select="concat($lod, 'mandchou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mni'"><xsl:value-of select="concat($lod, 'manipuri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mno'"><xsl:value-of select="concat($lod, 'langues manobo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'moh'"><xsl:value-of select="concat($lod, 'mohawk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mol'"><xsl:value-of select="concat($lod, 'Moldave', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mon'"><xsl:value-of select="concat($lod, 'mongol', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mos'"><xsl:value-of select="concat($lod, 'moré', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mul'"><xsl:value-of select="concat($lod, 'multilingue', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mun'"><xsl:value-of select="concat($lod, 'langues mounda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mus'"><xsl:value-of select="concat($lod, 'muskogee', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mwl'"><xsl:value-of select="concat($lod, 'mirandais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'mwr'"><xsl:value-of select="concat($lod, 'marvari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'myn'"><xsl:value-of select="concat($lod, 'langues mayas', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'myv'"><xsl:value-of select="concat($lod, 'erza', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nah'"><xsl:value-of select="concat($lod, 'langues nahuatl', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nai'"><xsl:value-of select="concat($lod, 'langues indiennes d&#8217;Amérique du Nord', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nap'"><xsl:value-of select="concat($lod, 'napolitain', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nau'"><xsl:value-of select="concat($lod, 'nauri', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nav'"><xsl:value-of select="concat($lod, 'navaho', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nbl'"><xsl:value-of select="concat($lod, 'ndébélé du Sud', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nde'"><xsl:value-of select="concat($lod, 'ndébélé du Nord', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ndo'"><xsl:value-of select="concat($lod, 'ndonga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nds'"><xsl:value-of select="concat($lod, 'bas-allemand, bas-saxon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nep'"><xsl:value-of select="concat($lod, 'népalais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'new'"><xsl:value-of select="concat($lod, 'nepal ghasa, néwari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nia'"><xsl:value-of select="concat($lod, 'nias', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nic'"><xsl:value-of select="concat($lod, 'langues nigéro-congolaises', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'niu'"><xsl:value-of select="concat($lod, 'niué', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nno'"><xsl:value-of select="concat($lod, 'norvégien nynorsk', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nob'"><xsl:value-of select="concat($lod, 'norvégien bokmål', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nog'"><xsl:value-of select="concat($lod, 'nogaï', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'non'"><xsl:value-of select="concat($lod, 'vieux norrois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nor'"><xsl:value-of select="concat($lod, 'Norvégien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nqo'"><xsl:value-of select="concat($lod, 'n&#8217;ko', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nso'"><xsl:value-of select="concat($lod, 'sotho du Nord, pedi, sepedi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nub'"><xsl:value-of select="concat($lod, 'langues nubiennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nwc'"><xsl:value-of select="concat($lod, 'newari classique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nya'"><xsl:value-of select="concat($lod, 'chichewa, chewa, nyanja', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nym'"><xsl:value-of select="concat($lod, 'nyamwézi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nyn'"><xsl:value-of select="concat($lod, 'nyankolé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nyo'"><xsl:value-of select="concat($lod, 'nyoro', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'nzi'"><xsl:value-of select="concat($lod, 'nzéma', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oci'"><xsl:value-of select="concat($lod, 'occitan (après 1500), provençal', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oji'"><xsl:value-of select="concat($lod, 'ojibwa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ori'"><xsl:value-of select="concat($lod, 'oriya', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'orm'"><xsl:value-of select="concat($lod, 'galla', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'osa'"><xsl:value-of select="concat($lod, 'osage', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oss'"><xsl:value-of select="concat($lod, 'ossète', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ota'"><xsl:value-of select="concat($lod, 'turc ottoman (1500-1928)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'oto'"><xsl:value-of select="concat($lod, 'langues otomi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'paa'"><xsl:value-of select="concat($lod, 'langues papoues', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pag'"><xsl:value-of select="concat($lod, 'pangasinan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pal'"><xsl:value-of select="concat($lod, 'pèhlevî', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pam'"><xsl:value-of select="concat($lod, 'pampangan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pan'"><xsl:value-of select="concat($lod, 'pendjabi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pap'"><xsl:value-of select="concat($lod, 'papiamento', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pau'"><xsl:value-of select="concat($lod, 'paluan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'peo'"><xsl:value-of select="concat($lod, 'perse, vieux (environ 600-400 av. J.-C.)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'per'"><xsl:value-of select="concat($lod, 'persan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'phi'"><xsl:value-of select="concat($lod, 'langues philippines', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'phn'"><xsl:value-of select="concat($lod, 'phénicien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pli'"><xsl:value-of select="concat($lod, 'pali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pol'"><xsl:value-of select="concat($lod, 'polonais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pon'"><xsl:value-of select="concat($lod, 'pohnpei', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'por'"><xsl:value-of select="concat($lod, 'portugais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pra'"><xsl:value-of select="concat($lod, 'langues prâkrit', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pro'"><xsl:value-of select="concat($lod, 'provençal ancien, occitan ancien (jusqu&#8217;à 1500)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'pus'"><xsl:value-of select="concat($lod, 'Pachto (Pachtou)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'qaa-qtz'"><xsl:value-of select="concat($lod, 'réservé pour usage local', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'que'"><xsl:value-of select="concat($lod, 'quéchua', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'raj'"><xsl:value-of select="concat($lod, 'rajasthani', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rap'"><xsl:value-of select="concat($lod, 'rapanui', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rar'"><xsl:value-of select="concat($lod, 'māori des îles Cook, rarotongien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'roa'"><xsl:value-of select="concat($lod, 'langues romanes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'roh'"><xsl:value-of select="concat($lod, 'romanche', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rom'"><xsl:value-of select="concat($lod, 'romani, tsigane', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rum'"><xsl:value-of select="concat($lod, 'roumain, moldave', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'run'"><xsl:value-of select="concat($lod, 'roundi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rup'"><xsl:value-of select="concat($lod, 'aroumain, macédo-roumain', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'rus'"><xsl:value-of select="concat($lod, 'russe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sad'"><xsl:value-of select="concat($lod, 'sandawé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sag'"><xsl:value-of select="concat($lod, 'sango', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sah'"><xsl:value-of select="concat($lod, 'iakoute', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sai'"><xsl:value-of select="concat($lod, 'langues indiennes d&#8217;Amérique du Sud', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sal'"><xsl:value-of select="concat($lod, 'langues salishennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sam'"><xsl:value-of select="concat($lod, 'samaritain', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'san'"><xsl:value-of select="concat($lod, 'sanskrit', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sas'"><xsl:value-of select="concat($lod, 'sasak', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sat'"><xsl:value-of select="concat($lod, 'santal', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'scn'"><xsl:value-of select="concat($lod, 'sicilien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sco'"><xsl:value-of select="concat($lod, 'scots, écossais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sel'"><xsl:value-of select="concat($lod, 'selkoupe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sem'"><xsl:value-of select="concat($lod, 'langues sémitiques', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sga'"><xsl:value-of select="concat($lod, 'irlandais ancien (jusqu&#8217;à 900)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sgn'"><xsl:value-of select="concat($lod, 'langue des signes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'shn'"><xsl:value-of select="concat($lod, 'shan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'shp'"><xsl:value-of select="concat($lod, 'Shipibo-conibo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sid'"><xsl:value-of select="concat($lod, 'sidamo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sin'"><xsl:value-of select="concat($lod, 'singhalais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sio'"><xsl:value-of select="concat($lod, 'langues sioux', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sit'"><xsl:value-of select="concat($lod, 'langues sino-tibétaines', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sla'"><xsl:value-of select="concat($lod, 'langues slaves', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'slo'"><xsl:value-of select="concat($lod, 'slovaque', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'slv'"><xsl:value-of select="concat($lod, 'slovène', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sma'"><xsl:value-of select="concat($lod, 'sâme du Sud', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sme'"><xsl:value-of select="concat($lod, 'sâme du Nord', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smi'"><xsl:value-of select="concat($lod, 'langues sâmes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smj'"><xsl:value-of select="concat($lod, 'sâme de Lule', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smn'"><xsl:value-of select="concat($lod, 'sâme d&#8217;Inari', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'smo'"><xsl:value-of select="concat($lod, 'samoan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sms'"><xsl:value-of select="concat($lod, 'sâme skolt', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sna'"><xsl:value-of select="concat($lod, 'shona', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'snd'"><xsl:value-of select="concat($lod, 'sindhi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'snk'"><xsl:value-of select="concat($lod, 'soninké', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sog'"><xsl:value-of select="concat($lod, 'sogdien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'som'"><xsl:value-of select="concat($lod, 'somali', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'son'"><xsl:value-of select="concat($lod, 'langues songhai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sot'"><xsl:value-of select="concat($lod, 'sotho du Sud', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'spa'"><xsl:value-of select="concat($lod, 'espagnol, castillan', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'srd'"><xsl:value-of select="concat($lod, 'sarde', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'srn'"><xsl:value-of select="concat($lod, 'sranan tongo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'srp'"><xsl:value-of select="concat($lod, 'serbe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'srr'"><xsl:value-of select="concat($lod, 'sérère', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ssa'"><xsl:value-of select="concat($lod, 'langues nilo-sahariennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ssw'"><xsl:value-of select="concat($lod, 'swati', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'suk'"><xsl:value-of select="concat($lod, 'sukuma', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sun'"><xsl:value-of select="concat($lod, 'soundanais', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sus'"><xsl:value-of select="concat($lod, 'soussou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'sux'"><xsl:value-of select="concat($lod, 'sumérien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'swa'"><xsl:value-of select="concat($lod, 'swahili', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'swe'"><xsl:value-of select="concat($lod, 'suédois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'syc'"><xsl:value-of select="concat($lod, 'syriaque classique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'syr'"><xsl:value-of select="concat($lod, 'syriaque', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tah'"><xsl:value-of select="concat($lod, 'tahitien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tai'"><xsl:value-of select="concat($lod, 'langues tai', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tam'"><xsl:value-of select="concat($lod, 'tamoul', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tat'"><xsl:value-of select="concat($lod, 'tatar', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tel'"><xsl:value-of select="concat($lod, 'télougou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tem'"><xsl:value-of select="concat($lod, 'timné', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ter'"><xsl:value-of select="concat($lod, 'téréno', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tet'"><xsl:value-of select="concat($lod, 'tétun', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tgk'"><xsl:value-of select="concat($lod, 'tadjik', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tgl'"><xsl:value-of select="concat($lod, 'tagalog', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tha'"><xsl:value-of select="concat($lod, 'thaï', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tib'"><xsl:value-of select="concat($lod, 'tibétain', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tig'"><xsl:value-of select="concat($lod, 'tigré', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tir'"><xsl:value-of select="concat($lod, 'tigrigna', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tiv'"><xsl:value-of select="concat($lod, 'tiv', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tkl'"><xsl:value-of select="concat($lod, 'tokélaouéen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tlh'"><xsl:value-of select="concat($lod, 'klingon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tli'"><xsl:value-of select="concat($lod, 'tlingit', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tmh'"><xsl:value-of select="concat($lod, 'tamacheq', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tog'"><xsl:value-of select="concat($lod, 'tonga (Nyasa)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ton'"><xsl:value-of select="concat($lod, 'tongan (Îles Tonga)', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tpi'"><xsl:value-of select="concat($lod, 'tok pisin', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tsi'"><xsl:value-of select="concat($lod, 'tsimchiane', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tsn'"><xsl:value-of select="concat($lod, 'tswana', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tso'"><xsl:value-of select="concat($lod, 'tsonga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tuk'"><xsl:value-of select="concat($lod, 'turkmène', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tum'"><xsl:value-of select="concat($lod, 'tumbuka', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tup'"><xsl:value-of select="concat($lod, 'langues tupi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tur'"><xsl:value-of select="concat($lod, 'turc', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tut'"><xsl:value-of select="concat($lod, 'langues altaïques', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tvl'"><xsl:value-of select="concat($lod, 'touvalouéen', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'twi'"><xsl:value-of select="concat($lod, 'tchi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'tyv'"><xsl:value-of select="concat($lod, 'touvain', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'udm'"><xsl:value-of select="concat($lod, 'oudmourte', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'uga'"><xsl:value-of select="concat($lod, 'ougaritique', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'uig'"><xsl:value-of select="concat($lod, 'ouïgour', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ukr'"><xsl:value-of select="concat($lod, 'ukrainien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'umb'"><xsl:value-of select="concat($lod, 'umbundu', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'und'"><xsl:value-of select="concat($lod, 'langue indéterminée', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'urd'"><xsl:value-of select="concat($lod, 'ourdou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'uzb'"><xsl:value-of select="concat($lod, 'ouzbek', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vai'"><xsl:value-of select="concat($lod, 'vaï', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ven'"><xsl:value-of select="concat($lod, 'venda', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vie'"><xsl:value-of select="concat($lod, 'vietnamien', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vol'"><xsl:value-of select="concat($lod, 'volapük', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'vot'"><xsl:value-of select="concat($lod, 'vote', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wak'"><xsl:value-of select="concat($lod, 'langues wakashennes', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wal'"><xsl:value-of select="concat($lod, 'wolaitta, wolaytta', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'war'"><xsl:value-of select="concat($lod, 'waray', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'was'"><xsl:value-of select="concat($lod, 'washo', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wel'"><xsl:value-of select="concat($lod, 'gallois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wen'"><xsl:value-of select="concat($lod, 'langues sorabe', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wln'"><xsl:value-of select="concat($lod, 'wallon', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'wol'"><xsl:value-of select="concat($lod, 'wolof', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'xal'"><xsl:value-of select="concat($lod, 'kalmouk, oïrat', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'xho'"><xsl:value-of select="concat($lod, 'xhosa', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yao'"><xsl:value-of select="concat($lod, 'yao', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yap'"><xsl:value-of select="concat($lod, 'yapois', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yid'"><xsl:value-of select="concat($lod, 'yiddish', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'yor'"><xsl:value-of select="concat($lod, 'yorouba', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'ypk'"><xsl:value-of select="concat($lod, 'langues yupik', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zap'"><xsl:value-of select="concat($lod, 'zapotèque', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zbl'"><xsl:value-of select="concat($lod, 'symboles Bliss, Bliss', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zen'"><xsl:value-of select="concat($lod, 'zénaga', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zgh'"><xsl:value-of select="concat($lod, 'amazighe standard marocain', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zha'"><xsl:value-of select="concat($lod, 'zhuang, chuang', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'znd'"><xsl:value-of select="concat($lod, 'langues zandé', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zul'"><xsl:value-of select="concat($lod, 'zoulou', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zun'"><xsl:value-of select="concat($lod, 'zuñi', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zxx'"><xsl:value-of select="concat($lod, 'Pas de contenu linguistique, non applicable', $break)"/></xsl:when>
                <xsl:when test="@langcode = 'zza'"><xsl:value-of select="concat($lod, 'zaza, dimili, dimli, kirdki, kirmanjki, zazaki', $break)"/></xsl:when>
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
