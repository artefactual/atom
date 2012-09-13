<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="marc">
	<xsl:import href="MARC21slimUtils.xsl"/>
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:strip-space elements="*"/>
	<xsl:template match="/ead">
		<marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
			<marc:record>
				<marc:leader>
					<xsl:text>01125cam a2200289 a 4500</xsl:text>
				</marc:leader>
				<marc:controlfield tag="008">
					<xsl:text>040320u9999\\\\xx\\\\\\\\\u\\\\\\\\und\d</xsl:text>
				</marc:controlfield>
				<marc:datafield tag="040" ind1="" ind2="">
					<marc:subfield code="a">ORE</marc:subfield>
					<marc:subfield code="c">ORE</marc:subfield>
				</marc:datafield>
				<xsl:if test="//unitid">
					<marc:datafield tag="099" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//unitid/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="//origination">
					<marc:datafield tag="100" ind1="1" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//persname/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="//unittitle">
					<marc:datafield tag="245" ind1="1" ind2="0">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//unittitle/.)"/>
						</marc:subfield>
						<xsl:if test="//unitdate">
							<marc:subfield code="f"><xsl:value-of select="normalize-space(//unitdate/.)"/>.</marc:subfield>
						</xsl:if>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="archdesc/did/physdesc">
					<marc:datafield tag="300" ind1="" ind2="">
						<xsl:for-each select="archdesc/did/physdesc/extent">
							<marc:subfield>
								<xsl:attribute name="code">
									<xsl:value-of select="substring(@encodinganalog,5,1)"/>
								</xsl:attribute>
								<xsl:value-of select="normalize-space(.)"/>
							</marc:subfield>
						</xsl:for-each>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="archdesc/arrangement">
					<marc:datafield tag="351" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/arrangement/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="archdesc/dsc">
					<xsl:for-each select="archdesc/dsc/c01">
						<marc:datafield tag="505" ind1="0" ind2="0">
							<marc:subfield code="t">
								<xsl:value-of select="normalize-space(did/unitid/.)"/>
								<xsl:text>: </xsl:text>
								<xsl:value-of select="normalize-space(did/unittitle/.)"/>
								<xsl:text>, </xsl:text>
								<xsl:value-of select="normalize-space(did/unitdate/.)"/>
							</marc:subfield>
						</marc:datafield>
						<xsl:for-each select="c02">
							<marc:datafield tag="505" ind1="0" ind2="0">
								<marc:subfield code="g">
									<xsl:text>&amp;nbsp;&amp;nbsp;&amp;nbsp;box </xsl:text>
									<xsl:value-of select="normalize-space(did/container/.)"/>
									<xsl:text> -- </xsl:text>
								</marc:subfield>
								<marc:subfield code="t">
									<xsl:value-of select="normalize-space(did/unittitle/.)"/>
									<xsl:if test="did/unitdate/.!=''">
										<xsl:text>, </xsl:text>
										<xsl:value-of select="normalize-space(did/unitdate/.)"/>
										<xsl:text>. </xsl:text>
									</xsl:if>
								</marc:subfield>
								<xsl:if test="did/physdesc/extent">
									<marc:subfield code="g">
										<xsl:value-of select="normalize-space(did/physdesc/extent/.)"/>
										<xsl:text>.</xsl:text>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="did/physdesc/dimensions">
									<marc:subfield code="g">
										<xsl:value-of select="normalize-space(did/physdesc/dimensions/.)"/>
										<xsl:text>.</xsl:text>
									</marc:subfield>
								</xsl:if>
							</marc:datafield>
							<xsl:for-each select="c03">
								<marc:datafield tag="505" ind1="0" ind2="0">
									<marc:subfield code="g">
										<xsl:text>&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;&amp;nbsp;</xsl:text>
										<xsl:value-of select="normalize-space(did/container/.)"/>
										<xsl:text> -- </xsl:text>
									</marc:subfield>
									<marc:subfield code="t">
										<xsl:value-of select="normalize-space(did/unittitle/.)"/>
										<xsl:if test="did/unitdate/.!=''">
											<xsl:text>, </xsl:text>
											<xsl:value-of select="normalize-space(did/unitdate/.)"/>
											<xsl:text>. </xsl:text>
										</xsl:if>
									</marc:subfield>
									<xsl:if test="did/physdesc/extent">
										<marc:subfield code="g">
											<xsl:value-of select="normalize-space(did/physdesc/extent/.)"/>
											<xsl:text>.</xsl:text>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="did/physdesc/dimensions">
										<marc:subfield code="g">
											<xsl:value-of select="normalize-space(did/physdesc/dimensions/.)"/>
											<xsl:text>.</xsl:text>
										</marc:subfield>
									</xsl:if>
								</marc:datafield>
							</xsl:for-each>
						</xsl:for-each>
					</xsl:for-each>
				</xsl:if>
				<xsl:if test="archdesc/prefercite">
					<marc:datafield tag="524" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/prefercite/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
<!--We could enter the scope and contents note, but I've been
                    told that the abstract note is just as good and possibly
                    preferred-->
				<xsl:if test="//abstract">
					<xsl:if test="//abstract/@encodinganalog='5203_'">
						<marc:datafield tag="520" ind1="3" ind2="">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(//abstract/.)"/>
							</marc:subfield>
						</marc:datafield>
					</xsl:if>
				</xsl:if>
				<xsl:if test="//bioghist">
					<marc:datafield tag="545" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//bioghist/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="archdesc/relatedmaterial">
					<marc:datafield tag="544" ind1="1" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/relatedmaterial/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="archdesc/custodhist">
					<marc:datafield tag="561" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/custodhist/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:for-each select="//controlaccess/persname | //controlaccess/corpname">
					<xsl:sort select="@encodinganalog"/>
					<xsl:choose>
						<xsl:when test="@encodinganalog='600'">
							<marc:datafield tag="600" ind1="1" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)"/>
								</marc:subfield>
							</marc:datafield>
						</xsl:when>
						<xsl:when test="@encodinganalog='610'">
							<marc:datafield tag="610" ind1="2" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)"/>
								</marc:subfield>
							</marc:datafield>
						</xsl:when>
						<xsl:when test="@encodinganalog='611'">
							<marc:datafield tag="611" ind1="2" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)"/>
								</marc:subfield>
							</marc:datafield>
						</xsl:when>
						<xsl:when test="@encodinganalog='700'">
							<marc:datafield tag="700" ind1="1" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)"/>
								</marc:subfield>
							</marc:datafield>
						</xsl:when>
						<xsl:when test="@encodinganalog='710'">
							<marc:datafield tag="710" ind1="2" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)"/>
								</marc:subfield>
							</marc:datafield>
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
				</xsl:for-each>
				<xsl:for-each select="//controlaccess/genreform">
					<xsl:if test="//@encodinganalog='655'">
						<marc:datafield tag="655" ind1="" ind2="7">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(.)"/>
							</marc:subfield>
							<marc:subfield code="2">
								<xsl:value-of select="normalize-space(@source)"/>
							</marc:subfield>
						</marc:datafield>
					</xsl:if>
				</xsl:for-each>
				<xsl:for-each select="//controlaccess/subject[@source='lcsh'] | //controlaccess/geogname[@source='lcsh']">
					<xsl:choose>
						<xsl:when test="//@source='lcsh'">
							<xsl:if test="//@encodinganalog='650'">
								<marc:datafield tag="690" ind1="" ind2="">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)"/>
									</marc:subfield>
								</marc:datafield>
							</xsl:if>
							<xsl:if test="//@encodinganalog='651'">
								<marc:datafield tag="691" ind1="" ind2="">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)"/>
									</marc:subfield>
								</marc:datafield>
							</xsl:if>
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
				</xsl:for-each>
				<xsl:for-each select="//controlaccess/title">
					<xsl:choose>
						<xsl:when test="//@rules='aacr2'">
							<xsl:if test="//@encodinganalog='740'">
								<marc:datafield tag="740" ind1="0" ind2="4">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)"/>
									</marc:subfield>
								</marc:datafield>
							</xsl:if>
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
				</xsl:for-each>
				<xsl:if test="eadheader/eadid">
					<marc:datafield tag="856" ind1="4" ind2="1">
						<marc:subfield code="u">
							<xsl:value-of select="normalize-space(eadheader/eadid/@url)"/>
						</marc:subfield>
						<marc:subfield code="z">View the finding aid online.</marc:subfield>
					</marc:datafield>
				</xsl:if>
			</marc:record>
		</marc:collection>
	</xsl:template>
	<xsl:template match="head"/>
	<xsl:template match="p">
		<xsl:value-of select="."/>
	</xsl:template>
</xsl:stylesheet>
