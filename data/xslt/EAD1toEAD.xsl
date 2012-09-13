<?xml version="1.0" encoding="UTF-8"?>
<!-- 
STYLESHEET FOR CONVERSION OF EAD 1.0 TO 2002. 
PREVIOUS VERSION dp2003-06-17T12:44
THIS VERSION     sy2003-10-15
  
-->
<xsl:transform 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  version="1.1">

	<xsl:output 
  method="xml" 
  version="1.0" 
  omit-xml-declaration="no" 
  indent="yes" 
  encoding="UTF-8" 
  doctype-public="+//ISBN 1-931666-00-8//DTD ead.dtd (Encoded Archival Description (EAD) Version 2002)//EN" 
  doctype-system="{$dtdpath}"/>
	<xsl:strip-space elements="*"/>
	<!--========================================================================-->
	<!--  USER DEFINED VARIABLES                                                -->
	<!--========================================================================-->
	<!-- All of these variables, XSLT paramaters, may also be overriden from the command line:
     see readme.txt in this distribution
-->
	<xsl:param name='countrycode'>us</xsl:param>
	<!-- Added to the <eadid> as @countrycode. Use ISO 3166-1 values.
-->
	<xsl:param name='mainagencycode'>ctY</xsl:param>
	<!-- Added to <eadid> as @mainagencycode. Use ISO 15511 values.
     This is the code of the finding aids maintainer, and may not necessaily be the same as
     the repository code.
-->
	<xsl:param name='convdate'>March 23, 2004</xsl:param>
	<!-- conversion date 
     default convdate value may be overridden from the command line
     eg., using saxon:  
     saxon -o ead-v2002.xml ead-v1.xml xsl\v1to02.xsl convdate="non-default-value" 
-->
	<xsl:param name='isoconvdate'>20040323</xsl:param>
	<!-- conversion date: USE ISO 8601 
     eg., using saxon:  
     saxon -o ead-v2002.xml ead-v1.xml xsl\v1to02.xsl convdate="non-default-value" 
-->
	<xsl:param name='docname'>conversion</xsl:param>
	<!-- default docname value may be overridden from the command line
     docname is the name of the document being converted, and is used in indentifing reports
     eg., using saxon:  
     saxon -o ead-v2002.xml ead-v1.xml v1to02.xsl docname="ead-v1.xml"
-->
	<xsl:param name='dtdpath'>./dtds/ead.dtd</xsl:param>
	<!-- path to EAD 2002 dtd. May be local or remote: 
     e.g. file:///c:/ead/dtds/ead.dtd
          http://my.server.com/dtd/ead.dtd
-->
	<xsl:param name='report'>n</xsl:param>
	<!--produce a report of the conversion: "y" or "n" 
-->
	<xsl:param name='reportpath'>
		<xsl:text>c:\ead2002conv\doc\</xsl:text>
		<xsl:value-of select='$docname'/>
		<xsl:text>.report.html</xsl:text>
	</xsl:param>
	<!--location (and name extension) the report should be written to -->
	<xsl:param name='bundle'>n</xsl:param>
	<!--replace bundle <adminfo> and <add> within their own <descgrps>s: "y" or "n"
    Stylesheet by default UNBUNDLES the children of <add> and <admininfo>, unless:
    * this parameter is set to 'y', they are bundled with <descgroup type="originalElementName">
    * <admininfo>, <add> have a <head> or other "block" level elements (address, chronlist, list, note, table, p, blockquote)
       they are bundled with <descgroup type="originalElementName">
    * <admininfo>, <add> consist _only_ of "block elements" they are bundles with <odd type="originalElementName">
-->
	<xsl:param name='langlang'>eng</xsl:param>
	<!-- Default is English (ISO639-2b 'eng'). Valid alternatives in this stylesheet version are: French(ISO639-2b 'fre') 
     determines whether replacement of @langmaterial atributes in EAD v1.0 with EAD 20002
       <langmaterial>
         <language langcode="value">value</language>
       </langmaterial>
     is written out with English or French language names.
     ISO639-2 language codes (and names in French and English are read from iso639-2.xml)
-->
	<xsl:param name='converter'>v1to02.xsl (sy2003-10-15)</xsl:param>
	<!-- name of the conversion script
     Change this value, only if you modify the THIS STYLESHEET
-->
	<!--========================================================================-->
	<!--  END USER DEFINED VARIABLES                                            -->
	<!--========================================================================-->
	<!-- create the report, calling in report.xsl-->
	<xsl:template match="/">
		<xsl:apply-templates
    select="*|@*|comment()|processing-instruction()|text()"/>
		<xsl:if test='$report="y"'>
			<xsl:document 
      method="html" 
      indent="yes" 
      encoding="UTF-8"
      doctype-public="-//W3C//DTD HTML 4.0//EN" 
      doctype-system=""
       href="{$reportpath}">
				<xsl:element name='html'>
					<xsl:element name='head'>
						<xsl:element name='style'>
							<xsl:text>body {</xsl:text>
							<xsl:text>margin-top:0.25in;margin-left:0.50in;</xsl:text>
							<xsl:text>margin-right:0.50in;margin-bottom:3.0in;</xsl:text>
							<xsl:text>font-family: century;</xsl:text>
							<xsl:text>}</xsl:text>
						</xsl:element>
					</xsl:element>
					<xsl:element name='body'>
						<xsl:element name='div'>
							<xsl:element name='h4'>
								<xsl:text>Finding aid title:</xsl:text>
								<xsl:value-of select='//titleproper'/>
							</xsl:element>
							<xsl:element name='h4'>
								<xsl:text>Unit title: </xsl:text>
								<xsl:value-of select='//archdesc/did/unittitle'/>
							</xsl:element>
							<xsl:element name='h4'>
								<xsl:text>EADID: </xsl:text>
								<xsl:value-of select='//eadid'/>
							</xsl:element>
							<xsl:if test='contains(system-property("xsl:vendor"), "SAXON")'>
								<xsl:element name='h4'>
									<xsl:text>Systemid: </xsl:text>
									<xsl:value-of select='saxon:system-id()'/>
								</xsl:element>
							</xsl:if>
							<xsl:element name='h4'>
								<xsl:text>Converted </xsl:text>
								<xsl:value-of select='$convdate'/>
								<xsl:text> using XSLT: </xsl:text>
								<xsl:value-of select='$converter'/>
							</xsl:element>
						</xsl:element>
						<xsl:element name='ol'>
							<xsl:apply-templates mode='change'
            select="*|@*"/>
						</xsl:element>
					</xsl:element>
				</xsl:element>
			</xsl:document>
		</xsl:if>
	</xsl:template>
	<xsl:template match='@*'>
		<xsl:choose>
			<xsl:when test='normalize-space(.)= ""'/>
			<xsl:otherwise>
				<xsl:attribute name='{name(.)}'>
					<xsl:value-of select='normalize-space(.)'/>        

				</xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template 
  match="*|comment()|processing-instruction()|text()">
		<xsl:copy>
			<xsl:apply-templates
     select="*|@*|comment()|processing-instruction()|text()"/>
		</xsl:copy>
	</xsl:template>
	<!-- match eadhead, copy -->
	<xsl:template match='eadheader'>
		<xsl:copy>
			<!-- eadid @ processing -->
			<xsl:for-each select='@*'>
				<xsl:choose>
					<xsl:when test='name()="langencoding"'>
						<xsl:attribute name='langencoding'>
							<xsl:choose>
								<xsl:when test='contains(normalize-space(.), "639-2")'>
									<xsl:text>iso639-2b</xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:value-of select='translate(normalize-space(.), " ", "_")'/>
								</xsl:otherwise>
							</xsl:choose>          

						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise>
						<xsl:copy/>
					</xsl:otherwise>
				</xsl:choose>    

			</xsl:for-each>
			<xsl:apply-templates
     select="eadid|filedesc|profiledesc|comment()|processing-instruction()|text()"/>
			<xsl:choose>
				<xsl:when test='revisiondesc'>
					<xsl:for-each select='revisiondesc'>
						<xsl:element name='revisiondesc'>
							<xsl:apply-templates select="@*"/>
							<xsl:choose>
								<xsl:when test='change'>
									<xsl:for-each select='change'>
										<xsl:copy>
											<xsl:apply-templates
                   select="*|@*|comment()|processing-instruction()|text()"/>
										</xsl:copy>
										<!-- conversion statement -->
										<xsl:element name='change'>
											<xsl:element name='date'>
												<xsl:if test='$isoconvdate'>
													<xsl:attribute name='normal'>
														<xsl:value-of select='$isoconvdate'/>
													</xsl:attribute>
												</xsl:if>
												<xsl:value-of select='$convdate'/>
											</xsl:element>
											<xsl:element name='item'>
												<xsl:value-of select='//eadid'/>
												<xsl:text> converted from EAD 1.0 to 2002 by </xsl:text>
												<xsl:value-of select='$converter'/>
												<xsl:text>.</xsl:text>
											</xsl:element>
										</xsl:element>
									</xsl:for-each>
								</xsl:when>
								<xsl:otherwise>
									<xsl:for-each select='list'>
										<xsl:element name='list'>
											<xsl:apply-templates select="@*"/>
											<xsl:apply-templates select="head | listhead"/>
											<xsl:for-each select='item | defitem'>
												<xsl:copy>
													<xsl:apply-templates
                       select="*|@*|comment()|processing-instruction()|text()"/>
												</xsl:copy>
											</xsl:for-each>
											<!-- conversion statement -->
											<xsl:choose>
												<xsl:when test='item'>
													<xsl:element name='item'>
														<xsl:element name='date'>
															<xsl:if test='$isoconvdate'>
																<xsl:attribute name='normal'>
																	<xsl:value-of select='$isoconvdate'/>
																</xsl:attribute>
															</xsl:if>
															<xsl:value-of select='$convdate'/>
														</xsl:element>
														<xsl:text> </xsl:text>
														<xsl:value-of select='//eadid'/>
														<xsl:text> converted from EAD 1.0 to 2002 by </xsl:text>
														<xsl:value-of select='$converter'/>
														<xsl:text>.</xsl:text>
													</xsl:element>                    

												</xsl:when>
												<xsl:otherwise>
													<xsl:element name='defitem'>
														<xsl:element name='label'>
															<xsl:element name='date'>
																<xsl:if test='$isoconvdate'>
																	<xsl:attribute name='normal'>
																		<xsl:value-of select='$isoconvdate'/>
																	</xsl:attribute>
																</xsl:if>
																<xsl:value-of select='$convdate'/>
															</xsl:element>
														</xsl:element>
													</xsl:element>
													<xsl:element name='item'>
														<xsl:value-of select='//eadid'/>
														<xsl:text> converted from EAD 1.0 to 2002 by </xsl:text>
														<xsl:value-of select='$converter'/>
														<xsl:text>.</xsl:text>    

													</xsl:element>                        

												</xsl:otherwise>
											</xsl:choose>
										</xsl:element>
									</xsl:for-each>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:element>
					</xsl:for-each>        

				</xsl:when>
				<xsl:otherwise>
					<!-- apply-templates for change report -->
					<xsl:element name='revisiondesc'>
						<!-- conversion statement -->
						<xsl:element name='change'>
							<xsl:element name='date'>
								<xsl:if test='$isoconvdate'>
									<xsl:attribute name='normal'>
										<xsl:value-of select='$isoconvdate'/>
									</xsl:attribute>
								</xsl:if>
								<xsl:value-of select='$convdate'/>
							</xsl:element>
							<xsl:element name='item'>
								<xsl:value-of select='//eadid'/>
								<xsl:text> converted from EAD 1.0 to 2002 by </xsl:text>
								<xsl:value-of select='$converter'/>
								<xsl:text>.</xsl:text>    

							</xsl:element>
						</xsl:element>
					</xsl:element>
				</xsl:otherwise>
			</xsl:choose>  

		</xsl:copy>
	</xsl:template>
	<!-- attribute removal -->
	<xsl:template match='@langmaterial | 
                     @legalstatus  |
                     @otherlegalstatus | 
                     @pubstatus[parent::title or parent::titleproper] |
                     @extent[parent::title or parent::titleproper] 
'/>
	<!--unitdate/@type-->
	<xsl:template match='@type[parent::unitdate]'>
		<xsl:choose>
			<xsl:when test='.="bulk" or .="inclusive"'>
				<xsl:attribute name='type'>
					<xsl:value-of select='.'/>
				</xsl:attribute>        

			</xsl:when>
			<xsl:otherwise>
				<xsl:attribute name='datechar'>
					<xsl:text>single</xsl:text>
				</xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- render attributes -->
	<xsl:template match='@render'>
		<xsl:attribute name='render'>
			<xsl:choose>
				<xsl:when test='. = "boldquoted"'>
					<xsl:text>bolddoublequote</xsl:text>
				</xsl:when>
				<xsl:when test='. = "quoted"'>
					<xsl:text>doublequote</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select='.'/>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:attribute>
	</xsl:template>
	<!-- end render attributes -->
	<!-- "other" attributes -->
	<xsl:template match='@otherlevel[parent::*]'>
		<xsl:choose>
			<xsl:when test='not(parent::*/@level)'>
				<xsl:attribute name='level'>
					<xsl:text>otherlevel</xsl:text>
				</xsl:attribute>
				<xsl:choose>
					<xsl:when test='normalize-space(.)= ""'/>
					<xsl:otherwise>
						<xsl:attribute name='otherlevel'>
							<xsl:value-of select='normalize-space(.)'/>        

						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:when test='parent::*/@level != "otherlevel"'/>
			<!--If @level has a legal value, then remove @otherlevel
as both are not logically possible. -->
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test='normalize-space(.)= ""'/>
					<xsl:otherwise>
						<xsl:attribute name='otherlevel'>
							<xsl:value-of select='normalize-space(.)'/>        

						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match='@othersource'>
		<xsl:choose>
			<xsl:when test='parent::*/@source != "othersource"'/>
			<xsl:otherwise>
				<xsl:attribute name='source'>
					<xsl:choose>
						<xsl:when test='contains(normalize-space(.), " ")'>
							<xsl:value-of select='translate(normalize-space(.), " ", "_")'/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select='normalize-space(.)'/>
						</xsl:otherwise>
					</xsl:choose>          

				</xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match='@othertype[parent::container or parent::archdesc]'>
		<xsl:choose>
			<xsl:when test='parent::*/@type != "othertype"'/>
			<xsl:otherwise>
				<xsl:attribute name='type'>
					<xsl:choose>
						<xsl:when test='contains(normalize-space(.), " ")'>
							<xsl:value-of select='translate(normalize-space(.), " ", "_")'/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:value-of select='normalize-space(.)'/>
						</xsl:otherwise>
					</xsl:choose>          

				</xsl:attribute>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- make types initial uppercase to possibly create labels-->
	<xsl:template match="@type[parent::container or parent::archdesc]">
		<xsl:attribute name="type">
			<xsl:value-of select="concat(translate(substring(.,1,1),'abcdefghijklmnopqrstuvwxyz','ABCDEFGHIJKLMNOPQRSTUVWXYZ'), substring(.,2))"/>
		</xsl:attribute>
	</xsl:template>
	<xsl:template match='@othertype[parent::dsc]'>
		<xsl:attribute name='othertype'>
			<xsl:choose>
				<xsl:when test='contains(normalize-space(.), " ")'>
					<xsl:value-of select='translate(normalize-space(.), " ", "_")'/>
				</xsl:when>
				<xsl:otherwise>
					<xsl:value-of select='normalize-space(.)'/>
				</xsl:otherwise>
			</xsl:choose>  

		</xsl:attribute>
	</xsl:template>
	<xsl:template match='@countrycode'>
		<xsl:attribute name='countrycode'>
			<xsl:value-of select='translate(normalize-space(.), " .-:_", "")'/>
		</xsl:attribute>
	</xsl:template>
	<xsl:template match='@repositorycode'>
		<xsl:attribute name='repositorycode'>
			<xsl:value-of select='translate(normalize-space(.), " .-:_", "")'/>
		</xsl:attribute>
	</xsl:template>
	<xsl:template match='eadid'>
		<xsl:copy>
			<xsl:apply-templates select='@encodinganalog'/>
			<xsl:choose>
				<xsl:when test='translate(starts-with(@type,"SGML"),"sgml","SGML")'>
					<xsl:attribute name="publicid">
						<xsl:value-of select="." />
					</xsl:attribute>
				</xsl:when>
				<xsl:otherwise>
					<xsl:if test='@type'>
						<xsl:text>Type=</xsl:text>
						<xsl:value-of select='@type'/>
						<xsl:text> </xsl:text>
					</xsl:if>
				</xsl:otherwise>
			</xsl:choose>
			<xsl:if test='$countrycode'>
				<xsl:attribute name='countrycode'>
					<xsl:value-of select='$countrycode'/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test='$mainagencycode'>
				<xsl:attribute name='mainagencycode'>
					<xsl:value-of select='$mainagencycode'/>
				</xsl:attribute>
			</xsl:if>
			<xsl:if test='@source'>
				<xsl:text>Source=</xsl:text>
				<xsl:value-of select='@source'/>
				<xsl:text> </xsl:text>
			</xsl:if>
			<xsl:if test='@systemid'>
				<xsl:text>System ID=</xsl:text>
				<xsl:value-of select='@systemid'/>
				<xsl:text> </xsl:text>
			</xsl:if>
			<xsl:apply-templates
     select="*|comment()|processing-instruction()|text()"/>
		</xsl:copy>
	</xsl:template>
	<!-- did, with @legalstatus embedded -->
	<xsl:template match='did'>
		<xsl:variable name='langlabel'>
			<xsl:choose>
				<xsl:when test='$langlang = "eng"'>
					<xsl:text>Language</xsl:text>
				</xsl:when>
				<xsl:otherwise>
					<xsl:text>Langue</xsl:text>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:variable>
		<xsl:variable name='langattr' select='parent::*/@langmaterial'/>
		<!-- first copy did in as is -->
		<xsl:copy>
			<xsl:apply-templates
       select="*|@*|comment()|processing-instruction()|text()"/>
			<xsl:choose>
				<xsl:when test='parent::*/head and not(*)'>
					<xsl:element name='unittitle'>
						<xsl:value-of select='parent::*/head'/>
					</xsl:element>
				</xsl:when>
				<xsl:when test='not(parent::*/head) and not(*)'>
					<xsl:element name='unittitle'>
						<xsl:text>[unittitle added: see error report]</xsl:text>
					</xsl:element>
				</xsl:when>
				<xsl:when test='not(*[not(self::head)])'>
					<xsl:element name='unittitle'>
						<xsl:value-of select='head'/>
					</xsl:element>
				</xsl:when>
				<xsl:otherwise/>
			</xsl:choose>


			<!-- if parent has @langmaterial, then create langmaterial element -->
			<xsl:if test='parent::*[@langmaterial]'>
				<xsl:element name='langmaterial'>
					<xsl:if test='parent::archdesc'>
						<xsl:attribute name='label'>
							<xsl:value-of select='$langlabel'/>
							<xsl:if test='string-length(normalize-space($langattr)) &gt; 3'>
								<xsl:text>s</xsl:text>
							</xsl:if>
						</xsl:attribute>
					</xsl:if>
					<xsl:call-template name='extractlang'>
						<xsl:with-param name='length' select='string-length(normalize-space($langattr))'/>
						<xsl:with-param name='langlist' select='$langattr'/>
					</xsl:call-template>
				</xsl:element>
			</xsl:if>
		</xsl:copy>
		<!-- after the did, add accessrestrict and legalstatus elements if
       parent has @legalstatus -->
		<xsl:if test='parent::*/@legalstatus or parent::*/@otherlegalstatus'>
			<xsl:element name='accessrestrict'>
				<xsl:if test='parent::archdesc'>
					<xsl:element name='head'>
						<xsl:text>Legal Status</xsl:text>
					</xsl:element>
				</xsl:if>
				<xsl:element name='legalstatus'>
					<xsl:choose>
						<xsl:when test='parent::*/@legalstatus = "public"'>
							<xsl:attribute name='type'>
								<xsl:value-of select='parent::*/@legalstatus'/>
							</xsl:attribute>
							<xsl:text>Public</xsl:text>
						</xsl:when>
						<xsl:when test='parent::*/@legalstatus = "private"'>
							<xsl:attribute name='type'>
								<xsl:value-of select='parent::*/@legalstatus'/>
							</xsl:attribute>
							<xsl:text>Private</xsl:text>
						</xsl:when>
						<xsl:when test='parent::*/@otherlegalstatus'>
							<xsl:attribute name='type'>
								<xsl:value-of select='translate(normalize-space(parent::*/@otherlegalstatus), " ", "_")'/>
							</xsl:attribute>
							<xsl:value-of select='parent::*/@otherlegalstatus'/>
						</xsl:when>
						<xsl:otherwise/>
					</xsl:choose>
				</xsl:element>
			</xsl:element>
		</xsl:if>
	</xsl:template>
	<!-- the following key uses langlist.xml, an xml version of the ISO 639-2 
     codelist as the source  -->
	<xsl:key name='langkey' match='langpair' use='@code'/>
	<!-- recursive template that processes the @langmaterial attribute values.
    each value token (or code) is matched against the keys in langkey. when
    a match is found, a language element is created. punctuation added
    relative to position in list. codes not found reported in langmaterial
    element -->
	<xsl:template name='extractlang'>
		<xsl:param name="langlist"/>
		<xsl:param name='length'/>
		<xsl:variable name='worklist' select='concat(normalize-space($langlist), " ")'/>
		<xsl:choose>
			<xsl:when test='$worklist != " "'>
				<xsl:variable name='first' select='substring-before($worklist, " ")'/>
				<xsl:variable name='remainder' select='substring-after($worklist, " ")'/>
				<xsl:for-each select='document("iso639-2.xml")'>
					<xsl:element name='language'>
						<xsl:variable name='v' select='key("langkey", $first)'/>
						<xsl:choose>
							<xsl:when test='$v !=""'>
								<xsl:attribute name='langcode'>
									<xsl:value-of select='$first'/>
								</xsl:attribute>
							</xsl:when>
							<xsl:otherwise/>
						</xsl:choose>
						<xsl:if test='($remainder = "") and ($length &gt; 3)'>
							<xsl:choose>
								<xsl:when test='$langlang = "eng"'>
									<xsl:text>and </xsl:text>
								</xsl:when>
								<xsl:otherwise>
									<xsl:text>e </xsl:text>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:if>
						<xsl:choose>
							<xsl:when test='$v !=""'>
								<xsl:choose>
									<xsl:when test='$langlang = "eng"'>
										<xsl:value-of select='$v/langeng'/>
									</xsl:when>
									<xsl:otherwise>
										<xsl:value-of select='$v/langfre'/>
									</xsl:otherwise>
								</xsl:choose>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>[code "</xsl:text>
								<xsl:value-of select='$first'/>
								<xsl:text>" not found in ISO 639-2 list]</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
						<xsl:choose>
							<xsl:when test='$remainder != ""'>
								<xsl:text>, </xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>.</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:element>    

				</xsl:for-each>
				<xsl:call-template name='extractlang'>
					<xsl:with-param name='length' select='$length'/>
					<xsl:with-param name='langlist' select='$remainder'/>
				</xsl:call-template>      

			</xsl:when>
			<xsl:otherwise/>
		</xsl:choose>
	</xsl:template>
	<xsl:template match='ref | ptr | dao | extptr | extref | title | archref | bibref | note'>
		<xsl:element name='{name()}'>
			<xsl:if test='@actuate'>
				<xsl:choose>
					<xsl:when test='@actuate = "auto"'>
						<xsl:attribute name='actuate'>
							<xsl:text>onload</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:when test='@actuate= "user"'>
						<xsl:attribute name='actuate'>
							<xsl:text>onrequest</xsl:text>
						</xsl:attribute>
					</xsl:when>
					<xsl:otherwise/>
				</xsl:choose>
			</xsl:if>
			<xsl:for-each select='@*'>
				<xsl:choose>
					<xsl:when test='name()="actuate" or
                        name()="behavior" or
                        name()="inline" or 
                        name()="content-role" or 
                        name()="content-title"'/>
					<xsl:otherwise>
						<xsl:apply-templates select='.'/>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:for-each>
			<xsl:apply-templates
       select="*|comment()|processing-instruction()|text()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match='daogrp | linkgrp'>
		<xsl:element name='{name()}'>
			<xsl:for-each select='@altrender | @audience | @id | @role'>
				<xsl:copy/>
			</xsl:for-each>
			<xsl:element name='resource'>
				<xsl:attribute name='label'>
					<xsl:text>start</xsl:text>
				</xsl:attribute>
			</xsl:element>
			<xsl:for-each select='daoloc | extptrloc | extrefloc'>
				<xsl:copy> 
					<!-- daoloc -->
					<xsl:for-each select='@altrender | @audience | @entityref |
                          @href | @id | @role | @title | 
                          @xpointer'>
						<xsl:copy/> 
						<!-- each attribute -->
					</xsl:for-each>  
					<xsl:attribute name='label'>
						<xsl:text>resource-</xsl:text>
						<xsl:value-of select='position()'/>
					</xsl:attribute>
					<xsl:apply-templates select='*'/>
				</xsl:copy> 
				<!-- daoloc -->
				<xsl:element name='arc'>
					<xsl:if test='@show'>
						<xsl:copy-of select='@show'/>
					</xsl:if>
					<xsl:attribute name='from'>
						<xsl:text>start</xsl:text>
					</xsl:attribute>
					<xsl:attribute name='to'>
						<xsl:text>resource-</xsl:text>
						<xsl:value-of select='position()'/>
					</xsl:attribute>
				</xsl:element>
			</xsl:for-each>
		</xsl:element>
	</xsl:template>
	<xsl:template match='organization'>
		<xsl:element name='arrangement'>    
			<xsl:apply-templates
     select="*|@*|comment()|processing-instruction()|text()"/>
		</xsl:element>
	</xsl:template>
	<xsl:template match='admininfo'>
		<xsl:call-template name='unbundle'>
			<xsl:with-param name='localname'>admininfo</xsl:with-param>
			<xsl:with-param name='descs' select='accessrestrict | acqinfo |
                       altformavail | appraisal |
                       custodhist | prefercite |
                       processinfo | userestrict |
                       accruals | admininfo'/>
		</xsl:call-template>
	</xsl:template>
	<xsl:template match='add'>
		<xsl:call-template name='unbundle'>
			<xsl:with-param name='localname'>add</xsl:with-param>
			<xsl:with-param name='descs' select='bibliography | fileplan | 
                    index | relatedmaterial | separatedmaterial | 
                    add | otherfindaid'/>
		</xsl:call-template>
	</xsl:template>
	<xsl:template name='unbundle'>
		<!-- The following two variables used for counting the number
       of "unbundled" desc and block elements that are children
       of admininfo or add, respectively $descs and $blocks 
  -->
		<xsl:param name='localname'/>
		<xsl:param name='descs'/>
		<xsl:variable name='blocks' select='address | chronlist | list |
                       note | table | p | blockquote'/>
		<xsl:choose>
			<!-- (count($blocks) = 0) and (count($descs) = 0) invalid; no
         test necessary
    -->
			<xsl:when test='(count($blocks) &gt; 0) and (count($descs) = 0)'>
				<xsl:element name='odd'>
					<xsl:if test='not(@type)'>
						<xsl:attribute name='type'>
							<xsl:value-of select='$localname'/>
						</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates
             select="*|@*|comment()|processing-instruction()|text()"/>
				</xsl:element>
			</xsl:when>
			<!-- no blocks .: unbundle
    -->
			<xsl:when test='(count($blocks) = 0) and (count($descs) = 1)'>
				<xsl:choose>
					<xsl:when test='head and */head'>
						<xsl:apply-templates
              select="*[not(self::head)]|comment()|processing-instruction()|text()"/>          

					</xsl:when>
					<xsl:when test='head and not(*/head)'>
						<xsl:for-each select='$descs'>
							<xsl:choose>
								<xsl:when test='name()=$localname'>
									<xsl:apply-templates
                     select="*|@*|comment()|processing-instruction()|text()"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy>
										<xsl:apply-templates select='@*'/>
										<xsl:element name='head'>
											<xsl:value-of select='parent::*/head'/>
										</xsl:element>
										<xsl:apply-templates
                     select="*|comment()|processing-instruction()|text()"/>                      

									</xsl:copy>          

								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</xsl:when>
					<xsl:otherwise>
						<xsl:for-each select='$descs'>
							<xsl:choose>
								<xsl:when test='name()=$localname'>
									<xsl:apply-templates
                     select="*|comment()|processing-instruction()|text()"/>
								</xsl:when>
								<xsl:otherwise>
									<xsl:copy>
										<xsl:apply-templates
                   select="*|@*|comment()|processing-instruction()|text()"/>                      

									</xsl:copy>          

								</xsl:otherwise>
							</xsl:choose>
						</xsl:for-each>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<!-- the basic unbundle-->
			<xsl:when test='not(head) and (count($blocks) = 0) and (count($descs) &gt; 1) and $bundle="n"'>
				<xsl:apply-templates
           select="*|comment()|processing-instruction()"/>
			</xsl:when>
			<xsl:otherwise>
				<!-- Covers: head, blocks = 0 and descs > 1 
                   blocks > 0 and descs > 0  -->
				<xsl:element name='descgrp'>
					<xsl:if test='not(@type)'>
						<xsl:attribute name='type'>
							<xsl:value-of select='$localname'/>
						</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates
           select="*|@*|comment()|processing-instruction()|text()"/>
				</xsl:element>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<xsl:template match='table'>
		<xsl:element name='table'>
			<xsl:for-each select='@*'>
				<xsl:choose>
					<xsl:when test='name()="orient" or 
                        name()="shortentry" or 
                        name()="tabstyle" or
                        name()="tocentry"'/>
					<xsl:otherwise>
						<xsl:attribute name='{name()}'>
							<xsl:value-of select='.'/>
						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>      

			</xsl:for-each>

			<xsl:apply-templates
       select="head|comment()|processing-instruction()"/>

			<xsl:for-each select='tgroup'>
				<xsl:element name='tgroup'>
					<xsl:for-each select='@*'>
						<xsl:choose>
							<xsl:when test='name()="char" or 
                            name()="charoff" or 
                            name()="tgroupstyle"'/>
							<xsl:otherwise>
								<xsl:attribute name='{name()}'>
									<xsl:value-of select='.'/>
								</xsl:attribute>
							</xsl:otherwise>
						</xsl:choose>      

					</xsl:for-each>
					<!-- this is not quite right, as comments and pi's can occur betwixt -->
					<xsl:apply-templates
           select="comment()|processing-instruction()"/>
					<!-- colspec -->
					<xsl:for-each select='colspec'>
						<xsl:element name='colspec'>
							<xsl:apply-templates select='@*'/>
						</xsl:element>
					</xsl:for-each>
					<!-- do nothing with spanspec -->


					<!-- thead -->
					<xsl:for-each select='thead'>
						<xsl:element name='thead'>
							<xsl:apply-templates select='@*'/>
							<xsl:for-each select='row'>
								<xsl:element name='row'>
									<xsl:apply-templates
                       select="*|@*|comment()|processing-instruction()"/>
								</xsl:element>
							</xsl:for-each>
						</xsl:element>  
						<!-- thead -->      

					</xsl:for-each>
					<!-- tbody -->
					<xsl:for-each select='tbody'>
						<xsl:element name='tbody'>
							<xsl:apply-templates select='@*'/>
							<xsl:apply-templates
               select="comment()|processing-instruction()"/>
							<xsl:for-each select='row'>
								<xsl:element name='row'>
									<xsl:apply-templates
                   select="*|@*|comment()|processing-instruction()"/>
								</xsl:element>
							</xsl:for-each>
							<xsl:if test='preceding-sibling::tfoot'>
								<xsl:for-each select='preceding-sibling::tfoot'>
									<xsl:for-each select='row'>
										<xsl:element name='row'>
											<xsl:attribute name='altrender'>
												<xsl:text>tfoot</xsl:text>
											</xsl:attribute>
											<xsl:apply-templates
                       select="*|@*|comment()|processing-instruction()"/>
										</xsl:element>
									</xsl:for-each>
								</xsl:for-each> 
								<!-- preceding-sibling::tfoot -->
							</xsl:if>        

						</xsl:element>  
						<!-- tbody -->
					</xsl:for-each>
				</xsl:element> 
				<!-- tgroup -->
			</xsl:for-each>
		</xsl:element> 
		<!-- table -->
	</xsl:template>
	<xsl:template match='entry'>
		<xsl:element name='entry'>
			<xsl:for-each select='@*'>
				<xsl:choose>
					<xsl:when test='name()="rotate" or 
                         name()="spanname"'/>
					<xsl:otherwise>
						<xsl:attribute name='{name()}'>
							<xsl:value-of select='.'/>
						</xsl:attribute>
					</xsl:otherwise>
				</xsl:choose>      

			</xsl:for-each>
			<xsl:apply-templates 
      select="*|comment()|processing-instruction()|text()"/>  

		</xsl:element>
	</xsl:template>
	<xsl:template match='drow'>
		<xsl:for-each select="dentry">
			<xsl:apply-templates 
      select="*|comment()|processing-instruction()|text()"/>
		</xsl:for-each>
	</xsl:template>
	<xsl:template match='c[drow] |
         c01[drow] |
         c02[drow] |
         c03[drow] |
         c04[drow] |
         c05[drow] |
         c06[drow] |
         c07[drow] |
         c08[drow] |
         c09[drow] |
         c10[drow] |
         c11[drow] |
         c12[drow]'>
		<xsl:variable name='langattr' select='@langmaterial'/>
		<!-- first copy c#  -->
		<xsl:copy>
			<xsl:apply-templates select='@*'/>
			<!-- then make a did -->

			<did>
				<xsl:for-each select="drow/dentry">
					<xsl:apply-templates select='abstract |container | dao | daogrp | langmaterial | materialspec |
                       note | origination | physdesc | physloc | repository | unitdate |
                                 unitid | unittitle'/>
				</xsl:for-each>
				<!-- if parent has @langmaterial, then create langmaterial
				element -->
				<xsl:if test='@langmaterial'>
					<xsl:element name='langmaterial'>
						<xsl:if test='parent::archdesc'>
							<xsl:attribute name='label'>
								<xsl:value-of select='$langlabel'/>
								<xsl:if test='string-length(normalize-space($langattr))&gt; 3'>
									<xsl:text>s</xsl:text>
								</xsl:if>
							</xsl:attribute>
						</xsl:if>
						<xsl:call-template name='extractlang'>
							<xsl:with-param name='length' select='string-length(normalize-space($langattr))'/>
							<xsl:with-param name='langlist' select='$langattr'/>
						</xsl:call-template>
					</xsl:element>
				</xsl:if>
			</did>
			<!-- after the did, add accessrestrict and legalstatus elements if
            parent has @legalstatus -->
			<xsl:if test='@legalstatus or @otherlegalstatus'>
				<xsl:element name='accessrestrict'>
					<xsl:if test='parent::archdesc'>
						<xsl:element name='head'>
							<xsl:text>Legal Status</xsl:text>
						</xsl:element>
					</xsl:if>
					<xsl:element name='legalstatus'>
						<xsl:choose>
							<xsl:when test='@legalstatus = "public"'>
								<xsl:attribute name='type'>
									<xsl:value-of select='@legalstatus'/>
								</xsl:attribute>
								<xsl:text>Public</xsl:text>
							</xsl:when>
							<xsl:when test='@legalstatus = "private"'>
								<xsl:attribute name='type'>
									<xsl:value-of select='@legalstatus'/>
								</xsl:attribute>
								<xsl:text>Private</xsl:text>
							</xsl:when>
							<xsl:when test='@otherlegalstatus'>
								<xsl:attribute name='type'>
									<xsl:value-of select='translate(normalize-space(@otherlegalstatus), " ", "_")'/>
								</xsl:attribute>
								<xsl:value-of select='@otherlegalstatus'/>
							</xsl:when>
							<xsl:otherwise/>
						</xsl:choose>
					</xsl:element>
				</xsl:element>
			</xsl:if>
			<!-- now toss in after the did the non-did-level elements -->
			<xsl:for-each select="drow/dentry">
				<xsl:apply-templates select='accessrestrict |
				accruals |
    			acqinfo | altformavail | appraisal | arrangement |
				bibliography | bioghist | controlaccess | custodhist | descgrp |
				fileplan | index | odd | originalsloc | otherfindaid | phystech |
				prefercite | processinfo | relatedmaterial | scopecontent |
				separatedmaterial | userestrict | admininfo | add | organization'/>
			</xsl:for-each>
		</xsl:copy>
	</xsl:template>
	<xsl:template match='tspec' />
	<xsl:template match='bibliography'>
		<xsl:element name='{name()}'>
			<xsl:for-each select='@*'>
				<xsl:if test='name()="numbered"'>
					<xsl:attribute name='altrender'>
						<xsl:choose>
							<xsl:when test='.="yes"'>
								<xsl:text>numbered</xsl:text>
							</xsl:when>
							<xsl:otherwise>
								<xsl:text>unumbered</xsl:text>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:attribute>
				</xsl:if>
			</xsl:for-each>
			<xsl:apply-templates
       select="*|comment()|processing-instruction()|text()"/>
		</xsl:element>
	</xsl:template>
</xsl:transform>
