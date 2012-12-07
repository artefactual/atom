<?xml version='1.0' encoding='utf-8'?>

<!--============================================

	export-postprocess.xsl - Stylesheet to normalize XML for export

	This file is part of the Access to Memory (AtoM).
	Copyright (C) 2006-2008 Peter Van Garderen <peter@artefactual.com>

==============================================-->


<xsl:stylesheet version="1.0" 
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:php="http://php.net/xsl"
	exclude-result-prefixes="xsl php">

<xsl:output method='xml' 
			version='1.0' 
			encoding='UTF-8' 
			indent='yes'
			omit-xml-declaration='no'/>

<xsl:strip-space elements="*"/>

<xsl:param name="doctype"/>

<!--============================================
	START TRANSFORMATION AT THE ROOT NODE
==============================================-->
<xsl:template match="/">
	<!-- output the doctype -->
	<xsl:if test="$doctype">
		<xsl:value-of disable-output-escaping="yes" select="php:functionString('html_entity_decode', '&lt;')"/>
		<xsl:value-of disable-output-escaping="yes" select="$doctype"/>
		<xsl:value-of disable-output-escaping="yes" select="php:functionString('html_entity_decode', '&gt;')"/>
	</xsl:if>

	<xsl:apply-templates/>
</xsl:template>

<!-- REMOVE COMMENT NODES-->
<xsl:template match="comment()">
	<xsl:apply-templates/>
</xsl:template>

<!-- REMOVE EMPTY NODES-->
<xsl:template match="*[. = '']|@*[. = '']">
	<xsl:apply-templates/>
</xsl:template>

<!--============================================
	IDENTITY TRANSFORM EVERYTHING ELSE
==============================================-->
 <xsl:template match="node()|@*">
   <xsl:copy>
   <xsl:apply-templates select="@*"/>
   <xsl:apply-templates/>
   </xsl:copy>
 </xsl:template>

</xsl:stylesheet>
