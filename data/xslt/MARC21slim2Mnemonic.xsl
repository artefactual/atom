<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="marc">
	<xsl:import href="MARC21slimUtils.xsl"/>
	<xsl:output method="text" encoding="UTF-8" indent="yes"/>
	

	<xsl:template match="/">
	<xsl:apply-templates/>
	</xsl:template>
	
	<xsl:template match="marc:record">
	
	  <!--================================================
	      = Create the MARC:LEADER information
	      ==============================================-->

	  <xsl:for-each select="marc:leader">=LDR<xsl:text>  </xsl:text><xsl:value-of select="." /><xsl:text>&#13;&#10;</xsl:text></xsl:for-each>

	  <!--================================================
	      = Create the CONTROLFIELD INFORMATION
	      ==============================================-->


	  <xsl:for-each select="marc:controlfield">
		  <xsl:if test="@tag&lt;10">
				<xsl:if test="normalize-space(.)!=''">
				<xsl:text>=</xsl:text><xsl:value-of select="@tag" /><xsl:text>  </xsl:text>
					<xsl:call-template name="replaceText">
							<xsl:with-param name="tstring" select="." />
							<xsl:with-param name="text1" select="'$'" />
							<xsl:with-param name="text2" select="'{dollar}'" />
						</xsl:call-template>
				<xsl:text>&#13;&#10;</xsl:text>
				</xsl:if>
		  </xsl:if>
	  </xsl:for-each>

	  <!--================================================
	      = Create the GENERAL SUBFIELD INFORMATION
	      ==============================================-->

	  <xsl:for-each select="marc:datafield">
		  
		  <xsl:if test="marc:subfield!=''">
			<xsl:if test="@tag&gt;=10">

				
				<xsl:variable name="cind1" select="@ind1" />
				<xsl:variable name="cind2" select="@ind2" />
				
				<xsl:choose>
				<xsl:when test="$cind1=' ' and $cind2=' '">
					<xsl:text>=</xsl:text>
					<xsl:value-of select="@tag" />
					<xsl:text>  \\</xsl:text>
					<xsl:for-each select="marc:subfield">
					<xsl:text>$</xsl:text>
					<xsl:value-of select="@code" />
					<xsl:call-template name="replaceText">
						<xsl:with-param name="tstring" select="translate(.,'&#13;&#10;&#9;','')" />
						<xsl:with-param name="text1" select="'$'" />
						<xsl:with-param name="text2" select="'{dollar}'" />
						</xsl:call-template>
					</xsl:for-each>
				</xsl:when>
				   
				<xsl:when test="$cind1=' ' and $cind2!=' '">
					<xsl:text>=</xsl:text>
					<xsl:value-of select="@tag" />
					<xsl:text>  \</xsl:text><xsl:value-of select="$cind2" />
					<xsl:for-each select="marc:subfield">
					<xsl:text>$</xsl:text>
					<xsl:value-of select="@code" />
					<xsl:call-template name="replaceText">
						<xsl:with-param name="tstring" select="normalize-space(translate(.,'&#13;&#10;&#9;',''))" />
						<xsl:with-param name="text1" select="'$'" />
						<xsl:with-param name="text2" select="'{dollar}'" />
						</xsl:call-template>
					</xsl:for-each>
				</xsl:when>
				   
				<xsl:when test="$cind1!=' ' and $cind2=' '">
					<xsl:text>=</xsl:text>
					<xsl:value-of select="@tag" />
					<xsl:text>  </xsl:text><xsl:value-of select="$cind1" /><xsl:text>\</xsl:text>
					<xsl:for-each select="marc:subfield">
					<xsl:text>$</xsl:text>
					<xsl:value-of select="@code" />
					<xsl:call-template name="replaceText">
						<xsl:with-param name="tstring" select="normalize-space(translate(.,'&#13;&#10;&#9;',''))" />
						<xsl:with-param name="text1" select="'$'" />
						<xsl:with-param name="text2" select="'{dollar}'" />
						</xsl:call-template>
					</xsl:for-each>
				</xsl:when>
							   
				<xsl:when test="$cind1!=' ' and $cind2!=' '">
					<xsl:text>=</xsl:text>
					<xsl:value-of select="@tag" />
					<xsl:text>  </xsl:text><xsl:value-of select="$cind1" /><xsl:value-of select="$cind2" />
					<xsl:for-each select="marc:subfield">
					<xsl:text>$</xsl:text>
					<xsl:value-of select="@code" />
					<xsl:call-template name="replaceText">
						<xsl:with-param name="tstring" select="normalize-space(translate(.,'&#13;&#10;&#9;',''))" />
						<xsl:with-param name="text1" select="'$'" />
						<xsl:with-param name="text2" select="'{dollar}'" />
						</xsl:call-template>				   
					</xsl:for-each>
				</xsl:when>
				</xsl:choose>
			</xsl:if>	
			<xsl:text>&#13;&#10;</xsl:text>
		</xsl:if>
        </xsl:for-each>	
	  <xsl:text>&#13;&#10;</xsl:text>
	</xsl:template>
	
</xsl:stylesheet>
