<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:marc="http://www.loc.gov/MARC21/slim"  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="marc">
	<!--
*******************************************************************
* FILE:             EAD2MARCXML.xsl			                      *
*                                                                 *
* VERSION:          200803                                        *
*                                                                 *
* AUTHOR:           Mark Carlson                                  *
*                   Special Collections Computer Support Analyst  *
*                   University of Washington Libraries            *
*                   carlsonm@u.washington.edu                     *
*                                                                 *
*                                                                 *
* GENERAL COMMENTS: This stylesheet is based on the EAD to MARC   *
*                   crosswalk found at:                           *
*                   http://www.loc.gov/ead/tglib/appendix_a.html  *
*                   Because of the built-in flexibility in EAD    *
*                   encoding, some functionality may need to be   *
*                   altered to fit your institution's encoding    *
*                   practices.   For more information about       *
*                   user specific configuration, please consult   *
*                   the readme.txt file distributed with this     *
*                   file.                                         *
*                                                                 *
* DISTRIBUTION:     This file may be freely distributed as long   *
*                   as this section remains in the file.          *
*******************************************************************
-->
	<!-- The following file can be found on the WWW at http://www.loc.gov/standards/marcxml/xslt/MARC21slimUtils.xsl -->
	<xsl:include href="MARC21slimUtils.xsl"/>

	<!-- The following are for user configuration settings -->
	<xsl:param name="includeMARCExtrasFile">no</xsl:param>
	<xsl:param name="includeMARC856">no</xsl:param>
	<xsl:param name="MARC856text">Connect to the online finding aid for this collection.</xsl:param>

	<xsl:variable name="lc">abcdefghijklmnopqrstuvwxyz</xsl:variable>
	<xsl:variable name="uc">ABCDEFGHIJKLMNOPQRSTUVWXYZ</xsl:variable>
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:strip-space elements="*" />
	<xsl:template match="ead">
		<marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
			<marc:record>
				<marc:leader>
					<xsl:text>01125cpcaa2200289Ia 4500</xsl:text>
				</marc:leader>
				<!--===================================== M A R C   0 0 8 =====================================-->
				<marc:controlfield tag="008">
					<xsl:text>\\\\\\</xsl:text>
					<!-- This section attempts to extract the date information from the normal attribute in <unitdate> -->
					<xsl:choose>
						<xsl:when test="archdesc/did/unitdate[@type='inclusive']/@normal">
							<xsl:choose>
								<xsl:when test="contains(archdesc/did/unitdate[@type='inclusive']/@normal, '/')">
									<xsl:value-of select="concat('i', substring-before(archdesc/did/unitdate[@type='inclusive']/@normal, '/'), substring-after(archdesc/did/unitdate[@type='inclusive']/@normal, '/'))"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="concat('s', archdesc/did/unitdate[@type='inclusive'], '\\\\')"/>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<xsl:text>\9999\\\\</xsl:text>
						</xsl:otherwise>
					</xsl:choose>
					<xsl:text>xx\\\\\\\\\u\\\\\\\\</xsl:text>
					<xsl:choose>
						<xsl:when test="string(archdesc/did/langmaterial/language[1]/@langcode)">
							<xsl:value-of select="normalize-space(archdesc/did/langmaterial/language[1]/@langcode)"/>
						</xsl:when>
						<xsl:otherwise>eng</xsl:otherwise>
					</xsl:choose>
					<xsl:text>\d</xsl:text>
				</marc:controlfield>
				<!--===================================== M A R C   0 4 0 =====================================-->
				<marc:datafield tag="040" ind1="" ind2="">
					<xsl:choose>
						<xsl:when test="not(contains(//eadid/@mainagencycode, '-'))">
							<marc:subfield code="a">
								<xsl:value-of select="translate(substring(//eadid/@mainagencycode, 1), $lc, $uc)"/>
							</marc:subfield>
							<marc:subfield code="c">
								<xsl:value-of select="translate(substring(//eadid/@mainagencycode, 1), $lc, $uc)"/>
							</marc:subfield>
						</xsl:when>
						<xsl:when test="contains(//eadid/@mainagencycode, '-')">
							<marc:subfield code="a">
								<xsl:value-of select="translate(substring-before(//eadid/@mainagencycode, '-'), $lc, $uc)"/>
							</marc:subfield>
							<marc:subfield code="c">
								<xsl:value-of select="translate(substring-before(//eadid/@mainagencycode, '-'), $lc, $uc)"/>
							</marc:subfield>
						</xsl:when>
					</xsl:choose>
				</marc:datafield>
				<!--===================================== M A R C   0 4 1 =====================================-->
				<!--
				/******************************************************/
				/ This is triggered by the presence of 2 or more       /
				/ <language> elements in archdesc/did/langmaterial.    /
				/ The output is taken from the @langcode attribute     /
				/******************************************************/
				-->
				<xsl:if test="string(archdesc/did/langmaterial/language[2])">
					<marc:datafield tag="041" ind1="0" ind2="">
						<xsl:for-each select="archdesc/did/langmaterial/language">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(@langcode)" />
							</marc:subfield>
						</xsl:for-each>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   1 x x =====================================-->
				<xsl:choose>
					<xsl:when test="string(archdesc/did/origination/persname)">
						<marc:datafield tag="100" ind1="1" ind2="">
							<xsl:choose>
								<xsl:when test="contains(., '$')">
									<xsl:variable name="term" select="translate(normalize-space(archdesc/did/origination/persname[1]), '&#x000D;', '')"/>
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<marc:subfield code="a">
										<xsl:value-of select="translate(normalize-space(archdesc/did/origination/persname[1]), '&#x000D;', '')"/>
										<xsl:call-template name="fullstop">
											<xsl:with-param name="input" select="normalize-space(archdesc/did/origination/persname[1])"/>
										</xsl:call-template>
									</marc:subfield>
								</xsl:otherwise>
							</xsl:choose>
						</marc:datafield>
					</xsl:when>
					<xsl:when test="string(archdesc/did/origination/famname)">
						<marc:datafield tag="100" ind1="3" ind2="">
							<xsl:choose>
								<xsl:when test="contains(., '$')">
									<xsl:variable name="term" select="translate(normalize-space(archdesc/did/origination/famname), '&#x000D;', '')"/>
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<marc:subfield code="a">
										<xsl:value-of select="translate(normalize-space(archdesc/did/origination/famname[1]), '&#x000D;', '')"/>
										<xsl:call-template name="fullstop">
											<xsl:with-param name="input" select="normalize-space(archdesc/did/origination/famname[1])"/>
										</xsl:call-template>
									</marc:subfield>
								</xsl:otherwise>
							</xsl:choose>
						</marc:datafield>
					</xsl:when>
					<xsl:when test="string(archdesc/did/origination/corpname)">
						<xsl:choose>
							<!--
						/******************************************/
						/ For <corpname>, defaults to MARC 110     /
						/ when encodinganalog is blank or not      /
						/ encoded as 111                           /
						/******************************************/
						-->
							<xsl:when test="archdesc/did/origination/corpname/@encodinganalog='111'">
								<marc:datafield tag="111" ind1="2" ind2="">
									<xsl:choose>
										<xsl:when test="contains(., '$')">
											<xsl:variable name="term" select="translate(normalize-space(archdesc/did/origination/corpname/@encodinganalog='111'), '&#x000D;', '')"/>
											<xsl:call-template name="processSubfields">
												<xsl:with-param name="term" select="$term"/>
												<xsl:with-param name="fullstop" select="'yes'"/>
											</xsl:call-template>
										</xsl:when>
										<xsl:otherwise>
											<marc:subfield code="a">
												<xsl:value-of select="translate(normalize-space(archdesc/did/origination/corpname), '&#x000D;', '')" />
												<xsl:call-template name="fullstop">
													<xsl:with-param name="input" select="normalize-space(archdesc/did/origination/corpname)"/>
												</xsl:call-template>
											</marc:subfield>
										</xsl:otherwise>
									</xsl:choose>
								</marc:datafield>
							</xsl:when>
							<xsl:otherwise>
								<marc:datafield tag="110" ind1="2" ind2="">
									<xsl:choose>
										<xsl:when test="contains(., '$')">
											<xsl:variable name="term" select="translate(normalize-space(archdesc/did/origination/corpname/@encodinganalog='111'), '&#x000D;', '')"/>
											<xsl:call-template name="processSubfields">
												<xsl:with-param name="term" select="$term"/>
												<xsl:with-param name="fullstop" select="'yes'"/>
											</xsl:call-template>
										</xsl:when>
										<xsl:otherwise>
											<marc:subfield code="a">
												<xsl:value-of select="translate(normalize-space(archdesc/did/origination/corpname), '&#x000D;', '')" />
												<xsl:call-template name="fullstop">
													<xsl:with-param name="input" select="normalize-space(archdesc/did/origination/corpname)"/>
												</xsl:call-template>
											</marc:subfield>
										</xsl:otherwise>
									</xsl:choose>
								</marc:datafield>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<!--
					/*********************************************************/
					/ When there is an <origination> element mapped to MARC   /
					/ but no child <persname>, <corpname>, <name> element     /
					/*********************************************************/
					-->
					<xsl:when test="string(archdesc/did/origination[@encodinganalog])">
						<marc:datafield tag="{archdesc/did/origination/@encodinganalog}">
							<xsl:choose>
								<xsl:when test="starts-with(archdesc/did/origination/@encodinganalog, '10')">
									<xsl:attribute name="ind1">1</xsl:attribute>
								</xsl:when>
								<xsl:otherwise>
									<xsl:attribute name="ind1">2</xsl:attribute>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:attribute name="ind2"></xsl:attribute>
							<marc:subfield code="a">
								<xsl:value-of select="translate(normalize-space(archdesc/did/origination), '&#x000D;', '')"/>
								<xsl:call-template name="fullstop">
									<xsl:with-param name="input" select="normalize-space(archdesc/did/origination)"/>
								</xsl:call-template>
							</marc:subfield>
						</marc:datafield>
					</xsl:when>
				</xsl:choose>
				<!--===================================== M A R C   1 3 0 / 2 4 5 =====================================-->
				<xsl:if test="archdesc/did/unittitle">
					<xsl:if test="archdesc/did/unittitle/@encodinganalog='130'">
						<marc:datafield tag="130" ind1="0" ind2="">
							<marc:subfield code="a">
								<xsl:value-of select="translate(normalize-space(archdesc/did/unittitle[@encodinganalog='130']), '&#x000D;&#x00a0;', '')" />
							</marc:subfield>
						</marc:datafield>
					</xsl:if>
					<marc:datafield tag="245">
						<xsl:choose>
							<xsl:when test="string(archdesc/did/origination) or archdesc/did/unittitle/@encodinganalog='130'">
								<xsl:attribute name="ind1">1</xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="ind1">0</xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:attribute name="ind2">0</xsl:attribute>
						<marc:subfield code="a">
							<xsl:variable name="unittitle">
								<xsl:value-of select="translate(normalize-space(archdesc/did/unittitle[not(@encodinganalog='130')]), '&#x000D;', '')"/>
								<xsl:text>,</xsl:text>
							</xsl:variable>
							<xsl:value-of select="translate($unittitle, '&#x000D;', '')"/>
						</marc:subfield>
						<xsl:if test="string(archdesc/did/unitdate[@type='inclusive'])">
							<marc:subfield code="f">
								<xsl:variable name="unitdatei">
									<xsl:value-of select="translate(normalize-space(archdesc/did/unitdate[@type='inclusive']), '&#x000D;', '')" />
									<xsl:if test="not(string(archdesc/did/unitdate[@type='bulk']))">
										<xsl:text>.</xsl:text>
									</xsl:if>
								</xsl:variable>
								<xsl:value-of select="translate($unitdatei, '&#x000D;', '')"/>
							</marc:subfield>
						</xsl:if>
						<xsl:if test="string(archdesc/did/unitdate[@type='bulk'])">
							<marc:subfield code="g">
								<xsl:variable name="unitdateb">
									<xsl:value-of select="concat('(bulk ', translate(normalize-space(archdesc/did/unitdate[@type='bulk']), '&#x000D;', ''), ').')" />
								</xsl:variable>
								<xsl:value-of select="translate($unitdateb, '&#x000D;', '')"/>
							</marc:subfield>
						</xsl:if>
					</marc:datafield>
					<!--===================================== M A R C   2 4 6 =====================================-->
					<!--
					/***************************************************************/
					/ This generates a MARC 246 field.  If the collection title     /
					/ contains the word "Papers" or "Records", then this text is    /
					/ used to generate subfield a.  Otherwise, subfield a is left   /
					/ blank and must be filled in manually.                         /
					/***************************************************************/
					-->
					<marc:datafield tag="246" ind1="3" ind2="0">
						<xsl:choose>
							<xsl:when test="contains(archdesc/did/unittitle, 'Records')">
								<marc:subfield code="a">Records</marc:subfield>
							</xsl:when>
							<xsl:when test="contains(archdesc/did/unittitle, 'Papers')">
								<marc:subfield code="a">Papers</marc:subfield>
							</xsl:when>
							<!-- Defaulting in blank subfield a if not "Papers" or "Records" -->
							<xsl:otherwise>
								<marc:subfield code="a"></marc:subfield>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="string(archdesc/did/unitdate[@type='inclusive'])">
							<marc:subfield code="f">
								<xsl:value-of select="translate(normalize-space(archdesc/did/unitdate[@type='inclusive']), '&#x000D;', '')" />
							</marc:subfield>
						</xsl:if>
						<xsl:if test="string(archdesc/did/unitdate[@type='bulk'])">
							<marc:subfield code="g">
								<xsl:value-of select="concat('bulk ', translate(normalize-space(archdesc/did/unitdate[@type='bulk']), '&#x000D;', ''), ')')" />
							</marc:subfield>
						</xsl:if>
					</marc:datafield>
				</xsl:if>
				<!--================================= M A R C   2 5 4 / 2 5 5 / 2 5 6 =================================-->
				<!--
				/**************************************************/
				/ Since <materialspec> is mapped to several MARC   /
				/ fields in EAD, the encodinganalog attribute must /
				/ be mapped for the stylesheet to be able to       /
				/ determine which MARC field to map it to.  If the /
				/ encodinganalog attribute is not specified, then  /
				/ this MARC field is ignored                       /
				/**************************************************/
				-->
				<xsl:if test="archdesc/did/materialspec/@encodinganalog">
					<marc:datafield tag="{substring(archdesc/did/materialspec/@encodinganalog, 1, 3)}" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/did/materialspec), '&#x000D;', '')"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   3 0 0 =====================================-->
				<!--
				/**********************************************************************/
				/ Formatting of the MARC 300 fields is a little tricky since there are /
				/ many ways to use the EAD elements.  This stylesheet assumes that     /
				/ there will be no more than 2 <extent> elements and that the second   /
				/ extent element should be formatted with parentheses around it.  If   /
				/ this does not reflect your current usage of the extent element, you  /
				/ may need to alter this section to fit your needs.                    /
				/**********************************************************************/
				-->
				<xsl:if test="string(archdesc/did/physdesc)">
					<xsl:for-each select="archdesc/did/physdesc[not(@audience='internal')]">
						<marc:datafield tag="300" ind1="" ind2="">
							<xsl:choose>
								<xsl:when test="string(extent[2]) or not(contains(extent[1], '('))">
									<marc:subfield code="a">
										<xsl:value-of select="substring-before(extent[1], ' ')"/>
									</marc:subfield>
									<marc:subfield code="f">
										<xsl:value-of select="substring-after(extent[1], ' ')"/>
										<xsl:if test="string(physfacet) and not(string(extent[2]))">
											<xsl:text> :</xsl:text>
										</xsl:if>
									</marc:subfield>
								</xsl:when>
								<xsl:otherwise>
									<marc:subfield code="a">
										<xsl:value-of select="extent"/>
									</marc:subfield>
								</xsl:otherwise>
							</xsl:choose>
							<!--
							<xsl:value-of select="translate(normalize-space(archdesc/did/physdesc/extent[1]), '&#x000D;', '')" />
							-->
							<xsl:if test="string(extent[2])">
								<xsl:variable name="extent2" select="translate(normalize-space(extent[2]), '&#x000D;', '')"/>
								<xsl:choose>
									<xsl:when test="starts-with($extent2, '(')">
										<marc:subfield code="a">
											<xsl:value-of select="substring-before($extent2, ' ')" />
										</marc:subfield>
										<marc:subfield code="f">
											<xsl:value-of select="substring-after($extent2, ' ')" />
											<xsl:if test="string(physfacet)">
												<xsl:text> :</xsl:text>
											</xsl:if>
										</marc:subfield>
									</xsl:when>
									<xsl:otherwise>
										<marc:subfield code="a">
											<xsl:text>(</xsl:text>
											<xsl:value-of select="substring-before($extent2, ' ')" />
										</marc:subfield>
										<marc:subfield code="f">
											<xsl:value-of select="substring-after($extent2, ' ')" />
											<xsl:text>)</xsl:text>
											<xsl:if test="string(physfacet)">
												<xsl:text> :</xsl:text>
											</xsl:if>
										</marc:subfield>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:if>
							<xsl:if test="not(string(extent[2])) and not(string(physfacet)) and not(string(dimensions))">
								<xsl:call-template name="fullstop">
									<xsl:with-param name="input" select="normalize-space(extent[1])"/>
								</xsl:call-template>
							</xsl:if>
							<xsl:if test="string(physfacet)">
								<marc:subfield code="b">
									<xsl:value-of select="translate(normalize-space(physfacet), '&#x000D;', '')" />
									<xsl:if test="string(dimensions)">
										<xsl:text> ;</xsl:text>
									</xsl:if>
								</marc:subfield>
							</xsl:if>
							<xsl:if test="string(dimensions)">
								<marc:subfield code="c">
									<xsl:value-of select="translate(normalize-space(dimensions), '&#x000D;', '')" />
								</marc:subfield>
							</xsl:if>
						</marc:datafield>
					</xsl:for-each>
				</xsl:if>
				<!--===================================== M A R C   3 4 0 / 5 3 8 =====================================-->
				<xsl:if test="archdesc/phystech/@encodinganalog and string(archdesc/phystech[not(@audience='internal')])">
					<!--
				/**********************************************************/
				/ Since <phystech> can map to either MARC 340 or 538, the  /
				/ correct mapping must be specified in the encodinganalog  /
				/ attribute.                                               /
				/**********************************************************/
				-->
					<marc:datafield tag="{normalize-space(archdesc/phystech/@encodinganalog)}"	ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/phystech), '&#x000D;', '')"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 4 5 =====================================-->
				<xsl:if test="string(archdesc/bioghist[not(@audience='internal')])">
					<marc:datafield tag="545">
						<xsl:choose>
							<xsl:when test="starts-with(archdesc/bioghist/@encodinganalog, 5450) or contains(archdesc/bioghist/head, 'Biographical') or string(archdesc/did/origination/persname)">
								<xsl:attribute name="ind1">0</xsl:attribute>
							</xsl:when>
							<xsl:otherwise>
								<xsl:attribute name="ind1">1</xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:attribute name="ind2"></xsl:attribute>
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/bioghist), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 2 0  ===================================-->
				<xsl:if test="string(archdesc/scopecontent[not(@audience='internal')])">
					<marc:datafield tag="520" ind1="2" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/scopecontent), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   3 5 1 =====================================-->
				<xsl:if test="string(archdesc/arrangement[not(@audience='internal')])">
					<marc:datafield tag="351" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/arrangement), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 4 1 =====================================-->
				<xsl:if test="string(archdesc/acqinfo[not(@audience='internal')])">
					<marc:datafield tag="541" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:choose>
								<xsl:when test="//eadid/@mainagencycode='wau-ar' and ((archdesc/acqinfo/p/persname or archdesc/acqinfo/p/corpname) and archdesc/acqinfo/p/date)">
									<xsl:variable name="acqinfo">
										<xsl:text>Source: </xsl:text>
										<xsl:value-of select="translate(normalize-space(archdesc/acqinfo/p/corpname), '&#x000D;', '')"/>
										<xsl:text>, </xsl:text>
										<xsl:value-of select="translate(normalize-space(archdesc/acqinfo/p/date), '&#x000D;', '')"/>
									</xsl:variable>
									<xsl:value-of select="translate($acqinfo, '&#x000D;', '')" />
									<xsl:call-template name="fullstop">
										<xsl:with-param name="input" select="$acqinfo"/>
									</xsl:call-template>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select="translate(normalize-space(archdesc/acqinfo), '&#x000D;', '')"/>
								</xsl:otherwise>
							</xsl:choose>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 0 6 =====================================-->
				<xsl:if test="string(archdesc/accessrestrict[not(@audience='internal')])">
					<marc:datafield tag="506" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/accessrestrict), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 4 0 =====================================-->
				<xsl:if test="string(archdesc/userestrict[not(@audience='internal')])">
					<marc:datafield tag="540" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/userestrict), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 2 4 =====================================-->
				<xsl:if test="string(archdesc/prefercite[not(@audience='internal')])">
					<marc:datafield tag="524" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/prefercite), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 4 6 =====================================-->
				<xsl:if test="string(archdesc/did/langmaterial/language[2])">
					<marc:datafield tag="546" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/did/langmaterial), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 5 5 =====================================-->
				<xsl:if test="string(archdesc/otherfindaid[not(@audience='internal')])">
					<marc:datafield tag="555" ind1="0" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/otherfindaid), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 6 1 =====================================-->
				<xsl:if test="string(archdesc/custodhist[not(@audience='internal')])">
					<marc:datafield tag="561" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/custodhist), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 8 3 =====================================-->
				<xsl:if test="string(archdesc/appraisal[not(@audience='internal')])">
					<marc:datafield tag="583" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:text>Appraise; </xsl:text>
						</marc:subfield>
						<marc:subfield code="z">
							<xsl:value-of select="translate(normalize-space(archdesc/appraisal), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 8 4 =====================================-->
				<xsl:if test="string(archdesc/accruals[not(@audience='internal')])">
					<marc:datafield tag="584" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/accruals), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 3 5 =====================================-->
				<xsl:if test="string(archdesc/originalsloc[not(@audience='internal')])">
					<marc:datafield tag="535" ind1="1" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/originalsloc), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 3 0 =====================================-->
				<xsl:if test="string(archdesc/altformavail[not(@audience='internal')])">
					<marc:datafield tag="530" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/altformavail), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 4 4 =====================================-->
				<xsl:if test="string(archdesc/separatedmaterial[not(@audience='internal')])">
					<marc:datafield tag="544" ind1="0" ind2="">
						<marc:subfield code="n">
							<xsl:value-of select="translate(normalize-space(archdesc/separatedmaterial), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="string(archdesc/relatedmaterial[not(@audience='internal')])">
					<marc:datafield tag="544" ind1="1" ind2="">
						<marc:subfield code="n">
							<xsl:value-of select="translate(normalize-space(archdesc/relatedmaterial), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!-- UW only -->
				<xsl:if test="string(archdesc/processinfo/processinfo[@type='reloc']) and //eadid/@mainagencycode='wau-ar'">
					<marc:datafield tag="544" ind1="" ind2="">
						<marc:subfield code="n">
							<xsl:value-of select="normalize-space(archdesc/processinfo/processinfo[@type='reloc'])" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   5 0 0 =====================================-->
				<xsl:if test="string(archdesc/odd[not(@audience='internal')][not(@type='hist')][@encodinganalog='500'])">
					<marc:datafield tag="500" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/odd), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="string(archdesc/note[not(@audience='internal')][@encodinganalog='500'])">
					<marc:datafield tag="500" ind1="" ind2="">
						<marc:subfield code="a">
							<xsl:value-of select="translate(normalize-space(archdesc/note), '&#x000D;', '')" />
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!--===================================== M A R C   6 x x (not 6 5 0) =====================================-->
				<xsl:for-each select="archdesc//controlaccess/persname[not(@altrender='nodisplay')][not(@audience='internal')] | archdesc//controlaccess/corpname[not(@altrender='nodisplay')][not(@audience='internal')] | archdesc//controlaccess/famname[not(@altrender='nodisplay')][not(@audience='internal')]">
					<xsl:sort select="@encodinganalog" />
					<xsl:if test="string(self::*)">
						<xsl:variable name="term" select="translate(normalize-space(.), '&#x000D;', '')"/>
						<xsl:choose>
							<!--
						/*****************************************************************************************/
						/ Per the EAD crosswalk, <persname>, <corpname>, etc. map to 6xx when the encodinganalog  /
						/ attribute is 6xx or the role attribute is "subject"                                     /
						/*****************************************************************************************/
						-->
							<xsl:when test="(@encodinganalog='600' or @role='subject') and (self::persname or self::famname)">
								<marc:datafield tag="600">
									<xsl:choose>
										<xsl:when test="self::persname">
											<xsl:attribute name="ind1">1</xsl:attribute>
										</xsl:when>
										<xsl:when test="self::famname">
											<xsl:attribute name="ind1">3</xsl:attribute>
										</xsl:when>
									</xsl:choose>
									<xsl:attribute name="ind2">0</xsl:attribute>
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:when>
							<!--===================================== M A R C   6 1 0 / 6 1 1 ================================-->
							<!--
							/****************************************************************************/
							/ Since <corpname> can map to either 610 or 611, if the MARC mapping is not  /
							/ specified in the encodinganalog attribute, then MARC 610 is assumed        /
							/****************************************************************************/
							-->
							<xsl:when test="(@encodinganalog='610' or @role='subject') and self::corpname">
								<marc:datafield tag="610" ind1="2" ind2="0">
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:when>
							<xsl:when test="@encodinganalog='611'">
								<marc:datafield tag="611" ind1="2" ind2="0">
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:when>
							<!--===================================== M A R C   7 0 0 =====================================-->
							<xsl:when test="@encodinganalog='700'">
								<marc:datafield tag="700">
									<xsl:choose>
										<xsl:when test="self::persname">
											<xsl:attribute name="ind1">1</xsl:attribute>
										</xsl:when>
										<xsl:when test="self::famname">
											<xsl:attribute name="ind1">3</xsl:attribute>
										</xsl:when>
									</xsl:choose>
									<xsl:attribute name="ind2">0</xsl:attribute>
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:when>
							<!--===================================== M A R C   7 1 0 / 7 1 1 ================================-->
							<xsl:when test="@encodinganalog='710'">
								<marc:datafield tag="710" ind1="2" ind2="">
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:when>
							<xsl:when test="@encodinganalog='711'">
								<marc:datafield tag="711" ind1="2" ind2="">
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:when>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
				<!--===================================== M A R C   6 5 5 =====================================-->
				<xsl:for-each select="archdesc//controlaccess/genreform[not(@altrender='nodisplay')][not(@audience='internal')]">
					<xsl:variable name="term" select="translate(normalize-space(.), '&#x000D;', '')"/>
					<xsl:if test="string(self::*) and @encodinganalog='655'">
						<marc:datafield tag="655" ind1="" ind2="7">
							<marc:subfield code="a">
								<xsl:value-of select="$term" />
								<xsl:call-template name="fullstop">
									<xsl:with-param name="input" select="$term"/>
									<xsl:with-param name="fullstop" select="'yes'"/>
								</xsl:call-template>
							</marc:subfield>
							<xsl:if test="@source and not(@source='lcsh')">
								<marc:subfield code="2">
									<xsl:value-of select="normalize-space(@source)" />
								</marc:subfield>
							</xsl:if>
						</marc:datafield>
					</xsl:if>
				</xsl:for-each>
				<!--===================================== M A R C   6 5 0 =====================================-->
				<xsl:for-each select="archdesc//controlaccess/subject[not(@altrender='nodisplay')][not(@audience='internal')][not(@source='uwsc')][not(@source='nwda')]">
					<xsl:if test="string(self::*)">
						<xsl:variable name="term" select="translate(normalize-space(.), '&#x000D;', '')"/>
						<marc:datafield tag="650" ind1="" ind2="0">
							<xsl:call-template name="processSubfields">
								<xsl:with-param name="term" select="$term"/>
								<xsl:with-param name="fullstop" select="'yes'"/>
							</xsl:call-template>
						</marc:datafield>
						<xsl:if test="@source and not(@source='lcsh')">
							<marc:subfield code="2">
								<xsl:value-of select="normalize-space(@source)" />
							</marc:subfield>
						</xsl:if>
					</xsl:if>
				</xsl:for-each>
				<!--===================================== M A R C   6 5 1 =====================================-->
				<xsl:for-each select="archdesc//controlaccess/geogname[not(@altrender='nodisplay')][not(@audience='internal')]">
					<xsl:if test="string(self::*)">
						<xsl:variable name="term" select="translate(normalize-space(.), '&#x000D;', '')"/>
						<xsl:if test="@encodinganalog='651'">
							<marc:datafield tag="651" ind1="" ind2="0">
								<xsl:call-template name="processSubfields">
									<xsl:with-param name="term" select="$term"/>
									<xsl:with-param name="fullstop" select="'yes'"/>
								</xsl:call-template>
							</marc:datafield>
						</xsl:if>
					</xsl:if>
				</xsl:for-each>
				<!--================================ M A R C   6 3 0 / 7 3 0 ===========================-->
				<!--
				/*******************************************************************************************/
				/ This one is tricky to map to MARC indicators/subfields.  Some post-process editing may be /
				/ required.                                                                                 /
				/*******************************************************************************************/
				-->
				<xsl:for-each select="archdesc//controlaccess/title[not(@altrender='nodisplay')][not(@audience='internal')]">
					<xsl:if test="string(self::*)">
						<xsl:variable name="term" select="translate(normalize-space(.), '&#x000D;', '')"/>
						<xsl:choose>
							<xsl:when test="@encodinganalog='630' or @role='subject'">
								<marc:datafield tag="630" ind1="0" ind2="0">
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:when>
							<xsl:otherwise>
								<marc:datafield tag="{substring(normalize-space(@encodinganalog), 1, 3)}" ind1="0" ind2="2">
									<xsl:call-template name="processSubfields">
										<xsl:with-param name="term" select="$term"/>
										<xsl:with-param name="fullstop" select="'yes'"/>
									</xsl:call-template>
								</marc:datafield>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
				<!--===================================== M A R C   8 5 6 ================================-->
				<xsl:if test="eadheader/eadid and $includeMARC856='yes'">
					<marc:datafield tag="856" ind1="4" ind2="2">
						<marc:subfield code="u">
							<xsl:value-of select="normalize-space(eadheader/eadid/@url)" />
						</marc:subfield>
						<marc:subfield code="z">
							<xsl:value-of select="$MARC856text"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<!-- UW only -->
				<xsl:if test="string(archdesc/processinfo/processinfo/archref) and //eadid/@mainagencycode='wau-ar'">
					<marc:datafield tag="856" ind1="4" ind2="2">
						<marc:subfield code="u">
							<xsl:value-of select="archdesc/processinfo/processinfo[@type='reloc']/archref/@href" />
						</marc:subfield>
						<marc:subfield code="z">
							<xsl:value-of select="normalize-space(archdesc/processinfo/processinfo[@type='reloc'])"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="//eadid/@mainagencycode='wau-ar'">
					<xsl:choose>
						<xsl:when test="ead/archdesc/did/unitid/@type='collection'">
							<marc:datafield tag="945" ind1="5" ind2="">
								<marc:subfield code="a">
									<xsl:text>Photograph collection </xsl:text>
									<xsl:value-of select="ead/archdesc/did/unitid"/>
								</marc:subfield>
								<marc:subfield code="l">scpnw</marc:subfield>
								<marc:subfield code="t">15</marc:subfield>
								<marc:subfield code="s">-</marc:subfield>
								<marc:subfield code="o">x</marc:subfield>
							</marc:datafield>
						</xsl:when>
						<xsl:otherwise>
							<marc:datafield tag="945" ind1="" ind2="">
								<marc:subfield code="l">scmua</marc:subfield>
								<marc:subfield code="t">7</marc:subfield>
								<marc:subfield code="s">-</marc:subfield>
								<marc:subfield code="o">x</marc:subfield>
							</marc:datafield>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				<!--======================== M A R C   I M P O R T E D   F I E L D S =====================-->
				<!--
				/*******************************************************************************************/
				/ This template processes MARC fields from the marcextras.xml file.  You can place any MARC /
				/ field into this file and it will be exported during conversion from EAD to MARC.  Consult /
				/ the notes in the marcextras.xml document for more details on format of data, etc.         /                      /
				/ This template assumes that the data in marcextras.xml will be in either an embedded       /
				/ subfields format ($b, $c) or in marcxml format (e.g. <marc:datafield...>.  If "$" is      /
				/ present in the string, the stylesheet assumes that embedded subfields are present and     /
				/ process them as such.  Otherwise, it just assumes that it is in MARCXML format and does   /
				/ a straight copy of the data.                                                              /
				/*******************************************************************************************/
				-->
				<xsl:if test="string(document('marcextras.xml')//*) and $includeMARCExtrasFile='yes'">
					<xsl:for-each select="document('marcextras.xml')/document/*">
						<xsl:variable name="term" select="translate(normalize-space(.), '&#x000D;', '')"/>
						<xsl:choose>
							<xsl:when test="contains(., '$') or not(child::marc:datafield)">
								<marc:datafield tag="{substring(name(), 5)}" ind1="{@ind1}" ind2="{@ind2}">
									<xsl:choose>
										<xsl:when test="starts-with($term, '$')">
											<xsl:call-template name="encodedsubfields">
												<xsl:with-param name="input" select="substring(substring-after($term, '$'), 2)" />
												<xsl:with-param name="search-string" select="'$'" />
												<xsl:with-param name="subcode" select="substring(substring-after($term, '$'), 1, 1)"/>
												<xsl:with-param name="fullstop" select="@fullstop"/>
											</xsl:call-template>
										</xsl:when>
										<xsl:otherwise>
											<xsl:call-template name="processSubfields">
												<xsl:with-param name="term" select="$term" />
												<xsl:with-param name="fullstop" select="@fullstop"/>
											</xsl:call-template>
										</xsl:otherwise>
									</xsl:choose>
								</marc:datafield>
							</xsl:when>
							<xsl:otherwise>
								<xsl:copy-of select="child::*"/>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:for-each>
				</xsl:if>
			</marc:record>
		</marc:collection>
	</xsl:template>
	<xsl:template name="encodedsubfields">
		<xsl:param name="input" />
		<xsl:param name="search-string" />
		<xsl:param name="subcode"/>
		<xsl:param name="fullstop"/>
		<xsl:choose>
			<xsl:when test="$search-string and contains($input,$search-string)">
				<marc:subfield code="{$subcode}">
					<xsl:value-of select="substring-before($input,$search-string)" />
				</marc:subfield>
				<xsl:call-template name="encodedsubfields">
					<xsl:with-param name="input" select="substring(substring-after($input,$search-string), 2)" />
					<xsl:with-param name="search-string" select="$search-string" />
					<xsl:with-param name="subcode" select="substring(substring-after($input, '$'), 1, 1)"/>
					<xsl:with-param name="fullstop" select="$fullstop"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<marc:subfield code="{$subcode}">
					<xsl:value-of select="$input" />
					<xsl:if test="$fullstop='yes'">
						<xsl:call-template name="fullstop">
							<xsl:with-param name="input" select="$input"/>
						</xsl:call-template>
					</xsl:if>
				</marc:subfield>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template name="processSubfields">
		<xsl:param name="term"/>
		<xsl:param name="fullstop"/>
		<xsl:choose>
			<xsl:when test="contains($term, '$')">
				<xsl:if test="not(starts-with($term, '$'))">
					<marc:subfield code="a">
						<xsl:value-of select="substring-before($term, '$')" />
					</marc:subfield>
				</xsl:if>
				<xsl:call-template name="encodedsubfields">
					<xsl:with-param name="input" select="substring(substring-after($term, '$'), 2)" />
					<xsl:with-param name="search-string" select="'$'" />
					<xsl:with-param name="subcode" select="substring(substring-after($term, '$'), 1, 1)"/>
					<xsl:with-param name="fullstop" select="$fullstop"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<marc:subfield code="a">
					<xsl:value-of select="$term" />
					<xsl:if test="$fullstop='yes'">
						<xsl:call-template name="fullstop">
							<xsl:with-param name="input" select="$term"/>
						</xsl:call-template>
					</xsl:if>
				</marc:subfield>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template name="fullstop">
		<xsl:param name="input"/>
		<xsl:variable name="fullstop" select="normalize-space($input)" />
		<xsl:variable name="chars">.-?!])</xsl:variable>
		<xsl:if test="contains($chars, substring($fullstop, string-length($fullstop), 1)) = false">
			<xsl:text>.</xsl:text>
		</xsl:if>
	</xsl:template>
</xsl:stylesheet>
