<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0" exclude-result-prefixes="marc">
	<xsl:import href="MARC21slimUtils.xsl"/>
	<xsl:output method="xml" encoding="UTF-8" indent="yes"/>
	<xsl:template match="/ead">
		<marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
			<xsl:variable name="bulkdate" select="//unitdate[@encodinganalog='245$g']"/>
			<marc:record>
				<marc:leader>
					<xsl:text>01125ntc a2200289Ia 4500</xsl:text>
				</marc:leader>
				<xsl:choose>
					<xsl:when test="$bulkdate!=''">
						<marc:controlfield tag="008">
							<xsl:text>040320k</xsl:text>
							<xsl:value-of select="substring-before($bulkdate, '-')"/>
							<xsl:value-of select="substring-after($bulkdate,'-')"/>
							<xsl:text>xx\\\\\\\\\\\\000\0\eng\d</xsl:text>
						</marc:controlfield>
					</xsl:when>
					<xsl:otherwise>
						<marc:controlfield tag="008">
							<xsl:text>040320u9999\\\\xx\\\\\\\\\\\\000\0\eng\d</xsl:text>
						</marc:controlfield>
					</xsl:otherwise>
				</xsl:choose>
				<marc:datafield tag="040" ind1=" " ind2=" ">
					<marc:subfield code="a">ORE</marc:subfield>
					<marc:subfield code="e">dacs</marc:subfield>
					<marc:subfield code="c">ORE</marc:subfield>
				</marc:datafield>
<!--Removed the following
						<xsl:value-of select="normalize-space(//unitid/.)" />
				-->
				<xsl:if test="//unitid">
					<marc:datafield tag="099" ind1=" " ind2=" ">
						<marc:subfield code="a">ARCH-MSS</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="//origination">
					<xsl:if test="//origination/persname[@role='creator']">
						<xsl:call-template name="persname_template">
							<xsl:with-param name="string" select="normalize-space(//origination/persname/.)"/>
							<xsl:with-param name="field" select="'100'"/>
							<xsl:with-param name="ind1" select="'1'"/>
							<xsl:with-param name="ind2" select="' '"/>
						</xsl:call-template>
<!--<marc:datafield tag="100" ind1="1" ind2=" ">
							<marc:subfield code="a">
								<xsl:variable name="p100" select="normalize-space(//persname/.)" 
								<xsl:value-of select="normalize-space(//persname/.)" />
							</marc:subfield>
						</marc:datafield>-->
					</xsl:if>
					<xsl:if test="//origination/famname[@role='creator']">
						<xsl:call-template name="persname_template">
							<xsl:with-param name="string" select="normalize-space(//origination/famname/.)"/>
							<xsl:with-param name="field" select="'100'"/>
							<xsl:with-param name="ind1" select="'1'"/>
							<xsl:with-param name="ind2" select="' '"/>
						</xsl:call-template>
<!--<marc:datafield tag="100" ind1="1" ind2=" ">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(//famname/.)" />
							</marc:subfield>
						</marc:datafield>-->
					</xsl:if>
					<xsl:if test="//origination/corpname[@role='creator']">
						<xsl:call-template name="corpname_template">
							<xsl:with-param name="string" select="normalize-space(//origination/corpname/.)"/>
							<xsl:with-param name="field" select="'110'"/>
							<xsl:with-param name="ind1" select="'2'"/>
							<xsl:with-param name="ind2" select="'0'"/>
						</xsl:call-template>
<!--<marc:datafield tag="110" ind1="2" ind2=" ">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(//corpname/.)" />
							</marc:subfield>
						</marc:datafield>-->
					</xsl:if>
				</xsl:if>
				<xsl:if test="//unittitle">
					<marc:datafield tag="245" ind1="1" ind2="0">
						<xsl:choose>
							<xsl:when test="//unidate">
								<marc:subfield code="a"><xsl:value-of select="normalize-space(//unittitle[@encodinganalog='245$a']/.)"/>,
								</marc:subfield>
							</xsl:when>
							<xsl:otherwise>
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(//unittitle[@encodinganalog='245$a']/.)"/>
								</marc:subfield>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:if test="//unitdate">
							<xsl:choose>
								<xsl:when test="//unitdate/@encodinganalog='245$g'">
									<xsl:if test="//unitdate/@encodinganalog='245$f'">
										<marc:subfield code="f">
											<xsl:value-of select="normalize-space(//unitdate[@encodinganalog='245$f']/.)"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="//unitdate/@encodinganalog='245$g'">
										<marc:subfield code="g">bulk <xsl:value-of select="normalize-space(//unitdate[@encodinganalog='245$g']/.)"/>).</marc:subfield>
									</xsl:if>
								</xsl:when>
								<xsl:otherwise>
									<xsl:if test="//unitdate/@encodinganalog='245$f'">
										<marc:subfield code="f"><xsl:value-of select="normalize-space(//unitdate[@encodinganalog='245$f']/.)"/>.</marc:subfield>
									</xsl:if>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="archdesc/did/physdesc">
					<xsl:for-each select="archdesc/did/physdesc/extent">
						<marc:datafield tag="300" ind1=" " ind2=" ">
							<marc:subfield><xsl:attribute name="code"><xsl:value-of select="substring(@encodinganalog,5,1)"/></xsl:attribute><xsl:value-of select="normalize-space(.)"/>.
							</marc:subfield>
						</marc:datafield>
					</xsl:for-each>
				</xsl:if>
				<xsl:if test="archdesc/arrangement">
					<marc:datafield tag="351" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/arrangement/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:for-each select="//odd">
					<marc:datafield tag="500" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:for-each>
				<xsl:if test="//accessrestrict">
					<marc:datafield tag="506" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//accessrestrict/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
<!--We could enter the scope and contents note, but I've been
                    told that the abstract note is just as good and possibly
                    preferred-->
				<xsl:choose>
					<xsl:when test="//scopecontent">
						<marc:datafield tag="520" ind1="2" ind2=" ">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(//scopecontent/p/.)"/>
							</marc:subfield>
						</marc:datafield>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="//abstract">
							<xsl:if test="//abstract/@encodinganalog='5203_'">
								<marc:datafield tag="520" ind1="3" ind2=" ">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(//abstract/p/.)"/>
									</marc:subfield>
								</marc:datafield>
							</xsl:if>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:if test="archdesc/prefercite">
					<marc:datafield tag="524" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/prefercite/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="//fileplan/altformavail">
					<marc:datafield tag="530" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//fileplan/altformavail/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="//legalstatus/userestrict">
					<marc:datafield tag="540" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//legalstatus/userestrict/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="archdesc/relatedmaterial">
					<marc:datafield tag="544" ind1="1" ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/relatedmaterial/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:if test="//bioghist">
					<xsl:if test="//bioghist/@encodinganalog='5450_'">
						<marc:datafield tag="545" ind1="0" ind2=" ">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(//bioghist/p/.)"/>
							</marc:subfield>
						</marc:datafield>
					</xsl:if>
					<xsl:if test="//bioghist/@encodinganalog='5451_'">
						<marc:datafield tag="545" ind1="1" ind2=" ">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(//bioghist/p/.)"/>
							</marc:subfield>
						</marc:datafield>
					</xsl:if>
					<xsl:if test="//bioghist/@encodinganalog='545' or //bioghist/@encodinganalog='545$a'">
						<marc:datafield tag="545" ind1=" " ind2=" ">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(//bioghist/p/.)"/>
							</marc:subfield>
						</marc:datafield>
					</xsl:if>
				</xsl:if>
				<xsl:if test="//langmaterial/language">
					<marc:datafield tag="546" ind1=" " ind2=" ">
						<marc:subfield code="a">
							Materials in <xsl:value-of select="normalize-space(//language/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<marc:datafield tag="555" ind1=" " ind2=" ">
					<marc:subfield code="a">Finding aid available in the University Archives and on its World Wide Web site.</marc:subfield>
				</marc:datafield>
				<xsl:if test="archdesc/custodhist">
					<marc:datafield tag="561" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(archdesc/custodhist/.)"/>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<xsl:for-each select="//controlaccess/persname | //controlaccess/corpname">
					<xsl:sort select="@encodinganalog"/>
					<xsl:choose>
						<xsl:when test="@encodinganalog='600'">
							<xsl:call-template name="persname_template">
								<xsl:with-param name="string" select="normalize-space(.)"/>
								<xsl:with-param name="field" select="'600'"/>
								<xsl:with-param name="ind1" select="'1'"/>
								<xsl:with-param name="ind2" select="'0'"/>
							</xsl:call-template>
<!--<marc:datafield tag="600" ind1="1" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)" />
								</marc:subfield>
							</marc:datafield>-->
						</xsl:when>
						<xsl:when test="@encodinganalog='610'">
							<xsl:call-template name="corpname_template">
								<xsl:with-param name="string" select="normalize-space(.)"/>
								<xsl:with-param name="field" select="'610'"/>
								<xsl:with-param name="ind1" select="'2'"/>
								<xsl:with-param name="ind2" select="'0'"/>
							</xsl:call-template>
<!--<marc:datafield tag="610" ind1="2" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)" />
								</marc:subfield>
							</marc:datafield>-->
						</xsl:when>
						<xsl:when test="@encodinganalog='611'">
							<marc:datafield tag="611" ind1="2" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)"/>
								</marc:subfield>
							</marc:datafield>
						</xsl:when>
						<xsl:when test="@encodinganalog='700'">
							<xsl:call-template name="persname_template">
								<xsl:with-param name="string" select="normalize-space(.)"/>
								<xsl:with-param name="field" select="'700'"/>
								<xsl:with-param name="ind1" select="'1'"/>
								<xsl:with-param name="ind2" select="' '"/>
							</xsl:call-template>
<!--<marc:datafield tag="700" ind1="1" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)" />
								</marc:subfield>
							</marc:datafield>-->
						</xsl:when>
						<xsl:when test="@encodinganalog='710'">
							<xsl:call-template name="corpname_template">
								<xsl:with-param name="string" select="normalize-space(.)"/>
								<xsl:with-param name="field" select="'710'"/>
								<xsl:with-param name="ind1" select="'2'"/>
								<xsl:with-param name="ind2" select="' '"/>
							</xsl:call-template>
<!--<marc:datafield tag="710" ind1="2" ind2="0">
								<marc:subfield code="a">
									<xsl:value-of select="normalize-space(.)" />
								</marc:subfield>
							</marc:datafield>-->
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
				</xsl:for-each>
				<xsl:for-each select="//controlaccess/subject[@source='lcsh'] | //controlaccess/geogname[@source='lcsh']">
					<xsl:choose>
						<xsl:when test="@source='lcsh'">
							<xsl:if test="@encodinganalog='650'">
								<xsl:call-template name="subject_template">
									<xsl:with-param name="string" select="normalize-space(.)"/>
									<xsl:with-param name="field" select="'650'"/>
									<xsl:with-param name="ind1" select="' '"/>
									<xsl:with-param name="ind2" select="'0'"/>
								</xsl:call-template>
<!--<marc:datafield tag="650" ind1=" " ind2="0">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
									</marc:subfield>
								</marc:datafield>-->
							</xsl:if>
							<xsl:if test="@encodinganalog='651'">
								<xsl:call-template name="subject_template">
									<xsl:with-param name="string" select="normalize-space(.)"/>
									<xsl:with-param name="field" select="'651'"/>
									<xsl:with-param name="ind1" select="' '"/>
									<xsl:with-param name="ind2" select="'0'"/>
								</xsl:call-template>
<!--<marc:datafield tag="651" ind1=" " ind2="0">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
									</marc:subfield>
								</marc:datafield>-->
							</xsl:if>
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
				</xsl:for-each>
				<xsl:for-each select="//controlaccess/genreform">
					<xsl:if test="//@encodinganalog='655'">
						<marc:datafield tag="655" ind1=" " ind2="7">
							<marc:subfield code="a">
								<xsl:value-of select="normalize-space(.)"/>
							</marc:subfield>
							<marc:subfield code="2">
								<xsl:value-of select="normalize-space(@source)"/>
							</marc:subfield>
						</marc:datafield>
					</xsl:if>
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
<!--<xsl:if test="//repository/corpname/@encodinganalog='852$a'">
					<marc:datafield tag="852" ind1=" " ind2=" ">
						<marc:subfield code="a">
							<xsl:value-of select="normalize-space(//repository/corpname/node())" />
						</marc:subfield>
						<xsl:if test="//repository/corpname/subarea">
							<marc:subfield code="b">
								<xsl:value-of select="normalize-space(//repository/corpname/subarea/.)" />
							</marc:subfield>
						</xsl:if>
					</marc:datafield>
				</xsl:if>
				-->
				<xsl:if test="eadheader/eadid">
					<marc:datafield tag="856" ind1="4" ind2="2">
						<marc:subfield code="u">
							<xsl:variable name="url" select="normalize-space(eadheader/eadid/@url)"/>
							<xsl:if test="$url!=''">
								<xsl:value-of select="normalize-space(eadheader/eadid/@url)"/>
							</xsl:if>
							<xsl:if test="$url=''">
								<xsl:if test="eadheader/eadid/@publicid !=''">
									<xsl:value-of select="normalize-space(eadheader/eadid/@publicid)"/>
								</xsl:if>
							</xsl:if>
						</marc:subfield>
						<marc:subfield code="z">View the EAD finding aid at the Northwest Digital Archives.</marc:subfield>
					</marc:datafield>
				</xsl:if>
				<marc:datafield tag="856" ind1="4" ind2="2">
					<marc:subfield code="u">http://osulibrary.oregonstate.edu/archives/archive/mss/documents/<xsl:value-of select="substring-before(normalize-space(eadheader/eadid/.), '.xml')"/>.pdf</marc:subfield>
					<marc:subfield code="z">View the print finding aid online.</marc:subfield>
				</marc:datafield>
				<marc:datafield tag="949" ind1=" " ind2=" ">
					<marc:subfield code="a">*recs=b;bn=vag/loc=vagn/ty=99/st=o;loc=ngen/st=o/ty=99;</marc:subfield>
				</marc:datafield>
			</marc:record>
		</marc:collection>
	</xsl:template>
	<xsl:template name="persname_template">
		<xsl:param name="string"/>
		<xsl:param name="field"/>
		<xsl:param name="ind1"/>
		<xsl:param name="ind2"/>
		<marc:datafield>
			<xsl:attribute name="tag">
				<xsl:value-of select="$field"/>
			</xsl:attribute>
			<xsl:attribute name="ind1">
				<xsl:value-of select="$ind1"/>
			</xsl:attribute>
			<xsl:attribute name="ind2">
				<xsl:value-of select="$ind2"/>
			</xsl:attribute>
<!-- Sample input: Brightman, Samuel C. (Samuel Charles), 1911-1992 -->
<!-- Sample output: $aBrightman, Samuel C. $q(Samuel Charles), $d1911-. -->
<!-- will handle names with dashes e.g. Bourke-White, Margaret -->
<!-- CAPTURE PRIMARY NAME BY LOOKING FOR A PAREN OR A DASH OR NEITHER -->
			<xsl:choose>
<!-- IF A PAREN, STOP AT AN OPENING PAREN -->
				<xsl:when test="contains($string, '(')!=0">
					<marc:subfield code="a">
						<xsl:value-of select="substring-before($string, '(')"/>
					</marc:subfield>
				</xsl:when>
<!-- IF A DASH, CHECK IF IT'S A DATE OR PART OF THE NAME -->
				<xsl:when test="contains($string, '-')!=0">
					<xsl:variable name="name_1" select="substring-before($string, '-')"/>
					<xsl:choose>
<!-- IF IT'S A DATE REMOVE IT -->
						<xsl:when test="translate(substring($name_1, (string-length($name_1)), 1), '0123456789', '9999999999') = '9'">
							<xsl:variable name="name" select="substring($name_1, 1, (string-length($name_1)-6))"/>
							<marc:subfield code="a">
								<xsl:value-of select="$name"/>
							</marc:subfield>
						</xsl:when>
<!-- IF IT'S NOT A DATE, CHECK WHETHER THERE IS A DATE LATER -->
						<xsl:otherwise>
							<xsl:variable name="remainder" select="substring-after($string, '-')"/>
							<xsl:choose>
<!-- IF THERE'S A DASH, ASSUME IT'S A DATE AND REMOVE IT -->
								<xsl:when test="contains($remainder, '-')!=0">
									<xsl:variable name="tmp" select="substring-before($remainder, '-')"/>
									<xsl:variable name="name_2" select="substring($tmp, 1, (string-length($tmp)-6))"/>
									<marc:subfield code="a"><xsl:value-of select="$name_1"/>-<xsl:value-of select="$name_2"/></marc:subfield>
								</xsl:when>
<!-- IF THERE'S NO DASH IN THE REMAINDER, OUTPUT IT -->
								<xsl:otherwise>
									<marc:subfield code="a">
										<xsl:value-of select="$string"/>
									</marc:subfield>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
<!-- NO DASHES, NO PARENS, JUST OUTPUT THE NAME -->
				<xsl:otherwise>
					<marc:subfield code="a">
						<xsl:value-of select="$string"/>
					</marc:subfield>
				</xsl:otherwise>
			</xsl:choose>
<!-- CAPTURE SECONDARY NAME IN PARENS FOR SUBFIELD Q -->
			<xsl:if test="contains($string, '(')!=0">
				<xsl:variable name="subq_tmp" select="substring-after($string, '(')"/>
				<xsl:variable name="subq" select="substring-before($subq_tmp, ')')"/>
				<marc:subfield code="q">
               (<xsl:value-of select="$subq"/>)
            </marc:subfield>
			</xsl:if>
<!-- CAPTURE DATE FOR SUBFIELD D, ASSUME DATE IS LAST ITEM IN FIELD -->
<!-- Note: does not work if name has a dash in it -->
			<xsl:if test="contains($string, '-')!=0">
				<xsl:variable name="date_tmp" select="substring-before($string, '-')"/>
				<xsl:variable name="remainder" select="substring-after($string, '-')"/>
				<xsl:choose>
<!-- CHECK SECOND HALF FOR ANOTHER DASH; IF PRESENT, ASSUME THAT IS DATE -->
					<xsl:when test="contains($remainder, '-')!=0">
						<xsl:variable name="tmp" select="substring-before($remainder, '-')"/>
						<xsl:variable name="date_1" select="substring($remainder, (string-length($tmp)-3))"/>
<!-- CHECK WHETHER IT HAS A NUMBER BEFORE IT AND IF SO, OUTPUT IT AS DATE -->
						<xsl:if test="translate(substring($date_1, 1, 1), '0123456789', '9999999999') = '9'">
							<marc:subfield code="d"><xsl:value-of select="$date_1"/>.
                     </marc:subfield>
						</xsl:if>
					</xsl:when>
<!-- OTHERWISE THIS IS THE ONLY DASH SO TAKE IT -->
					<xsl:otherwise>
						<xsl:variable name="date_2" select="substring($string, (string-length($date_tmp)-3))"/>
<!-- CHECK WHETHER IT HAS A NUMBER BEFORE IT AND IF SO, OUTPUT IT AS DATE -->
						<xsl:if test="translate(substring($date_2, 1, 1), '0123456789', '9999999999') = '9'">
							<marc:subfield code="d"><xsl:value-of select="$date_2"/>.
                     </marc:subfield>
						</xsl:if>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
		</marc:datafield>
	</xsl:template>
	<xsl:template name="get_file">
		<xsl:param name="string"/>
		<xsl:choose>
			<xsl:when test="contains($string, '\')">
				<xsl:call-template name="get_file">
					<xsl:with-param name="string" select="substring-after($string, '\')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="substring-before($string, '.')"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template name="corpname_template">
		<xsl:param name="string"/>
		<xsl:param name="field"/>
		<xsl:param name="ind1"/>
		<xsl:param name="ind2"/>
		<marc:datafield>
			<xsl:attribute name="tag">
				<xsl:value-of select="$field"/>
			</xsl:attribute>
			<xsl:attribute name="ind1">
				<xsl:value-of select="$ind1"/>
			</xsl:attribute>
			<xsl:attribute name="ind2">
				<xsl:value-of select="$ind2"/>
			</xsl:attribute>
			<xsl:choose>
				<xsl:when test="contains($string, '. ')!=0">
					<xsl:variable name="tmp1" select="substring-before($string, '. ')"/>
					<xsl:variable name="tmp2" select="substring-after($string, '. ')"/>
					<marc:subfield code="a"><xsl:value-of select="$tmp1"/>. </marc:subfield>
					<xsl:call-template name="corpname_tokenizeb">
						<xsl:with-param name="string" select="$tmp2"/>
						<xsl:with-param name="type" select="'b'"/>
					</xsl:call-template>
				</xsl:when>
				<xsl:when test="contains($string, '--')!=0">
					<xsl:variable name="tmp1" select="substring-before($string, '--')"/>
					<xsl:variable name="tmp2" select="substring-after($string, '--')"/>
					<marc:subfield code="a">
						<xsl:value-of select="$tmp1"/>
					</marc:subfield>
					<xsl:call-template name="corpname_tokenizeb">
						<xsl:with-param name="string" select="$tmp2"/>
						<xsl:with-param name="type" select="'x'"/>
					</xsl:call-template>
				</xsl:when>
				<xsl:otherwise>
					<xsl:if test="contains($string, '. ')=0">
						<marc:subfield code="a">
							<xsl:value-of select="$string"/>
						</marc:subfield>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>
		</marc:datafield>
	</xsl:template>
	<xsl:template name="corpname_tokenizeb">
		<xsl:param name="string"/>
		<xsl:param name="type"/>
		<xsl:if test="contains($string, '. ')!=0">
			<xsl:variable name="str1" select="substring-before($string, '. ')"/>
			<xsl:variable name="str2" select="substring-after($string, '. ')"/>
			<marc:subfield code="b"><xsl:value-of select="$str1"/>. </marc:subfield>
			<xsl:call-template name="corpname_tokenizeb">
				<xsl:with-param name="string" select="$str2"/>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="contains($string, '--')!=0">
			<xsl:variable name="str1" select="substring-before($string, '--')"/>
			<xsl:variable name="str2" select="substring-after($string, '--')"/>
			<marc:subfield code="x">
				<xsl:value-of select="$str1"/>
			</marc:subfield>
			<xsl:call-template name="corpname_tokenizeb">
				<xsl:with-param name="string" select="$str2"/>
			</xsl:call-template>
		</xsl:if>
		<xsl:if test="$type='b'">
			<marc:subfield code="b">
				<xsl:value-of select="$string"/>
			</marc:subfield>
		</xsl:if>
		<xsl:if test="$type='x'">
			<marc:subfield code="x">
				<xsl:value-of select="$string"/>
			</marc:subfield>
		</xsl:if>
	</xsl:template>
<!--====================================================
	    = Subject Template uses three different methods to 
	    = authentice subject resources.  
	    = 1) Validates 651
	    = 2) Looks for () since this is generally an indication
	    =    of a geographic resource
	    = 3) geoglist: I've pulled a list of terms found in
	    =    the Gschedule and use them to authenticate.
	    = This isn't 100% and can be fooled, but until subjects
	    = can be encoded at a higher granularity, this provides
	    = a method to guestimate conversion
	    ====================================================-->
	<xsl:template name="subject_template">
		<xsl:param name="string"/>
		<xsl:param name="field"/>
		<xsl:param name="ind1"/>
		<xsl:param name="ind2"/>
		<marc:datafield>
			<xsl:attribute name="tag">
				<xsl:value-of select="$field"/>
			</xsl:attribute>
			<xsl:attribute name="ind1">
				<xsl:value-of select="$ind1"/>
			</xsl:attribute>
			<xsl:attribute name="ind2">
				<xsl:value-of select="$ind2"/>
			</xsl:attribute>
			<xsl:choose>
				<xsl:when test="contains($string, '--')!=0">
					<xsl:variable name="tmp1" select="substring-before($string, '--')"/>
					<xsl:variable name="tmp2" select="substring-after($string, '--')"/>
					<marc:subfield code="a">
						<xsl:value-of select="$tmp1"/>
					</marc:subfield>
					<xsl:call-template name="subject_tokenize">
						<xsl:with-param name="string" select="$tmp2"/>
						<xsl:with-param name="type" select="'x'"/>
					</xsl:call-template>
				</xsl:when>
				<xsl:otherwise>
					<marc:subfield code="a">
						<xsl:value-of select="$string"/>
					</marc:subfield>
				</xsl:otherwise>
			</xsl:choose>
		</marc:datafield>
	</xsl:template>
	<xsl:template name="subject_tokenize">
		<xsl:param name="string"/>
		<xsl:param name="type"/>
		<xsl:variable name="genx">
			<xsl:call-template name="genx"/>
		</xsl:variable>
		<xsl:variable name="geny">
			<xsl:call-template name="geny"/>
		</xsl:variable>
		<xsl:variable name="formlist">
			<xsl:call-template name="formlist"/>
		</xsl:variable>
		<xsl:variable name="geoglist">
			<xsl:call-template name="geoglist"/>
		</xsl:variable>
		<xsl:if test="contains($string, '--')!=0">
			<xsl:variable name="str1" select="substring-before($string, '--')"/>
			<xsl:variable name="str2" select="substring-after($string, '--')"/>
			<xsl:if test="contains($str2, '--')!=0">
				<xsl:variable name="newstr2" select="substring-after($str2, '--')"/>
				<xsl:variable name="tmpvar" select="substring-before($str2, '--')"/>
				<xsl:choose>
					<xsl:when test="/ead/archdesc/controlaccess/controlaccess/geogname[@encodinganalog='651']">
						<xsl:variable name="bool_found"><xsl:for-each select="/ead/archdesc/controlaccess/controlaccess/geogname[@encodinganalog='651']"><xsl:if test="contains(normalize-space(.), $tmpvar)!=0">
									true, 
									</xsl:if></xsl:for-each>
								false, 
						</xsl:variable>
						<xsl:if test="contains($bool_found,'true')!=0">
							<xsl:for-each select="/ead/archdesc/controlaccess/controlaccess/geogname[@encodinganalog='651']">
								<xsl:if test="contains(normalize-space(.), translate(substring-before($str2, '--'),'.',' '))!=0">
									<marc:subfield code="z">
										<xsl:value-of select="$str1"/>
									</marc:subfield>
<!--If it attaches and there is a second set of strings, its probably a $z as well.-->
									<marc:subfield code="z">
										<xsl:value-of select="substring-before($str2, '--')"/>
									</marc:subfield>
									<xsl:call-template name="subject_tokenize">
										<xsl:with-param name="string" select="substring-after($str2, '--')"/>
										<xsl:with-param name="type" select="'x'"/>
									</xsl:call-template>
								</xsl:if>
							</xsl:for-each>
						</xsl:if>
						<xsl:if test="contains($bool_found,'true')=0">
							<xsl:choose>
								<xsl:when test="contains($geoglist, translate($str1, '.', ''))!=0">
									<marc:subfield code="z">
										<xsl:value-of select="$str1"/>
									</marc:subfield>
									<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
										<marc:subfield code="v">
											<xsl:value-of select="substring-before($str2, '--')"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
										<marc:subfield code="y">
											<xsl:value-of select="substring-before($str2, '--')"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($genx, translate(substring-before($str2, '--'), '.', ''))!=0">
										<marc:subfield code="x">
											<xsl:value-of select="substring-before($str2, '--')"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($genx, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0">
										<marc:subfield code="z">
											<xsl:value-of select="substring-before($str2, '--')"/>
										</marc:subfield>
									</xsl:if>
								</xsl:when>
								<xsl:otherwise>
									<xsl:choose>
										<xsl:when test="contains($str1, '(')!=0">
											<xsl:choose>
												<xsl:when test="contains($formlist, translate($str1, '.', ''))!=0">
													<marc:subfield code="v">
														<xsl:value-of select="$str1"/>
													</marc:subfield>
													<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
														<marc:subfield code="v">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
														<marc:subfield code="y">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))!=0">
														<marc:subfield code="z">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0 and contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0">
														<marc:subfield code="x">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
												</xsl:when>
												<xsl:otherwise>
													<xsl:choose>
														<xsl:when test="contains($genx, translate($str1, '.', ''))!=0">
															<marc:subfield code="x">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
															<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
																<marc:subfield code="v">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
																<marc:subfield code="y">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))!=0">
																<marc:subfield code="z">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0 and contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0">
																<marc:subfield code="x">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
														</xsl:when>
														<xsl:otherwise>
															<marc:subfield code="z">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
															<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
																<marc:subfield code="v">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
																<marc:subfield code="y">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($genx, translate(substring-before($str2, '--'), '.', ''))!=0">
																<marc:subfield code="x">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($genx, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0">
																<marc:subfield code="z">
																	<xsl:value-of select="substring-before($str2, '--')"/>
																</marc:subfield>
															</xsl:if>
														</xsl:otherwise>
													</xsl:choose>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:when>
										<xsl:otherwise>
											<xsl:choose>
												<xsl:when test="contains($str1, ',')!=0">
													<xsl:variable name="tmpcomma">
														<xsl:value-of select="substring-after($str1, ',')"/>
														<xsl:value-of select="substring-before($str1, ',')"/>
													</xsl:variable>
													<xsl:if test="contains($geoglist, translate($tmpcomma, '.', ''))!=0">
														<marc:subfield code="z">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
														<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
															<marc:subfield code="v">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
															<marc:subfield code="y">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($genx, translate(substring-before($str2, '--'), '.', ''))!=0">
															<marc:subfield code="x">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($genx, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0">
															<marc:subfield code="z">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
													</xsl:if>
													<xsl:if test="contains($geoglist, translate($tmpcomma, '.', ''))=0">
														<xsl:if test="contains($formlist, translate($str1, '.', ''))!=0">
															<marc:subfield code="v">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geny, translate($str1, '.', ''))!=0">
															<marc:subfield code="y">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($formlist, translate($str1, '.', ''))=0 and contains($geny, translate($str1, '.', ''))!=0">
															<marc:subfield code="x">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
															<marc:subfield code="v">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
															<marc:subfield code="y">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))!=0">
															<marc:subfield code="z">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0 and contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0">
															<marc:subfield code="x">
																<xsl:value-of select="substring-before($str2, '--')"/>
															</marc:subfield>
														</xsl:if>
													</xsl:if>
												</xsl:when>
												<xsl:otherwise>
													<xsl:if test="contains($formlist, translate($str1, '.', ''))!=0">
														<marc:subfield code="v">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geny, translate($str1, '.', ''))!=0">
														<marc:subfield code="y">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($formlist, translate($str1, '.', ''))=0 and contains($geny, translate($str1, '.', ''))!=0">
														<marc:subfield code="x">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
														<marc:subfield code="v">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
														<marc:subfield code="y">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))!=0">
														<marc:subfield code="z">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0 and contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0">
														<marc:subfield code="x">
															<xsl:value-of select="substring-before($str2, '--')"/>
														</marc:subfield>
													</xsl:if>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:otherwise>
							</xsl:choose>
							<xsl:call-template name="subject_tokenize">
								<xsl:with-param name="string" select="$newstr2"/>
								<xsl:with-param name="type" select="'x'"/>
							</xsl:call-template>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:if test="contains($geoglist, translate($str1, '.', ''))!=0">
							<marc:subfield code="z">
								<xsl:value-of select="$str1"/>
							</marc:subfield>
							<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
								<marc:subfield code="v">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
							<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
								<marc:subfield code="y">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
							<xsl:if test="contains($genx, translate(substring-before($str2, '--'), '.', ''))!=0">
								<marc:subfield code="x">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
							<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($genx, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0">
								<marc:subfield code="z">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
						</xsl:if>
						<xsl:if test="contains($formlist, translate($str1, '.', ''))!=0">
							<marc:subfield code="v">
								<xsl:value-of select="$str1"/>
							</marc:subfield>
						</xsl:if>
						<xsl:if test="contains($geny, translate($str1, '.', ''))!=0">
							<marc:subfield code="y">
								<xsl:value-of select="$str1"/>
							</marc:subfield>
						</xsl:if>
						<xsl:if test="contains($formlist, translate($str1, '.', ''))=0 and contains($geny, translate($str1, '.', ''))!=0">
							<marc:subfield code="x">
								<xsl:value-of select="$str1"/>
							</marc:subfield>
						</xsl:if>
						<xsl:if test="contains($geoglist, translate($str1, '.', ''))=0">
							<xsl:if test="contains($formlist, translate(substring-before($str2, '--'), '.', ''))!=0">
								<marc:subfield code="v">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
							<xsl:if test="contains($geny, translate(substring-before($str2, '--'), '.', ''))!=0">
								<marc:subfield code="y">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
							<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))!=0">
								<marc:subfield code="z">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
							<xsl:if test="contains($geoglist, translate(substring-before($str2, '--'), '.', ''))=0 and contains($geny, translate(substring-before($str2, '--'), '.', ''))=0 and contains($formlist, translate(substring-before($str2, '--'), '.', ''))=0">
								<marc:subfield code="x">
									<xsl:value-of select="substring-before($str2, '--')"/>
								</marc:subfield>
							</xsl:if>
						</xsl:if>
						<xsl:call-template name="subject_tokenize">
							<xsl:with-param name="string" select="$newstr2"/>
							<xsl:with-param name="type" select="'x'"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
			<xsl:if test="contains($str2, '--')=0">
				<xsl:variable name="tmpvar" select="translate($str2, '.', '')"/>
				<xsl:choose>
					<xsl:when test="/ead/archdesc/controlaccess/controlaccess/geogname[@encodinganalog='651']">
						<xsl:variable name="bool_found2"><xsl:for-each select="/ead/archdesc/controlaccess/controlaccess/geogname[@encodinganalog='651']"><xsl:if test="contains(normalize-space(.), $tmpvar)!=0">
									true, 
								</xsl:if></xsl:for-each>
							false, 
						</xsl:variable>
						<xsl:if test="contains($bool_found2,'true')!=0">
							<xsl:for-each select="/ead/archdesc/controlaccess/controlaccess/geogname[@encodinganalog='651']">
								<xsl:if test="contains(normalize-space(.), translate($str2, '.', ''))!=0">
									<marc:subfield code="z">
										<xsl:value-of select="$str1"/>
									</marc:subfield>
<!--If it attaches and there is a second set of strings, its probably a $z as well.-->
									<marc:subfield code="z">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
							</xsl:for-each>
						</xsl:if>
						<xsl:if test="contains($bool_found2,'true')=0">
							<xsl:choose>
								<xsl:when test="contains($geoglist, translate($str1, '.', ''))!=0">
									<marc:subfield code="z">
										<xsl:value-of select="$str1"/>
									</marc:subfield>
									<xsl:if test="contains($formlist, translate($str2, '.', ''))!=0">
										<marc:subfield code="v">
											<xsl:value-of select="$str2"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($geny, translate($str2, '.', ''))!=0">
										<marc:subfield code="y">
											<xsl:value-of select="$str2"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($genx, translate($str2, '.', ''))!=0">
										<marc:subfield code="x">
											<xsl:value-of select="$str2"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($formlist, translate($str2, '.', ''))=0 and contains($geny, translate($str2, '.', ''))=0 and contains($genx, translate($str2, '.', ''))=0">
										<marc:subfield code="z">
											<xsl:value-of select="$str2"/>
										</marc:subfield>
									</xsl:if>
								</xsl:when>
								<xsl:otherwise>
									<xsl:choose>
										<xsl:when test="contains($str1, '(')!=0">
											<xsl:choose>
												<xsl:when test="contains($genx, translate($str1, '.', ''))!=0">
													<marc:subfield code="x">
														<xsl:value-of select="$str1"/>
													</marc:subfield>
												</xsl:when>
												<xsl:otherwise>
													<marc:subfield code="z">
														<xsl:value-of select="$str1"/>
													</marc:subfield>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:when>
										<xsl:otherwise>
											<xsl:choose>
												<xsl:when test="contains($str1, ',')!=0">
													<xsl:variable name="tmpcomma">
														<xsl:value-of select="substring-after($str1, ',')"/>
														<xsl:value-of select="substring-before($str1, ',')"/>
													</xsl:variable>
													<xsl:if test="contains($geoglist, translate($tmpcomma,'.',''))!=0">
														<marc:subfield code="z">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
														<xsl:if test="contains($formlist, translate($str2, '.', ''))!=0">
															<marc:subfield code="v">
																<xsl:value-of select="$str2"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geny, translate($str2, '.', ''))!=0">
															<marc:subfield code="y">
																<xsl:value-of select="$str2"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($genx, translate($str2, '.', ''))!=0">
															<marc:subfield code="x">
																<xsl:value-of select="$str2"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($formlist, translate($str2, '.', ''))=0 and contains($geny, translate($str2, '.', ''))=0 and contains($genx, translate($str2, '.', ''))=0">
															<marc:subfield code="z">
																<xsl:value-of select="$str2"/>
															</marc:subfield>
														</xsl:if>
													</xsl:if>
													<xsl:if test="contains($geoglist, translate($tmpcomma,'.',''))=0">
														<xsl:if test="contains($formlist, translate($str1, '.', ''))!=0">
															<marc:subfield code="v">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geny, translate($str1, '.', ''))!=0">
															<marc:subfield code="y">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($formlist, translate($str1, '.', ''))=0 and contains($geny, translate($str1, '.', ''))=0 and contains($geoglist, translate($tmpcomma,'.',''))=0">
															<marc:subfield code="x">
																<xsl:value-of select="$str1"/>
															</marc:subfield>
														</xsl:if>
														<xsl:if test="contains($geoglist, translate($tmpcomma,'.',''))=0">
															<xsl:if test="contains($formlist, translate($str2, '.', ''))!=0">
																<marc:subfield code="v">
																	<xsl:value-of select="$str2"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($geny, translate($str2, '.', ''))!=0">
																<marc:subfield code="y">
																	<xsl:value-of select="$str2"/>
																</marc:subfield>
															</xsl:if>
															<xsl:if test="contains($formlist, translate($str2, '.', ''))=0 and contains($geny, translate($str2, '.', ''))=0">
																<marc:subfield code="x">
																	<xsl:value-of select="$str2"/>
																</marc:subfield>
															</xsl:if>
														</xsl:if>
													</xsl:if>
												</xsl:when>
												<xsl:otherwise>
													<xsl:if test="contains($formlist, translate($str1, '.', ''))!=0">
														<marc:subfield code="v">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($geny, translate($str1, '.', ''))!=0">
														<marc:subfield code="y">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
													</xsl:if>
													<xsl:if test="contains($formlist, translate($str1, '.', ''))=0 and contains($geny, translate($str1, '.', ''))=0">
														<marc:subfield code="x">
															<xsl:value-of select="$str1"/>
														</marc:subfield>
													</xsl:if>
												</xsl:otherwise>
											</xsl:choose>
										</xsl:otherwise>
									</xsl:choose>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
					</xsl:when>
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="contains($geoglist, translate($str1, '.', ''))!=0">
								<marc:subfield code="z">
									<xsl:value-of select="$str1"/>
								</marc:subfield>
								<xsl:if test="contains($formlist, translate($str2, '.', ''))!=0">
									<marc:subfield code="v">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($geny, translate($str2, '.', ''))!=0">
									<marc:subfield code="y">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($genx, translate($str2, '.', ''))!=0">
									<marc:subfield code="x">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($formlist, translate($str2, '.', ''))=0 and contains($geny, translate($str2, '.', ''))=0 and contains($genx, translate($str2, '.', ''))=0">
									<marc:subfield code="z">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
							</xsl:when>
							<xsl:otherwise>
								<xsl:if test="contains($formlist, translate($str1, '.', ''))!=0">
									<marc:subfield code="v">
										<xsl:value-of select="$str1"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($geny, translate($str1, '.', ''))!=0">
									<marc:subfield code="y">
										<xsl:value-of select="$str1"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($formlist, translate($str1, '.', ''))=0 and contains($geny, translate($str1, '.', ''))=0">
									<marc:subfield code="x">
										<xsl:value-of select="$str1"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($formlist, translate($str2, '.', ''))!=0">
									<marc:subfield code="v">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($geny, translate($str2, '.', ''))!=0">
									<marc:subfield code="y">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
								<xsl:if test="contains($formlist, translate($str2, '.', ''))=0 and contains($geny, translate($str2, '.', ''))=0">
									<marc:subfield code="x">
										<xsl:value-of select="$str2"/>
									</marc:subfield>
								</xsl:if>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:if>
		</xsl:if>
		<xsl:if test="contains($string, '--')=0">
			<xsl:choose>
				<xsl:when test="contains($geoglist, translate($string, '.', ''))!=0">
					<marc:subfield code="z">
						<xsl:value-of select="$string"/>
					</marc:subfield>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="contains($string, '(')!=0">
							<xsl:choose>
								<xsl:when test="contains($genx, translate($string, '.', ''))!=0">
									<marc:subfield code="x">
										<xsl:value-of select="$string"/>
									</marc:subfield>
								</xsl:when>
								<xsl:otherwise>
									<marc:subfield code="z">
										<xsl:value-of select="$string"/>
									</marc:subfield>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="contains($string, ',')!=0">
									<xsl:variable name="tmpcomma">
										<xsl:value-of select="substring-after($string, ',')"/>
										<xsl:value-of select="substring-before($string, ',')"/>
									</xsl:variable>
									<xsl:if test="contains($geoglist, translate($tmpcomma, '.', ''))!=0">
										<marc:subfield code="z">
											<xsl:value-of select="$string"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($geoglist, translate($tmpcomma, '.', ''))=0">
										<xsl:if test="contains($formlist, translate($string, '.', ''))!=0">
											<marc:subfield code="v">
												<xsl:value-of select="$string"/>
											</marc:subfield>
										</xsl:if>
										<xsl:if test="contains($geny, translate($string, '.', ''))!=0">
											<marc:subfield code="y">
												<xsl:value-of select="$string"/>
											</marc:subfield>
										</xsl:if>
										<xsl:if test="contains($formlist, translate($string, '.', ''))=0 and contains($geny, translate($string, '.',''))=0">
											<marc:subfield code="x">
												<xsl:value-of select="$string"/>
											</marc:subfield>
										</xsl:if>
									</xsl:if>
								</xsl:when>
								<xsl:otherwise>
									<xsl:if test="contains($formlist, translate($string, '.', ''))!=0">
										<marc:subfield code="v">
											<xsl:value-of select="$string"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($geny, translate($string, '.', ''))!=0">
										<marc:subfield code="y">
											<xsl:value-of select="$string"/>
										</marc:subfield>
									</xsl:if>
									<xsl:if test="contains($formlist, translate($string, '.', ''))=0 and contains($geny, translate($string, '.',''))=0">
										<marc:subfield code="x">
											<xsl:value-of select="$string"/>
										</marc:subfield>
									</xsl:if>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:if>
	</xsl:template>
	<xsl:template match="head"/>
	<xsl:template match="p">
		<xsl:value-of select="."/>
	</xsl:template>
	<xsl:template name="geoglist">
		Canada 
Eastern Canada (1871 and later)
Atlantic Provinces.  Atlantic Canada
Maritime Provinces.
Nova Scotia
Prince Edward Island
New Brunswick
Newfoundland 
Labrador
Central Provinces 
Quebec 
Ontario
Western Canada
Prairie Provinces
Manitoba 
Saskatchewan 
Alberta
Cordilleran Provinces and Territories 
British Columbia
Northern Canada
Yukon
Northwest Territories 
Saint Pierre and Miquelon Islands
United States and possessions 
United States 
Eastern United States, 1870 and later
.Atlantic States 
Northeastern States
Northeast Atlantic States
New England
Maine 
New Hampshire 
Vermont 
Massachusetts 
Rhode Island 
Connecticut 
Middle Atlantic States 
New York (State) 
New Jersey 
Pennsylvania 
Delaware 
Maryland 
District of Columbia. Washington, D.C. 
Southern States.  Confederate States of America 
Southeastern States
Southeast Atlantic States 
Virginia 
West Virginia 
North Carolina 
South Carolina 
Georgia 
Florida 
South Central States 
East South Central States 
Kentucky 
Tennessee 
Alabama 
Mississippi 
West South Central States.  Old Southwest 
Arkansas 
Louisiana 
Oklahoma 
Texas 
Central States 
The West 
North Central States 
East North Central States. Old Northwest 
Ohio 
Indiana 
Illinois 
Michigan 
Wisconsin 
Northwestern United States 
West North Central States 
Minnesota 
Iowa 
Missouri 
North Dakota 
South Dakota 
Nebraska 
Kansas 
Pacific and Mountain States.  Far West
Rocky Mountain States 
Pacific States 
Pacific Northwest Northwest, Pacific
Montana 
Wyoming 
Idaho 
Washington (State) 
Oregon 
Southwestern States 
New Southwest 
Colorado 
New Mexico 
Arizona 
Utah 
Nevada 
California 
Alaska 
Hawaii. Sandwich Islands 
Caribbean area 
Latin America
Mexico 
Northern States (Mexico) 
Tamaulipas 
Nuevo Leon 
Coahuila 
Chihuahua 
Sonora 
Baja California (Region) 
Baja California Norte 
Baja California Sur 
Sinaloa 
Durango 
Zacatecas 
San Luis Potosi 
Central States 
Veracruz 
Puebla 
Tlaxcala 
Hidalgo 
Mexico (State) 
Mexico (Federal District)
Morelos
Michoacan [Michoacán]
Queretaro [Querétaro]
Guanajuato
Jalisco 
Aguascalientes 
Nayarit 
Colima 
Southern States (Mexico) 
Guerrero 
Oaxaca 
Chiapas 
Tabasco
Campeche 
Yucatan [Yucatán]
Quintana Roo 
Central America
Guatemala
Belize. British Honduras
Honduras 
El Salvador
Nicaragua 
Costa Rica 
Panama 
West Indies 
Greater Antilles 
Cuba 
Hispaniola
Haiti 
Dominican Republic.  Santo Domingo
Jamaica
Gayman Islands 
Puerto Rico
Bahamas.  Lucayos 
Turks and Caicos Islands 
Lesser Antilles.  Caribbees
Virgin Islands (General) 
Virgin Islands of the United States 
British Virgin Islands 
Leeward Islands 
Saint Christopher (Island).  Saint Kitts 
Anguilla
Antigua (Island and independent State) 
Montserrat
French West Indies
Guadeloupe 
Martinique
Windward Islands 
Dominica 
Saint Lucia 
Saint Vincent (Island and Independent State) 
Grenada (Island and Independent State) 
Barbados
Trinidad and Tobago
Trinidad
Tobago
Netherlands Antilles.  Dutch West Indies 
Aruba
Bonaire 
Curacao
South America 
Atlantic coast and continental shelf
Pacific coast and continental shelf
Guianas
Guyana.  British Guiana
Surinam.  Dutch Guiana
French Guiana 
Venezuela
Columbia
Ecuador
Peru
Bolivia
Chile 
Argentina 
Uruguay
Paraguay
Brazil 
North Brazil.  Amazon Basin 
Rondonia [Rondônia]. Guapore [Guaporé]
Acre
Amazonas
Roraima.  Rio Branco
Para [Pará]
Amapa [Amapá]
Northeast Brazil 
Maranhao [Maranhão]
Piaui [Piauí](Piauhy)
Ceara [Ceará]
Rio Grande do Norte
Paraiba [Paraíba]
Pernambuco
Alagoas [Alagôas]
Fernando de Noronha 
East Brazil.  Southeastern States 
Sergipe
Bahia
Minas Gerais
Espirito Santo [Espírito Santo] 
Rio de Janeiro (State)
Guanabara +    Until April 1960, it was known as Distrito Federal
South Brazil 
Sao Paulo [São Paulo]
Parana [Paraná]
Santa Catarina 
Rio Grande do Sul 
Central West Brazil 
Mato Grosso
Mato Grosso do Sul
Tocantins (1988-)
Goias 
Distrito Federal (1960-)
	</xsl:template>
	<xsl:template name="geny">
	10th century
11th century
12th century
13th century
14th century
15th century
16th century
17th century
1868-
18th century
1965-
19th century
20th century
21st century
500-1400
Chosºn dynasty, 1392-1910
Early church, ca. 30-600
Early modern and Elizabethan, 1500-1600
Early modern, 1500-1700
Edo period, 1600-1868
Heian period, 794-1185
Kamakura-Momoyama periods, 1185-1600
Koryº period, 935-1392
Meiji period, 1868-1912
Middle Ages, 600-1500
Middle English, 1100-1500
Ming-Qing dynasties, 1368-1912
Modern period, 1500-
Old English, ca. 450-1100
Qin-Han dynasties, 221 B.C.-220 A.D.
Restoration, 1660-1700
Revolution, 1775-1783, [Spanish-American War, 1898, etc.]
Revolution, 1775-1783, [War of 1812, etc.]
Song-Yuan dynasties, 960-1368
Taish¸ period, 1912-1926
Tang-Five dynasties, 618-960
Three kingdoms-Sui dynasty, 220-618
To 1500
To 1600
To 1868
To 1900
To 221 B.C.
To 500
To 794
To 935

	</xsl:template>
	<xsl:template name="genx">
Abandonment
Abdication
Ability testing
Abnormalities
Abscess
Absolute constructions
Absorption and adsorption
Abstracting and indexing
Abuse of
Accents and accentuation
Access control
Accidents
Accounting
Accreditation
Acoustic properties
Acoustics
Acquisition
Activity programs
Acupuncture
Adaptation
Additives
Address, Forms of
Adjectivals
Adjective
Adjuvant treatment
Administration
Administrative and political divisions
Admission
Adult education
Adverb
Adverbials
Adversaries
Aerial exploration
Aerial gunners
Aerial operations
Aerial operations, American, [British, etc.]
Aerodynamics
Aesthetics
Affinity labeling
Affixes
African American officers
African American students
African American troops
African Americans
African influences
Age
Age determination
Age factors
Aging
Agonists
Agreement
Agriculture
Agriculture, [America, etc.]
Aides
Aids and devices
Air conditioning
Air content
Air disc brakes
Air police
Air suspension
Airborne troops
Airmen
Alcohol use
Alignment
Allegorical interpretations
Allergenicity
Allusions
Alluvial plain
Alphabet
Alternative treatment
Altitudes
Alumni and alumnae
Ambulances
American influences
American Legion
Amphibious operations
Analogy
Analysis
Analysis, appreciation
Anaphora
Anatomy
Anglican Communion, [Lutheran Church, etc.]
Animacy
Animal models
Ankylosis
Annexation to ...
Anniversaries, etc.
Anodic oxidation
Anonyms and pseudonyms
Antagonists
Anthropometry
Antiaircraft artillery operations
Antilock brake systems
Antiquities
Antiquities, Byzantine
Antiquities, Celtic
Antiquities, Germanic
Antiquities, Phoenician
Antiquities, Roman
Antiquities, Slavic
Antiquities, Turkish
Anti-theft devices
Apheresis
Appointment, call, and election
Appointments and retirements
Apposition
Appreciation
Apprentices
Appropriate technology
Appropriations and expenditures
Arab influences
Arabic, [Italian, etc.]
Archaeological collections
Archaisms
Archival resources
Area
Armed Forces
Armenian authors
Armistices
Armored troops
Art collections
Art patronage
Article
Artificial insemination
Artificial spawning
Artillery
Artillery operations
Artillery operations, American, [British, French, etc.]
Asian Americans
Asian authors
Asian influences
Aspect
Aspiration
Assassination
Assassination attempt, [date]
Assassination attempts
Assaying
Asyndeton
Atrocities
Attendance
Attitudes
Audio equipment
Audio-visual aids
Auditing
Augment
Australian influences
Authorized, [Living Bible, Revised Standard, etc.]
Authorship
Automatic control
Automation
Autonomous communities
Autonomous regions
Autonomy and independence movements
Autopsy
Auxiliary verbs
Aviation
Aviation electronics technicians
Aviation mechanics
Aviation supplies and stores
Awards
Axles
Bahai interpretations
Balancing
Balloons
Bandmasters
Bands
Baptists, [Catholic Church, etc.]
Barracks and quarters
Barrier-free design
Baseball
Basketball
Basque authors
Basques
Batteries
Battlefields
Bearings
Behavior
Benefactors
Benefices
Bengali authors
Biblical teaching
Bilingual method
Bioaccumulation
Bioavailability
Biocompatibility
Biodegradation
Biological control
Biological warfare
Biopsy
Biotechnology
Birth
Birthplace
Bishops
Black authors
Black interpretations
Blacks
Blockades
Blood-vessels
Blunt trauma
Boats
Boatswains
Boatswain's mates
Bodies
Boiler technicians
Bomb reconnaissance
Bonding
Boning
Bonsai collections
Books and reading
Boundaries
Boy Scouts
Brakes
Brazilian influences
Brazing
Breaking in
Breeding
Brittleness
Brothers
Buddhism, [Christianity, etc.]
Buddhism, [Judaism, etc.]
Buddhist authors
Buddhist influences
Buddhist interpretations
Buildings
Buildings, structures, etc.
Bumpers
Business English
Business management
Buying
By-products
Calcification
Calibration
Camouflage
Campaigns
Camshafts
Cancer
Cannibalism
Canon
Canonical criticism
Cantons
Capital and capitol
Capital investments
Capital productivity
Capitalization
Captivity, [dates]
Carbon content
Carburetors
Carcasses
Carcinogenicity
Cardiovascular system
Care
Care and hygiene
Career in [specific field or discipline]
Cartography
Case
Case grammar
Casualties
Catalan authors
Catalytic converters
Cataphora
Categorial grammar
Catholic authors
Catholic Church
Catholic Church, [Methodist Church, etc.]
Caucuses
Causative
Causes
Cavalry
Cavalry operations
Cavitation erosion
Celtic authors
Celtic influences
Censorship
Censures
Centennial celebrations, etc.
Certification
Channelization
Channels
Chaplains
Chaplain's assistants
Characters
Charitable contributions
Charities
Chassis
Chemical defenses
Chemical resistance
Chemical warfare
Chemoprevention
Chemotaxonomy
Chemotherapy
Childhood and youth
Children
Children, [Jews, Physicians, etc.]
Children's use
Chinese authors
Chinese influences
Chiropractic treatment
Choral organizations
Christian authors
Christian influences
Christian Science authors
Christianity, [Islam, etc.]
Church history
Churches
Cipher
Citizen participation
Civic action
Civil functions
Civil rights
Civilian employees
Civilian relief
Civilization
Cladistic analysis
Claims
Claims vs. ...
Classical influences
Classifiers
Clauses
Cleaning
Clergy
Clerical work
Climate
Climatic factors
Clitics
Clones
Cloning
Clothing
Cloture
Clutches
Cobalt content
Codification
Cognate words
Coin collections
Cold weather conditions
Cold weather operation
Cold working
Collaboration
Collaborationists
Collectibles
Collection and preservation
Collective nouns
Collectors and collecting
Collier service
Collision avoidance systems
Collision damage
Colonial forces
Colonial influence
Colonies
Colonization
Color
Coloring
Combat sustainability
Combustion
Comedies
Commando operations
Commando troops
Commerce
Commercial policy
Commissariat
Committees
Communication
Communication  systems
Communication systems
Communications
Comparative clauses
Comparative method
Comparison
Competitions
Complaints against
Complement
Compliance costs
Complications
Composition
Composition and exercises
Compound words
Compression testing
Computer control systems
Computer network resources
Computer networks
Computer programs
Computer simulation
Computer-aided design
Computer-assisted instruction
Computer-assisted instruction for foreign speakers
Computer-assisted instruction for French, [Spanish, etc.] speakers
Concentration camps
Concessive clauses
Condition scoring
Conditionals
Conduct of life
Conference committees
Confiscations and contributions
Conformation
Confucian influences
Conjunctions
Connectives
Conscientious objectors
Conscript labor
Conservation
Conservation and restoration
Consonants
Constituent communication
Construction
Construction mechanics
Contamination
Contemporaries
Contested elections
Context
Contracting out
Contraction
Control
Control systems
Controlled release
Cooling
Cooling systems
Cooperative marketing
Coordinate constructions
Copies, Curious
Coronation
Corrosion
Corrosion fatigue
Corrupt practices
Cossacks
Cost control
Cost effectiveness
Cost of operation
Cost-of-living adjustments
Costs
Counseling of
Counterfeit money
Counting
Court and courtiers
Cracking
Craniology
Crankshafts
Crashworthiness
Credit ratings
Creep
Crimes against
Criminal provisions
Criticism and interpretation
Criticism, Form
Criticism, interpretation, etc.
Criticism, interpretation, etc., Jewish
Criticism, Narrative
Criticism, Redaction
Criticism, Textual
Cruise, [date]
Cryopreservation
Cryosurgery
Cryotherapy
Cryptography
Cult
Cultural assimilation
Cultural control
Cultural policy
Cultures and culture media
Curing
Customer services
Customizing
Customs and practices
Cuttings
Cylinder blocks
Cylinder heads
Cylinders
Cysts
Cytochemistry
Cytodiagnosis
Cytogenetics
Cytology
Cytopathology
Cytotaxonomy
Dalit authors
Data processing
Date of authorship
Dating
Death
Death and burial
Death mask
Decay
Decentralization
Deception
Decision making
Declension
Decontamination
Decoration
Defects
Defense measures
Defenses
Definiteness
Degradation
Degrees
Deinstitutionalization
Deixis
Deletion
Demobilization
Demonstratives
Denaturation
Density
Dental care
Deoxidizing
Departments
Dependency grammar
Dependency on [place]
Dependency on foreign countries
Deposition
Deprivation of the clerical garb
Deputy speakers
Deregulation
Derivatives
Description and travel
Desertions
Design
Design and construction
Destruction and pillage
Desulphurization
Detection
Deterioration
Determiners
Development
Devotional use
Dewatering
Diacritics
Diagnosis
Diagnostic use
Dialectology
Dialects
Diction
Diet therapy
Differentials
Differentiation
Differentiation therapy
Diffusion rate
Digestive organs
Digitization
Dilatation
Diminutives
Dioceses
Diphthongs
Diplomatic history
Diplomatic service
Dipole moments
Direct object
Disc brakes
Disciples
Discipline
Discourse analysis
Discovery and exploration
Disease and pest resistance
Disease-free stock
Diseases
Diseases and injuries
Diseases and pests
Disinfection
Dislocation
Dismissal of
Disorders
Dispersal
Displacement
Display systems
Dissection
Dissertations
Dissimilation
Dissolution
Distances, etc.
Divorce
Doctrines
Documentation
Domestic animals
Doors
Dormancy
Dose-response relationship
Dosimetric treatment
Draft resisters
Dramatic production
Dramatic works
Dramaturgy
Dravidian authors
Drill and tactics
Drought tolerance
Drug testing
Drug use
Druze authors
Drying
Ductility
Dust control
Dutch, [German, etc.]
Dwellings
Dynamic testing
Dynamics
Earthquake effects
Eclectic treatment
Ecology
Econometric models
Economic aspects
Economic conditions
Economic integration
Economic policy
Ecophysiology
Editions, Curious
Education
Education (Continuing education)
Education (Early childhood)
Education (Elementary)
Education (Graduate)
Education (Higher)
Education (Middle school)
Education (Preschool)
Education (Primary)
Education (Secondary)
Education and the war, [revolution, etc.]
Effect of acid deposition on
Effect of acid precipitation on
Effect of air pollution on
Effect of aircraft on
Effect of altitude on
Effect of arsenic on
Effect of atmospheric carbon dioxide on
Effect of atmospheric deposition on
Effect of atmospheric nitrogen dioxide on
Effect of atmospheric ozone on
Effect of automation on
Effect of browsing on
Effect of cadmium on
Effect of chemicals on
Effect of cold on
Effect of contaminated sediments on
Effect of dams on
Effect of dichlorophenoxyacetic acid on
Effect of dredging on
Effect of drought on
Effect of drugs on
Effect of environment on
Effect of ethephon on
Effect of exotic animals on
Effect of explosive devices on
Effect of factory and trade waste on
Effect of ferrous sulphate on
Effect of fires on
Effect of fishing on
Effect of floods on
Effect of fluorides on
Effect of fluorine on
Effect of forest management on
Effect of freezes on
Effect of gamma rays on
Effect of gases on
Effect of global warming on
Effect of glyphosate on
Effect of grazing on
Effect of greenhouse gases on
Effect of habitat modification on
Effect of heat on
Effect of heavy metals on
Effect of high temperatures on
Effect of human beings on
Effect of hunting on
Effect of ice on
Effect of implants on
Effect of imprisonment on
Effect of inflation on
Effect of insecticides on
Effect of iron on
Effect of light on
Effect of logging on
Effect of low temperatures on
Effect of magnesium on
Effect of manganese on
Effect of metals on
Effect of minerals on
Effect of music on
Effect of noise on
Effect of oil spills on
Effect of oxygen on
Effect of ozone on
Effect of pesticides on
Effect of pollution on
Effect of potassium on
Effect of predation on
Effect of radiation on
Effect of radioactive pollution on
Effect of salt on
Effect of sediments on
Effect of soil acidity on
Effect of sound on
Effect of space flight on
Effect of storms on
Effect of stray currents on
Effect of stress on
Effect of sulphur on
Effect of technological  innovations on
Effect of technological innovations on
Effect of tempearture on
Effect of temperature on
Effect of thermal pollution on
Effect of trampling on
Effect of trichloroethylene on
Effect of turbidity on
Effect of ultraviolet radiation on
Effect of vibration on
Effect of volcanic eruptions on
Effect of water levels on
Effect of water pollution on
Effect of water quality on
Effect of water waves on
Effect of wind on
Effectiveness
Eggs
Egyptian influences
Elastic properties
Election districts
Elections
Elections, [date]
Elective system
Electric equipment
Electric generators
Electric installations
Electric properties
Electric wiring
Electromechanical analogies
Electrometallurgy
Electronic equipment
Electronic fuel injection systems
Electronic information resources
Electronic installations
Electronic intelligence
Electronic systems
Electronic technicians
Elision
Elks
Ellipsis
Embouchure
Embrittlement
Embryology
Embryos
Emigration and immigration
Emphasis
Employee participation
Employees
Employment
Enclitics
Endocrine aspects
Endocrinology
Endoscopic surgery
Endowments
Energy conservation
Energy consumption
Engineering and construction
English
English influences
English, [French, German, etc.]
Entrance examinations
Entrance requirements
Environmental aspects
Environmental conditions
Environmental engineering
Environmental enrichment
Environmental testing
Epidemiology
Epithets
Eponyms
Equipment
Equipment and supplies
Ergative constructions
Erosion
Errors of usage
Errors, inventions, etc.
Eruption, [date]
Eruptions
Essence, genius, nature
Estate
Estimates
Etching
Ethics
Ethnic identity
Ethnic relations
Ethnobiology
Ethnobotany
Ethnological collections
Ethnomusicological collections
Ethnozoology
Etiology
Etymology
Euphemism
European authors
European influences
Evacuation of civilians
Evaluation
Evangelicalism
Evidences, authority, etc.
Evolution
Examination
Examinations
Exclamations
Excretion
Exercise
Exercise therapy
Exhaust gas
Exhaust systems
Exile
Existential constructions
Expansion and contraction
Experiments
Expertising
Explication
Explosion, [date]
Expulsion
Extra-canonical parallels
Extrusion
Facilities
Faculty
Faculty housing
Fading
Family
Family relationships
Fatigue
Feed utilization efficiency
Feeding and feeds
Fees
Feminist criticism
Fenders
Fertility
Fertilization
Fertilizers
Fetuses
Fibrosis
Fictional works
Field experiments
Field service
Field work
Figures of speech
Finance
Finance, Personal
Fingering
Finishing
Finnish influences
Fire controlmen
Fire fighters
Fire testing
Fire use
Fire, [date]
Firearms
Firemen
Fires and fire prevention
First editions
First performances
Fishing
Flags
Flammability
Flight
Flight officers
Flight surgeons
Flowering
Flowering time
Fluid dynamics
Food
Food service
Food supply
Football
Forced repatriation
Forecasting
Foreign authors
Foreign auxiliaries
Foreign bodies
Foreign economic relations
Foreign elements
Foreign influences
Foreign language competency
Foreign ownership
Foreign public opinion
Foreign public opinion, Austrian, [British, etc.]
Foreign relations
Foreign relations administration
Foreign service
Foreign speakers
Foreign words and phrases
Forgeries
Formability
Fracture
Fractures
Freedom of debate
Freemasonry
French influences
French, [German, etc.]
French, [Greek, Latin, etc.]
French, [Italian, etc.]
French, [Latin, etc.]
French, [Spanish, etc.]
French, [Spanish, etc.] speakers
Freshmen
Friends and associates
Front-wheel drive
Frost damage
Frost protection
Frost resistance
Fuel
Fuel consumption
Fuel injection systems
Fuel supplies
Fuel systems
Fume control
Fumigation
Function words
Funds and scholarships
Funeral customs and rites
Furloughs
Furniture
Galician influences
Gallicisms
Galvanomagnetic properties
Gambling
Games
Gas producers
Gay interpretations
Gays
Gemination
Gender
Gene therapy
General staff officers
Generative organs
Genetic aspects
Genetic engineering
Genetics
Genome mapping
Geographic information systems
Geographical distribution
Geography
German Americans
German authors
German influences
German, [Italian, etc.]
Germplasm resources
Gerund
Gerundive
Girl Scouts
Gold discoveries
Golf
Government
Government jargon
Government ownership
Government policy
Government relations
Governments in exile
Gradation
Grading
Graduate students
Graduate work
Graduation requirements
Graffiti
Grafting
Grammar
Grammar, Comparative
Grammar, Generative
Grammar, Historical
Grammatical categories
Grammaticalization
Graphemics
Graphic methods
Greek authors
Greek influences
Grilles
Grooming
Ground support
Growth
Guard duty
Guided missile personnel
Gunners
Habitat
Habitat suitability index models
Habitations
Hadith
Half-life
Handling
Handling characteristics
Haplology
Hardenability
Hardiness
Harmonics
Harmonies, English, [French, German, etc.]
Harmony
Harvesting
Harvesting time
Headquarters
Health
Health and hygiene
Health aspects
Health promotion services
Health risk assessment
Heat treatment
Heating
Heating and ventilation
Hebrew, [Italian, etc.]
Heirloom varieties
Helium content
Hemorrhage
Heraldry
Herbarium
Herbicide injuries
Hermeneutics
Heteronyms
Hiatus
Hibernation
Hindu authors
Hindu interpretations
Hispanic Americans
Histochemistry
Histology
Histopathology
Historical geography
Historiography
History
History and criticism
History of Biblical events
History of contemporary events
History of doctrines
History, Local
History, Military
History, Naval
Hockey
Home care
Home range
Homeopathic treatment
Homes and haunts
Homiletical use
Homing
Homonyms
Honor system
Honorific
Honorific unit titles
Honors courses
Hormone therapy
Horns
Hospice care
Hospital care
Hospital ships
Hospitals
Host plants
Hostages
Hot weather conditions
Hot working
Housing
Hungarian influences
Hunting
Husking
Hybridization
Hydatids
Hydraulic equipment
Hydrogen content
Hydrogen embrittlement
Hypertrophy
Ice breaking operations
Ideophone
Idioms
Ignition
Imaging
Immersion method
Immunodiagnosis
Immunological aspects
Immunology
Immunotherapy
Impact testing
Impeachment
Imperative
Implements
Imprisonment
In bookplates
In literature
In mass media
In motion pictures
Inauguration, [date]
Inclusions
Incubation
Indeclinable words
Indian influences
Indian troops
Indians
Indic influences
Indicative
Indirect discourse
Indirect object
Induced spawning
Industrial applications
Industrial capacity
Industries
Infallibility
Infancy
Infantry
Infections
Infertility
Infinitival constructions
Infinitive
Infixes
Inflation pressure
Inflection
Influence
Influence on foreign languages
Influence on French, [Italian, etc.]
Information resources
Information resources management
Information services
Information technology
Information techology
Inhibitors
Innervation
Inoculation
Insect resistance
In-service training
Insignia
Inspection
Inspiration
Installation
Institutional care
Instruction and study
Instrument panels
Instruments
Insulation
Insurance
Insurance requirements
Integrated control
Intellectual life
Intelligence levels
Intelligence specialists
Intelligence testing
Intensification
Interiors
Interjections
Intermediate care
International cooperation
International status
Interpretation
Interpretation (Phrasing, dynamics, etc.)
Interpretation and construction
Interrogative
Interventional radiology
Intonation
Intraoperative radiotherapy
Inventory control
Investigation
Iranian influences
Irish Americans
Irish authors
Irish influences
Irrigation
Islamic influences
Islamic interpretations
Isotopes
Italian Americans
Italian authors
Italian influences
Jaina authors
Japanese Americans
Japanese authors
Japanese influences
Jargon
Jewelry
Jewish authors
Jewish Christian authors
Jews
Job satisfaction
Job stress
Job vacancies
Journalism, Military
Journalists
Judging
Jungle warfare
Kidnapping, [date]
Kings and rulers
Kinship
Knock
Knowledge
Knowledge and learning
Koranic teaching
Korean authors
Kyrgyz authors
Labeling
Labiality
Labor productivity
Labor unions
Land tenure
Landscape architecture
Language
Language, style
Languages
Larvae
Laser surgery
Last years
Latin American influences
Law and legislation
Lawyers
Lead content
Leadership
Leave regulations
Leaves and furloughs
Legal research
Legal status, laws etc.
Legal status, laws, etc.
Legislative history
Lexicography
Lexicology
Lexicology, Historical
Libraries
Library
Library resources
Licenses
Life cycles
Life skills assessment
Lighting
Linear programming
Literary art
Literary style
Literary themes, motives
Literature and the war, [revolution, etc.]
Liturgical objects
Liturgical use
Liturgy
Liturgy, Experimental
Location
Locative constructions
Locks
Locomotion
Logistics
Longevity
Long-term care
Losses
Lubrication
Lubrication systems
Lutheran authors
Luxembourg authors
Lymphatics
Machinability
Machine gun drill and tactics
Machine translating
Machinery
Magnetic fields
Magnetic properties
Magnetic resonance imaging
Maintenance and repair
Majority leaders
Majority whips
Male authors
Malpractice
Management
Maneuvers
Manpower
Manure
Manuscripts
Manuscripts (Papyri)
Manuscripts, English, [Latin, Aramaic, etc.]
Maori authors
Map collections
Maratha authors
Markedness
Marketing
Marking
Markings
Marriage
Marriage customs and rites
Mascots
Mass media and the war, [revolution, etc.]
Massage
Masters-at-arms
Material culture
Materials
Materials management
Mathematical models
Mathematics
Measurement
Mechanical properties
Mechanism of action
Medals
Medals, badges, decorations, etc.
Medical care
Medical English
Medical examinations
Medical personnel
Medical supplies
Medical technologists
Medicine
Medieval civilization
Medieval influences
Mediterranean influences
Membership
Memorizing
Mennonite authors
Mental health
Mental health services
Mergers
Messes
Metabolic detoxification
Metabolism
Metallography
Metallurgy
Metamorphosis
Methodist authors
Methodology
Methylation
Metonyms
Metrics and rhythmics
Mexican influences
Microbiology
Micropropagation
Microscopy
Microstructure
Migration
Migrations
Military aspects
Military capital
Military construction operations
Military currency
Military intelligence
Military leadership
Military life
Military police
Military policy
Military relations
Militia
Milling
Mimetic words
Minangkabau influences
Minorities
Minority authors
Minority leaders
Minority whips
Misfueling
Missing in action
Missions
Mistresses
Mixing
Mnemonic devices
Mobilization
Modality
Models
Modern civilization
Modification
Moisture
Molecular aspects
Molecular diagnosis
Molecular genetics
Molecular rotation
Money
Mongolian authors
Monitoring
Monosyllables
Monuments
Mood
Moral and ethical aspects
Moral conditions
Mormon authors
Morphemics
Morphogenesis
Morphology
Morphophonemics
Morphosyntax
Mortality
Motion picture plays
Motion pictures and the war, [revolution, etc.]
Motorcycle troops
Motors
Motors (Compressed-gas)
Motors (Diesel)
Motors (Liquid nitrogen)
Motors (Two-stroke cycle)
Movements
Mufflers
Multiphonics
Muscles
Museums
Music and the war, [revolution, etc.]
Musical instrument collections
Muslim authors
Mutation
Mutation breeding
Mutual intelligibility
Mycenaean influences
Mythology
Name
Names
Nasality
National Guard
Natural history collections
Naval militia
Naval operations
Naval operations, American, [British, etc.]
Navigation
Nazi persecution
Necrosis
Needle biopsy
Needs assessment
Negatives
Nervous system
Nests
Neutralization
New words
Nitrogen content
Noise
Nominals
Non-commissioned officers
Nondestructive testing
Noun
Noun phrase
Number
Numerals
Numerical division
Numismatic collections
Numismatics
Nurses
Nursing
Nursing home care
Nutrition
Nutritional aspects
Obscene words
Obsolete words
Occupational specialties
Occupations
Occupied territories
Odor
Odor control
Officer efficiency reports
Officers
Officers' clubs
Officers on detached service
Officials and employees
Officials and employees, Alien
Officials and employees, Honorary
Officials and employees, Retired
Off-road operation
Oil filters
Old Norse influences
On postage stamps
On television
Onomatopoeic words
Open admission
Operational readiness
Operations other than war
Optical instrument repairers
Optical methods
Optical properties
Oratory
Orbit
Orchestras
Ordnance and ordnance stores
Ordnance facilities
Organization
Organizing
Organs
Orientation
Origin
Orthodox Eastern authors
Orthography and spelling
Osmotic potential
Ownership
Oxidation
Oxygen content
Packaging
Packing
Padding
Painting
Painting of vessels
Palaces
Palatalization
Palliative treatment
Palynotaxonomy
Parables
Parachute troops
Paragraphs
Parallelism
Paralysis
Paraphrase
Paraphrases, English, [French, German, etc.]
Parasites
Pardon
Parenthetical constructions
Parking
Paronyms
Parsee authors
Parsing
Participation, African American, [Indian, etc.]
Participation, Female
Participation, Foreign
Participation, German, [Irish, Swiss, etc.]
Participation, Immigrant
Participation, Jewish
Participation, Juvenile
Participle
Particles
Partitives
Parts of speech
Parturition
Party work
Passive voice
Pastoral counseling of
Pathogenesis
Pathogens
Pathophysiology
Patients
Pay, allowances, etc.
Payroll deductions
Peace
Pedaling
Pejoration
Penetration resistance
Pensions
Performance
Performances
Periodization
Permeability
Peroxidation
Persian influences
Person
Personnel management
Personnel records
Petty officers
Pharmacokinetics
Phenology
Philosophy
Phonemics
Phonetics
Phonology
Phonology, Comparative
Phonology, Historical
Photochemotherapy
Photograph collections
Photographers
Photography
Photomorphogenesis
Phototherapy
Phraseology
Phylogeny
Physical therapy
Physical training
Physiological aspects
Physiological effect
Physiological genomics
Physiological transport
Physiology
Pickling
Pistons and piston rings
Planning
Planting
Planting time
Plastic properties
Pneumatic equipment
Poetic works
Polish influences
Political activity
Political and social views
Political aspects
–Political aspects
Political-military affairs officers
Politics and government
Pollen
Pollen management
Pollution control devices
Polyglot
Polysemy
Population
Population policy
Population viability analysis
Portuguese influences
Positioning
Positions
Possessives
Postal clerks
Postal service
Poster collections
Postharvest diseases and injuries
Postharvest losses
Postharvest physiology
Postharvest technology
Postpositions
Power supply
Power trains
Power utilization
Powers and duties
Practice
Precancerous conditions
Precooling
Predators of
Pre-existence
Pregnancy
Preharvest sprouting
Prepositional phrases
Prepositions
Preservation
Presidents
Presiding officers
Press coverage
Prevention
Prices
Prisoners and prisons
Prisoners and prisons, British, [German, etc.]
Prisons
Private collections
Privileges and immunities
Prizes, etc.
Processing
Procurement
Production and direction
Production control
Production standards
Productivity
Professional ethics
Professional relationships
Prognosis
Programming
Promotions
Pronominals
Pronoun
Pronunciation
Pronunciation by foreign speakers
Propaganda
Propagation
Prophecies
Prose
Prosodic analysis
Protection
Protest movements
Protestant authors
Protestant churches
Provenance trials
Provenances
Provençal influences
Provinces
Provincialisms
Provisioning
Pruning
Psychic aspects
Psychological aspects
Psychological testing
Psychology
Psychophysiology
Psychosomatic aspects
Psychotropic effects
Public opinion
Public records
Public relations
Public services
Public welfare
Publication and distribution
Publication of proceedings
Publishing
Pump placing
Punctuation
Purchasing
Purges
Purification
Puritan authors
Quaker authors
Qualifications
Quality
Quality control
Quantifiers
Quantity
Queens
Quenching
Race identity
Race relations
Racial analysis
Radar
Radiation injuries
Radiation preservation
Radiator ornaments
Radiators
Radio and television plays
Radio broadcasting and the war, [revolutions, etc.]
Radio broadcasting of proceedings
Radio control
Radio equipment
Radio installations
Radioactive contamination
Radiography
Radioimmunoimaging
Radioimmunotherapy
Radioiodination
Radiomen
Radionuclide imaging
Radiotherapy
Rapid solidification processing
Rates
Rating of
Reactivity
Reader-response criticism
Reading
Receptors
Reconnaissance operations
Reconnaissance operations, American, [German, etc.]
Recreation
Recreational use
Recruiting
Recruiting, enlistment, etc.
Recycling
Red Cross
Reduplication
Reference
Reference books
Refining
Reflexives
Reform
Refugees
Regeneration
Regimental histories
Regional disparities
Regions
Registration and transfer
Regulation
Rehabilitation
Reimplantation
Reinstatement
Reintroduction
Relapse
Relation to Matthew, [Jeremiah, etc.]
Relation to the Old Testament
Relational grammar
Relations
Relations with [specific class of persons or ethnic group]
Relations with men
Relations with women
Relative clauses
Reliability
Relics
Religion
Religious aspects
Religious life
Religious life and customs
Relocation
Remedial teaching
Remodeling
Remote sensing
Remount service
Reoperation
Reorganization
Repairing
Reparations
Repatriation of war dead
Reporters and reporting
Reporting
Reporting to
Reproduction
Republics
Requirements
Research
Research grants
Reserve fleets
Reserves
Residence requirements
Residues
Resignation
Resignation from office
Respiration
Respiratory organs
Respite care
Resultative constructions
Retarders
Retirement
Revival
Rhetoric
Rhyme
Rhythm
Riding qualities
Riot, [date]
Riots
Ripening
Risk assessment
Risk factors
Risk management
Rites and ceremonies
Rituals
Riverine operations
Riverine operations, American, [British, etc.]
Rollover protective structures
Roman influences
Romanian influences
Roots
Rootstocks
Rowing
Rugby football
Rum ration
Rupture
Rural conditions
Russian influences
Safety appliances
Safety measures
Safety regulations
Salaries, etc.
–Salaries, etc.
Salvation Army
Sampling
Sanitary affairs
Sanitation
Sanskrit influences
Scandinavian influences
Scheduled tribes
Scholarships, fellowships, etc.
Schooling
Schools
Science
Scientific apparatus collections
Scientific applications
Scottish authors
Scottish influences
Scouts and scouting
Scrapping
Sea life
Seal
Search and rescue operations
Seasonal distribution
Seasonal variations
Seat belts
Seats
Secret service
Secretion
Secretions
Secular employment
Security measures
Seedlings
Seedlings, Bareroot
Seedlings, Container
Seeds
Selection
Selection and appointment
Selection indexes
Self-regulation
Semantics
Semantics, Historical
Seniority system
Sense organs
Sensory evaluation
Sentences
Separation
Serial numbers
Serodiagnosis
Service clubs
Service craft
Service life
Services for
Settings
Sex differences
Sex factors
Sexing
Sexual behavior
Shamanistic influences
Shelling
Shock absorbers
Shore patrol
Showing
Shrines
Side effects
Side factors
Silage
Simulation methods
Sindhi authors
Sisters
Size
Sizes
Ski troops
Skid resistance
Skidding
Slang
Slavic civilization
Slavic influences
Slide collections
Small-boat service
Snow protection and removal
Social aspects
Social conditions
Social life and customs
Social networks
Social policy
Social scientific criticism
Social services
Socialization
Societies and clubs
Societies, etc.
Sociological aspects
Socio-rhetorical criticism
Soils
Solubility
Somatic embryogenesis
Sonorants
Soundproofing
Sounds
South Asian authors
Soviet influences
Sowing
Spacing
Spanish influences
Spawning
Speakers
Speciation
Spectra
Spectral analysis
Spectroscopic imaging
Speed
Spermatozoa
Spiritual life
Spiritualistic interpretations
Spoken English
Spoken French, [Japanese, etc.]
Sports
Spray control
Springs and suspension
Stability
Staff corps
Staffs
Stage history
Stamp collections
Standardization
Standards
Starting devices
State supervision
States
Statistical methods
Statistical services
Statues
Steering-gear
Sterilization
Stewards
Storage
Storekeepers
Stranding
Strategic aspects
Stress corrosion
Structuralist criticism
Structure
Structure-activity relationships
Student housing
Student strike, [date]
Students
Study and teaching
Study and teaching (Continuing education)
Study and teaching (Early childhood)
Study and teaching (Elementary)
Study and teaching (Graduate)
Study and teaching (Higher)
Study and teaching (Internship)
Study and teaching (Middle school)
Study and teaching (Preschool)
Study and teaching (Primary)
Study and teaching (Residency)
Study and teaching (Secondary)
Style
Subcontracting
Subjectless constructions
Subjunctive
Submarine
Submarine forces
Subordinate constructions
Substance use
Substitution
Succession
Suffixes and prefixes
Suffrage
Suicidal behavior
Summering
Superchargers
Supervision
Supplementary employment
Suppletion
Supplies and stores
Supply and demand
Surfaces
Surgeons
Surgery
Susceptibility
Suspension
Swami–Narayani authors
Swimming
Switch-reference
Syllabication
Symbolic aspects
Symbolism
Symbols
Synonyms and antonyms
Syntax
Synthesis
Syphilis
Tactical aviation
Tank warfare
Taoist influences
Target practice
Taxation
Teaching office
Teachings
Technical English
Technique
Technological innovations
Technology
Teenagers' use
Television and the war, [revolution, etc.]
Television broadcasting of proceedings
Temperature
Tempo
Temporal clauses
Temporal constructions
Tennis
Tense
Term of office
Territorial expansion
Territorial questions
Territoriality
Territories and possessions
Test shooting
Testing
Texture
Theater and the war, [revolution, etc.]
Themes, motives
Theology
Theory, etc.
Therapeutic use
Thermal fatigue
Thermal properties
Thermography
Thermomechanical properties
Thermomechanical treatment
Thermotherapy
Thinning
Threshold limit values
Time management
Timing belts
Tires
Titles
Titles of books
Tobacco use
Tomb
Tombs
Tomography
Tonguing
Topic and comment
Towing
Toxicity testing
Toxicology
Track and field
Traction
Tragedies
Tragicomedies
Training
Training administrators
Training of
Transaxles
Transcription
Transfer
Transitivity
Translating
Translating into French, [German, etc.]
Translations into French, [German, etc.]
Transliteration
Transliteration into Korean, [Russian, etc.]
Transmission
Transmission devices
Transmission devices, Automatic
Transmutation
Transplantation
Transplanting
Transport of sick and wounded
Transport properties
Transport service
Transportation
Trapping
Travel
Treatment
Trench warfare
Trial practice
Trials of vessels
Tritium content
Trophies
Tropical conditions
Trypanotolerance
Tuberculosis
Tuition
Tumors
Tuning
Tunnel warfare
Turbochargers
Turkic influences
Turkish authors
Turnover
Type specimens
Ukrainian authors
Ukrainian influences
Ulcers
Ultrasonic imaging
Ultrastructure
Unclaimed benefits
Underground literature
Underground movements
Underground printing plants
Uniforms
Union territories
Unit cohesion
Unknown military personnel
Unknown military personnel, American, [British, etc.]
Upholstery
Urdu influences
Usage
Use
Use in hymns
Utilization
Vaccination
Validity
Valuation
Valves
Vapor lock
Variation
Varieties
Vegetative propagation
Venom
Verb
Verb phrase
Verbals
Versification
Versions
Versions, African, [Indic, Slavic, etc.]
Versions, Baptist
Versions, Catholic
Versions, Catholic vs. Protestant
Versions, Hussite
Versions, Jehovah's Witnesses
Vertical distribution
Vertical integration
Veterans
Veterinary service
Viability
Vibration
Violence against
Virus diseases
Viruses
Viscosity
Vitality
Vocabulary
Vocalization
Vocational guidance
Voice
Voivodeships
Volleyball
Voting
Vowel gradation
Vowel reduction
Vowels
Wage fixing
War use
War work
Warfare
Warrant officers
Wars
Waste disposal
Waste minimization
Watch duty
Water requirements
Water rights
Water-supply
Weapons systems
Weed control
Weight
Weights and measures
Weldability
Welding
Welsh authors
West Indian influences
Western civilization
Western influences
Wheels
White authors
Will
Windows and windshields
Wintering
Women
Women authors
Women's reserves
Word formation
Word frequency
Word order
Workload
Wounds and injuries
Wrestling
Writing
Written English
Written works
Yeomen
Yiddish influences
Yields
Yoruba authors
Young Men's Christian associations
Young Women's Christian associations
	</xsl:template>
	<xsl:template name="formlist">
	2-harpsichord scores
2-piano scores
3-piano scores
Abbreviations
Abbreviations of titles
Abridgments
Abstracts
Acronyms
Adaptations
Aerial photographs
Aerial views
Almanacs 
Amateurs' manuals
Anecdotes
Apologetic works
Archives
Art
Art and the war, [revolution, etc.] 
Atlases
Audio adaptations
Audiotape catalogs
Autographs
Bathymetric maps
Bibliography
Bibliography of bibliographies
Bio-bibliography
Biography
Book reviews
By-laws
Cadenzas
Calendars
Caricatures and cartoons
Case studies
Cases
Catalogs
Catalogs, Manufacturers'
Catalogs and collections
Catalogues raisonnes
Catechisms
CD-ROM catalogs
Census
Chapel exercises
Charters
Charters, grants, privileges
Charts, diagrams, etc. 
Children's sermons
Chord diagrams
Chorus scores with organ
Chorus scores with piano
Chorus scores without accompaniment
Chronology
Classification
Code numbers
Code words
Comic books, strips, etc. 
Commentaries
Commercial treaties
Compact disc catalogs
Comparative studies
Computer games
Concordances
Congresses
Constitution
Controversial literature
Conversation and phrase books
Correspondence
Creeds
Cross-cultural studies
Cross references
Curricula
Data tape catalogs
Databases
Designs and plans
Devotional literature
Diaries
Dictionaries
Dictionaries, Juvenile
Digests
Directories
Discography
Drama
Drawings
Early works to 1800
Electronic discussion groups
Encyclopedias
Encyclopedias, Juvenile
Examinations, questions, etc. 
Excerpts
Excerpts, Arranged
Exercises for dictation
Exhibitions
Facsimiles
Fake books
Fiction
Film and video adaptations
Film catalogs
Films for foreign speakers
Firing regulations
Folklore
Forms
Gazetteers
Genealogy
Gift books
Glossaries, vocabularies, etc. 
Guidebooks
Handbooks, manuals, etc. 
Harmonies
Humor
Hymns
Identification
Illustrations
Imprints
In art
Index maps
Indexes
Instructive editions
Instrumental settings
Interactive multimedia
Interlinear translations
Interviews
Introductions
Inventories
Job descriptions
Juvenile
Juvenile drama
Juvenile fiction
Juvenile films
Juvenile humor
Juvenile literature
Juvenile poetry
Juvenile software
Juvenile sound recordings
Laboratory manuals
Lead sheets
Legends
Librettos
Life skills guides
Lists of vessels
Literary collections
Literatures
Longitudinal studies
Maps
Maps, Comparative
Maps, Manuscript
Maps, Mental
Maps, Outline and base
Maps, Physical
Maps, Pictorial
Maps, Topographic
Maps, Tourist
Maps for 
Marginal readings
Meditations
Methods
Microform catalogs
Miscellanea
Music
Musical settings
Necrology
Newspapers
Nomenclature
Nomenclature (Popular) 
Nomograms
Non-commissioned officers' handbooks
Notation
Notebooks, sketchbooks, etc. 
Obituaries
Observations
Observers' manuals
Officers' handbooks
Orchestra studies
Order-books
Outlines, syllabi, etc. 
Pamphlets
Papal documents
Parallel versions, English [French, etc.] 
Paraphrases
Paraphrases, English [French, German, etc.] 
Parodies, imitations, etc. 
Parts
Parts (solo) 
Pastoral letters and charges
Patents
Pedigrees
Performance records
Periodicals
Personal narratives
Personal narratives, ... 
Petty officers' handbooks
Phonetic transcriptions
Photographs
Photographs from space
Piano scores
Piano scores (4 hands) 
Pictorial works
Picture Bibles
Platforms
Poetry
Popular works
Portraits
Posters
Prayer-books and devotions
Prayers
Prefaces
Private bills
Problems, exercises, etc. 
Programmed instruction
Quotations
Quotations, maxims, etc. 
Readers
Readers for new literates
Records and correspondence
Reference editions
Registers
Registers of dead
Regulations
Relief models
Remote-sensing images
Remote-sensing maps
Resolutions
Reverse indexes
Reviews
Romances
Rules
Rules and practice
Sacred books
Sailors' handbooks
Scenarios
Scholia
Scores
Scores and parts
Scores and parts (solo) 
Self-instruction 
Self-portraits
Sermons
Simplified editions
Slides
Software
Solo with
Solos with
Songs and music
Sound recordings for foreign speakers
Sources
Specifications
Specimens
Speeches in Congress
Spurious and doubtful works
Stage guides
Statistics
Statistics, Medical
Statistics, Vital
Stories, plots, etc. 
Studies and exercises
Study guides
Surveys
Tables
Tables of contents
Teaching pieces
Telephone directories
Telephone directories |v Yellow pages
Terminology
Terms and phrases
Textbooks
Textbooks for foreign speakers
Texts
Thematic catalogs
Tours
Trademarks
Translations
Treaties
Trials, litigation, etc. 
Union lists
Video catalogs
Video recordings for 
Vocal scores with 
Vocal scores without accompaniment
	</xsl:template>
</xsl:stylesheet>
