<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
		http://www.openarchives.org/OAI/2.0/oai_dc.xsd" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:marc="http://www.loc.gov/MARC21/slim">
	<xsl:import href="MARC21slimUtils.xsl" />
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<marc:collection xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
			<xsl:apply-templates />
		</marc:collection>
	</xsl:template>
	<xsl:template name="OAI-PMH">
		<xsl:for-each select="ListRecords/record/metadata/marc:record">
			<xsl:apply-templates />
		</xsl:for-each>
		<xsl:for-each select="GetRecord/record/metadata/marc:record">
			<xsl:apply-templates />
		</xsl:for-each>
	</xsl:template>
	<xsl:template match="text()" />
	<xsl:template match="marc:record">
		<marc:record>
			<xsl:apply-templates />
		</marc:record>
	</xsl:template>
	
	<xsl:template match="marc:leader">
		<marc:leader><xsl:value-of select="." /></marc:leader>
	</xsl:template>
	<xsl:template match="marc:controlfield">
		<marc:controlfield>
			<xsl:attribute name="tag"><xsl:value-of select="@tag" /></xsl:attribute>
			<xsl:value-of select="." />
		</marc:controlfield>
	</xsl:template>
	
	<xsl:template match="marc:datafield">
		<marc:datafield>
			<xsl:attribute name="tag">
				<xsl:value-of select="@tag" />
			</xsl:attribute>
			<xsl:attribute name="ind1">
				<xsl:value-of select="@ind1" />
			</xsl:attribute>
			<xsl:attribute name="ind2">
				<xsl:value-of select="@ind2" />
			</xsl:attribute>
			<xsl:for-each select="./marc:subfield">
				<marc:subfield>
					<xsl:attribute name="code">
						<xsl:value-of select="@code" />
					</xsl:attribute>
					<xsl:value-of select="." />
				</marc:subfield>
			</xsl:for-each>
		</marc:datafield>
	</xsl:template>
</xsl:stylesheet>
