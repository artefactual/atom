<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:marc="http://www.loc.gov/MARC21/slim"  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="marc">
    <xsl:import href="MARC21slimUtils.xsl"/>
    <xsl:output method="xml" encoding="UTF-8" indent="yes"/>



    <xsl:template match="/metadata">
        <marc:collection xmlns:marc="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
        <marc:record>
            <marc:leader><xsl:text>00000nem  2200000Ia 4500</xsl:text></marc:leader>
            <marc:controlfield tag="008"><xsl:text>040801u9999\\\\xx\\\\\\\\\u\\\\\\\\und\d</xsl:text></marc:controlfield>

			<!--034-->
            <marc:datafield tag="034" ind1="1" ind2=" ">
				<xsl:choose>
					<xsl:when test="idinfo/spdom/bounding/westbc != ''">
						<!--If the bounding coordinates are present, then all bounding coordinates should
							be present and the s and t subfields would not be needed.  If the bounding coordinates 
							are not present, then s and t would be valid -->
						<marc:subfield code="d"><xsl:value-of select="idinfo/spdom/bounding/westbc" /></marc:subfield>
						<marc:subfield code="e"><xsl:value-of select="idinfo/spdom/bounding/eastbc" /></marc:subfield>
						<marc:subfield code="f"><xsl:value-of select="idinfo/spdom/bounding/northbc" /></marc:subfield>
						<marc:subfield code="g"><xsl:value-of select="idinfo/spdom/bounding/southbc" /></marc:subfield>
					</xsl:when>
					<xsl:otherwise>
						<marc:subfield code="s"><xsl:value-of select="idinfo/spdom/dsgpoly/dsgpolyo/grngpoin/gringlat" /></marc:subfield>
						<marc:subfield code="t"><xsl:value-of select="idinfo/spdom/dsgpoly/dsgpolyo/grngpoin/gringlat" /></marc:subfield>
					</xsl:otherwise>
				</xsl:choose>
            </marc:datafield>
            
            <!--037 -->
            <!-- I probably ought to, but I'm not mapping this data element.  The main reason is: 
                 1) Too many many to one matches.  There are 7 elements that map to 037g and 7 elements
                    that map to 037n
                 2) I'm not sure if MARC records need so much aquisitions information.  
                 To that end, I'm limiting the 037 mapping to the following:
                 1) 037c: fees
                 2) 037n: ordering
                 3) 037g: formname: form version
                 4) 037h: form specification
             -->
             
             <xsl:if test="distinfo/stdorder/fees !='' 
						   or distinfo/stdorder/ordering !='' or distinfo/stdorder/digform/digtinfo/formname != ''
						   or distinfo/stdorder/digform/digtinfo/formspec != ''">
				<marc:datafield tag="037" ind1=" " ind2=" ">
					<xsl:if test="distinfo/stdorder/fees !=''">
						<marc:subfield code="c"><xsl:value-of select="normalize-space(distinfo/stdorder/fees/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="distinfo/stdorder/digform/digtinfo/formname != ''">
						<marc:subfield code="g"><xsl:value-of select="normalize-space(distinfo/stdorder/digform/digtinfo/formname/.)" />, <xsl:value-of select="normalize-space(distinfo/stdorder/digform/digtinfo/formvern/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="distinfo/stdorder/digform/digtinfo/formspec != ''">
						<marc:subfield code="h"><xsl:value-of select="normalize-space(distinfo/stdorder/digform/digtinfo/formspec/.)" /></marc:subfield>
					</xsl:if>
				
					<xsl:if test="distinfo/stdorder/ordering != ''">
						<marc:subfield code="n"><xsl:value-of select="normalize-space(distinfo/stdorder/ordering/.)" /></marc:subfield>
					</xsl:if>
				</marc:datafield>			   
			</xsl:if>
            
            
            <!--045 -->
            <!--First we check for a range of dates.  If a range isn't present, then we check 
                the generic element -->
            <xsl:choose>
				<xsl:when test="distinfo/availabl/timeinfo/sngdate"> 
					<!--This will represent a single date -->
					<marc:datafield tag="045" ind1="0" ind2=" ">
						<marc:subfield code="b">d<xsl:value-of select="distinfo/availabl/timeinfo/sngdate/caldate" /></marc:subfield>
					</marc:datafield>
				</xsl:when>
				<xsl:when test="distinfo/availabl/timeinfo/rngdates">
					<!--This will represent a range of dates -->
					<marc:datafield tag="045" ind1="1" ind2=" ">
						<marc:subfield code="b">d<xsl:value-of select="distinfo/availabl/timeinfo/rngdates/begdate" /></marc:subfield>
						<marc:subfield code="b">d<xsl:value-of select="distinfo/availabl/timeinfo/rngdates/enddate" /></marc:subfield>
					</marc:datafield>
				</xsl:when>
				<xsl:otherwise />
			</xsl:choose>
			
			<!-- 100 -->
			<!-- since the fgdc origin element doesn't differente between organizations or individuals, 
			     I'm always mapping data as an individual. If anyone has a better idea, I'm all ears.-->
			<xsl:if test="idinfo/citation/citeinfo/origin != ''">
				<marc:datafield tag="100" ind1="1" ind2="0">
					<marc:subfield code="a"><xsl:value-of select="normalize-space(idinfo/citation/citeinfo/origin/.)" /></marc:subfield>
				</marc:datafield>
			</xsl:if>
			
			
			<!--245: since this is a required field in both formats, I'll assume that I don't need to test for its presence.-->
			<marc:datafield tag="245" ind1="1" ind2="0">
				<marc:subfield code="a">
					<xsl:variable name="t245" select="normalize-space(idinfo/citation/citeinfo/title/.)" />
					<xsl:value-of select="$t245" />
					<xsl:call-template name="CheckPunctuationBack">
						<xsl:with-param name="Source" select="$t245" />
						<xsl:with-param name="find_punct" select="'.?!&quot;);'" />
						<xsl:with-param name="insert_punct" select="'.'" />
				    </xsl:call-template>
				</marc:subfield>
			</marc:datafield>
			
			<!-- 250 -->
			<xsl:if test="idinfo/citation/citeinfo/edition !=''"> 
				<marc:datafield tag="250" ind1=" " ind2=" ">
					<xsl:variable name="t250" select="normalize-space(idinfo/citation/citeinfo/edition/.)" />
					<marc:subfield code="a">
						<xsl:value-of select="$t250" />
											
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="$t250" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
					</marc:subfield>
				</marc:datafield>
			</xsl:if>
			
			<!-- 255 -->
			<xsl:if test="spref/horizsys/planar/mapproj/mapprojn != '' or spref/horizsys/planar/gridsys/gridsysn !='' or idinfo/spdom/bounding/westbc != ''">
				<marc:datafield tag="255" ind1=" " ind2=" ">
					<xsl:if test = "spref/horizsys/planar/mapproj/mapprojn != ''">
						<marc:subfield code="b"><xsl:value-of select="spref/horizsys/planar/mapproj/mapprojn" /></marc:subfield>
					</xsl:if>
					<xsl:if test="idinfo/spdom/bounding/westbc != ''">
							<!--If the bounding coordinates are present, then all bounding coordinates should
								be present and the s and t subfields would not be needed.  If the bounding coordinates 
								are not present, then s and t would be valid -->
							<marc:subfield code="c">(<xsl:value-of select="idinfo/spdom/bounding/westbc" /> -- <xsl:value-of select="idinfo/spdom/bounding/eastbc" />/<xsl:value-of select="idinfo/spdom/bounding/northbc" /> -- <xsl:value-of select="idinfo/spdom/bounding/southbc" />).</marc:subfield>
					</xsl:if>
					<xsl:if test="spref/horizsys/planar/gridsys/gridsysn != ''">
						<marc:subfield code="d">(<xsl:value-of select="spref/horizsys/planar/gridsys/gridsysn" />).</marc:subfield>
					</xsl:if>
				</marc:datafield>
			</xsl:if>
			
			<!-- 260 -->
			<xsl:if test="idinfo/citation/citeinfo/pubinfo"> 
				<marc:datafield tag="260" ind1=" " ind2=" ">
				<xsl:if test="idinfo/citation/citeinfo/pubinfo/pubplace != ''"> 
					<marc:subfield code="a"><xsl:value-of select="normalize-space(idinfo/citation/citeinfo/pubinfo/pubplace/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test="idinfo/citation/citeinfo/pubinfo/publish != ''"> 
					<marc:subfield code="b"><xsl:value-of select="normalize-space(idinfo/citation/citeinfo/pubinfo/publish/.)" /></marc:subfield>
				</xsl:if>
				</marc:datafield>
			</xsl:if>
			
			<!-- 270 (this is a field that could be ignored if desired -->
			<xsl:if test="metainfo/metc/cntinfo">
				<marc:datafield tag="270" ind1=" " ind2=" ">
					<xsl:if test="metainfo/metc/cntinfo/cntaddr !=''">
						<xsl:if test="metainfo/metc/cntinfo/cntaddr/address !=''">
							<marc:subfield code="a"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntaddr/address/.)" /></marc:subfield>
						</xsl:if>
						
						<xsl:if test="metainfo/metc/cntinfo/cntaddr/city !=''">
							<marc:subfield code="b"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntaddr/city/.)" /></marc:subfield>
						</xsl:if>
						
						<xsl:if test="metainfo/metc/cntinfo/cntaddr/state !=''">
							<marc:subfield code="c"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntaddr/state/.)" /></marc:subfield>
						</xsl:if>
						
						<xsl:if test="metainfo/metc/cntinfo/cntaddr/country !=''">
							<marc:subfield code="d"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntaddr/country/.)" /></marc:subfield>
						</xsl:if>
						
						<xsl:if test="metainfo/metc/cntinfo/cntaddr/postal !=''">
							<marc:subfield code="e"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntaddr/postal/.)" /></marc:subfield>
						</xsl:if>
					</xsl:if>
					
					<xsl:if test="metainfo/metc/cntinfo/cnttdd !=''">
						<marc:subfield code="h"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cnttdd/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="metainfo/metc/cntinfo/cntvoice !=''">
						<marc:subfield code="k"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntvoice/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="metainfo/metc/cntinfo/cntemail !=''">
						<marc:subfield code="m"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntemail/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="metainfo/metc/cntinfo/cntperp/cntper !=''">
						<marc:subfield code="p"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntperp/cntper/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="metainfo/metc/cntinfo/cntperp/cntorg !=''">
						<marc:subfield code="q"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntperp/cntorg/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="metainfo/metc/cntinfo/hours !=''">
						<marc:subfield code="r"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/hours/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="metainfo/metc/cntinfo/cntinst !=''">
						<marc:subfield code="z"><xsl:value-of select="normalize-space(metainfo/metc/cntinfo/cntinst/.)" /></marc:subfield>
					</xsl:if>
				</marc:datafield>
			</xsl:if>
			
			<!-- 310 -->
			<xsl:if test="idinfo/status">
				<marc:datafield tag="310" ind1=" " ind2=" ">
					<marc:subfield code="c"><xsl:value-of select="normalize-space(idinfo/status/update/.)" /></marc:subfield>
				</marc:datafield>
			</xsl:if>
			
			<!-- 342 -->
			<!-- I'm going to revisit this mapping at a later point, but a full mapping makes my head hurt.
			     To many many to one matches and conditional mappings based on the type of field or projection.
			     So, to make my life easier, I'm mapping just the map projection, and the lat/long and geographic units.
			-->
			<xsl:if test="spref/horizsys/geograph or spref/horizsys/planar">
				<marc:datafield tag="342" ind1=" " ind2=" ">
					<xsl:if test="spref/horizsys/planar/mapproj/mapprojn != ''">
						<marc:subfield code="a"><xsl:value-of select="normalize-space(spref/horizsys/planar/mapproj/mapprojn/.)" /></marc:subfield>
					</xsl:if>
				
					<xsl:if test="spref/horizsys/geograph/geogunit != ''">
						<marc:subfield code="b"><xsl:value-of select="normalize-space(spref/horizsys/geograph/geogunit/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/horizsys/geograph/latres != ''">
						<marc:subfield code="c"><xsl:value-of select="normalize-space(spref/horizsys/geograph/latres/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/horizsys/geograph/longres != ''">
						<marc:subfield code="d"><xsl:value-of select="normalize-space(spref/horizsys/geograph/longres/.)" /></marc:subfield>
					</xsl:if>
				</marc:datafield>
			</xsl:if>
			
			<xsl:if test="spref/vertdef/altsys">
				<marc:datafield tag="342" ind1=" " ind2=" ">
					<xsl:if test="spref/vertdef/altsys/altdatum != ''">
						<marc:subfield code="a"><xsl:value-of select="normalize-space(spref/vertdef/altsys/altdatum/.)" /></marc:subfield>
					</xsl:if>
				
					<xsl:if test="spref/vertdef/altsys/altunits != ''">
						<marc:subfield code="b"><xsl:value-of select="normalize-space(spref/vertdef/altsys/altunits/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/vertdef/altsys/altres != ''">
						<marc:subfield code="t"><xsl:value-of select="normalize-space(spref/vertdef/altsys/altres/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/vertdef/altsys/altenc != ''">
						<marc:subfield code="u"><xsl:value-of select="normalize-space(spref/vertdef/altsys/altenc/.)" /></marc:subfield>
					</xsl:if>
				</marc:datafield>
			</xsl:if>
			<!-- 343 -->
			<xsl:if test="spref/horizsys/planar/planci/distbrep or spref/horizsys/planar/planci/plandu !=''">
				<marc:datafield tag="343" ind1=" " ind2=" ">
					<xsl:if test="spref/horizsys/planar/planci/distbrep/bearunit != ''">
						<marc:subfield code="a"><xsl:value-of select="normalize-space(spref/horizsys/planar/planci/distbrep/bearunit/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/horizsys/planar/planci/plandu != ''">
						<marc:subfield code="b"><xsl:value-of select="normalize-space(spref/horizsys/planar/planci/plandu/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/horizsys/planar/planci/distbrep/distres != ''">
						<marc:subfield code="e"><xsl:value-of select="normalize-space(spref/horizsys/planar/planci/distbrep/distres/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/horizsys/planar/planci/distbrep/bearres != ''">
						<marc:subfield code="f"><xsl:value-of select="normalize-space(spref/horizsys/planar/planci/distbrep/bearres/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/horizsys/planar/planci/distbrep/bearrefm != ''">
						<marc:subfield code="i"><xsl:value-of select="normalize-space(spref/horizsys/planar/planci/distbrep/bearrefm/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="spref/horizsys/planar/planci/distbrep/bearrefd != ''">
						<marc:subfield code="n"><xsl:value-of select="normalize-space(spref/horizsys/planar/planci/distbrep/bearrefd/.)" /></marc:subfield>
					</xsl:if>
				</marc:datafield>
			</xsl:if>
			
			<!-- 352 -->
			<xsl:if test="spdoinfo/direct">
				<xsl:variable name="direct" select="normalize-space(spdoinfo/direct/.)" />
				<marc:datafield tag="352" ind1=" " ind2=" ">
					<marc:subfield code="a"><xsl:value-of select="$direct" /></marc:subfield>
					<xsl:choose>
						<xsl:when test="$direct='Point'">
							<xsl:if test="spdoinfo/ptvctinf/sdtsterm/sdtstype != ''">
								<marc:subfield code="b"><xsl:value-of select="normalize-space(spdoinfo/ptvctinf/sdtsterm/sdtstype/.)"/></marc:subfield>
							</xsl:if>
							
							<xsl:if test="spdoinfo/ptvctinf/sdtsterm/ptvctnt != ''">
								<marc:subfield code="c"><xsl:value-of select="normalize-space(spdoinfo/ptvctinf/sdtsterm/ptvctnt/.)"/></marc:subfield>
							</xsl:if>
						</xsl:when>
						
						<xsl:when test="$direct='Vector'">
							<xsl:if test="spdoinfo/ptvctinf/vpfterm/vpfinfo != ''">
								<marc:subfield code="b"><xsl:value-of select="normalize-space(spdoinfo/ptvctinf/vpfterm/vpfinfo/.)"/></marc:subfield>
							</xsl:if>
							
							<xsl:if test="spdoinfo/ptvctinf/vpfterm/vpflevel != ''">
								<marc:subfield code="g"><xsl:value-of select="normalize-space(spdoinfo/ptvctinf/vpfterm/vpflevel/.)"/></marc:subfield>
							</xsl:if>
						</xsl:when>
						
						<xsl:when test="$direct='Raster'">
							<xsl:if test="spdoinfo/rastinfo/rasttype != ''">
								<marc:subfield code="b"><xsl:value-of select="normalize-space(spdoinfo/rastinfo/rasttype/.)"/></marc:subfield>
							</xsl:if>
							
							<xsl:if test="spdoinfo/rastinfo/rowcount != ''">
								<marc:subfield code="d"><xsl:value-of select="normalize-space(spdoinfo/rastinfo/rowcount/.)"/></marc:subfield>
							</xsl:if>
							
							<xsl:if test="spdoinfo/rastinfo/colcount != ''">
								<marc:subfield code="e"><xsl:value-of select="normalize-space(spdoinfo/rastinfo/colcount/.)"/></marc:subfield>
							</xsl:if>
							
							<xsl:if test="spdoinfo/rastinfo/vrtcount != ''">
								<marc:subfield code="f"><xsl:value-of select="normalize-space(spdoinfo/rastinfo/vrtcount/.)"/></marc:subfield>
							</xsl:if>
							
						</xsl:when>
						<xsl:otherwise />
					</xsl:choose>
					<xsl:if test="spdoinfo/indspref !=''">
						<marc:subfield code="i"><xsl:value-of select="normalize-space(spdoinfo/indspref/.)" /></marc:subfield>
					</xsl:if>
				</marc:datafield>
			</xsl:if>
			
			<!--  355 -->
			<xsl:if test="idinfo/secinfo">
				<marc:datafield tag="355" ind1=" " ind2=" ">
					<xsl:if test="idinfo/secinfo/secclass != ''">
						<marc:subfield code="a"><xsl:value-of select="normalize-space(idinfo/secinfo/secclass/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="idinfo/secinfo/sechandl != ''">
						<marc:subfield code="b"><xsl:value-of select="normalize-space(idinfo/secinfo/sechandl/.)" /></marc:subfield>
					</xsl:if>
					
					<xsl:if test="idinfo/secinfo/secsys != ''">
						<marc:subfield code="e"><xsl:value-of select="normalize-space(idinfo/secinfo/secsys/.)" /></marc:subfield>
					</xsl:if>
				</marc:datafield>
			</xsl:if>
					
			<!-- 500 -->
			<xsl:if test="idinfo/descript/purpose !=''">
				<marc:datafield tag="500" ind1=" " ind2=" ">
					<xsl:variable name="t500" select="normalize-space(idinfo/descript/purpose/.)" />
					<marc:subfield code="a">
						<xsl:value-of select="$t500" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="$t500" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
					</marc:subfield>
				</marc:datafield>
			</xsl:if>
			
			<xsl:if test="idinfo/descript/supplinf !=''">
				<marc:datafield tag="500" ind1=" " ind2=" ">
					<xsl:variable name="t500" select="normalize-space(idinfo/descript/supplinf/.)" />
					<marc:subfield code="a">
						<xsl:value-of select="$t500" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="$t500" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
					</marc:subfield>
				</marc:datafield>
			</xsl:if>

			<xsl:if test="idinfo/timeperd/current !=''">
				<marc:datafield tag="500" ind1=" " ind2=" ">
					<xsl:variable name="t500" select="normalize-space(idinfo/timeperd/current/.)" />
					<marc:subfield code="a">
						<xsl:value-of select="$t500" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="$t500" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
					</marc:subfield>
				</marc:datafield>
			</xsl:if>
			
			<xsl:if test="idinfo/citation/citeinfo/geoform !=''">
				<marc:datafield tag="500" ind1=" " ind2=" ">
					<xsl:variable name="t500" select="normalize-space(idinfo/citation/citeinfo/geoform/.)" />
					<marc:subfield code="a">
						<xsl:value-of select="$t500" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="$t500" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
					</marc:subfield>
				</marc:datafield>
			</xsl:if>
			
			<xsl:if test="idinfo/citation/citeinfo/othercit !=''">
				<marc:datafield tag="500" ind1=" " ind2=" ">
				<xsl:variable name="t500" select="normalize-space(idinfo/citation/citeinfo/othercit/.)" />
					<marc:subfield code="a">
						<xsl:value-of select="$t500" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="$t500" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
					</marc:subfield>
				</marc:datafield>
			</xsl:if>
			
			<xsl:for-each select="idinfo/browse">
				<xsl:if test="browsed != ''">
					<marc:datafield tag="500" ind1=" " ind2=" ">
						<xsl:variable name="t500" select="normalize-space(browsed/.)" />
						<marc:subfield code="a">
							<xsl:value-of select="$t500" />
							<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="$t500" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
						</marc:subfield>
					</marc:datafield>
				</xsl:if>
			</xsl:for-each>
        
        <!-- 506 -->
        <xsl:if test="idinfo/accconst !=''">
			<marc:datafield tag="506" ind1=" " ind2=" ">
				<marc:subfield code="a">
					<xsl:value-of select="normalize-space(idinfo/accconst/.)" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="normalize-space(idinfo/accconst/.)" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
				</marc:subfield>
			</marc:datafield>
		</xsl:if>
		
		<!-- 514 -->
		<!--First we need to see if a field will even be present.  To do that, simply test for the presence 
		    of the parent elements-->
		<xsl:if test="dataqual/attracc or dataqual/logic != '' or dataqual/complete !='' or dataqual/posacc or dataqual/cloud !=''">
			<!--If this test passes, then at least one element is present that will map to a 514 field-->
			<marc:datafield tag="514" ind1=" " ind2=" ">
			
			<xsl:if test="dataqual/attracc/attraccr !=''">
				<marc:subfield code="a"><xsl:value-of select="normalize-space(dataqual/attracc/attraccr/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/attracc/qattracc/attraccv !=''">
				<marc:subfield code="b"><xsl:value-of select="normalize-space(dataqual/attracc/qattracc/attraccv/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/attracc/qattracc/attracce !=''">
				<marc:subfield code="c"><xsl:value-of select="normalize-space(dataqual/attracc/qattracc/attracce/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/logic !=''">
				<marc:subfield code="d"><xsl:value-of select="normalize-space(dataqual/logic/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/complete !=''">
				<marc:subfield code="e"><xsl:value-of select="normalize-space(dataqual/complete/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/posacc/horizpa !=''">
				<marc:subfield code="f"><xsl:value-of select="normalize-space(dataqual/posacc/horizpa/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/posacc/qhorizpa/horizpav !=''">
				<marc:subfield code="g"><xsl:value-of select="normalize-space(dataqual/posacc/qhorizpa/horizpav/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/posacc/horizpa/horizpae !=''">
				<marc:subfield code="h"><xsl:value-of select="normalize-space(dataqual/posacc/horizpa/horizpae/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/posacc/vertacc/vertaccr !=''">
				<marc:subfield code="i"><xsl:value-of select="normalize-space(dataqual/posacc/vertacc/vertaccr/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/posacc/vertacc/qvertpa/vertaccv !=''">
				<marc:subfield code="j"><xsl:value-of select="normalize-space(dataqual/posacc/vertacc/qvertpa/vertaccv/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/posacc/vertacc/qvertpa/vertacce !=''">
				<marc:subfield code="k"><xsl:value-of select="normalize-space(dataqual/posacc/vertacc/qvertpa/vertacce/.)" /></marc:subfield>
			</xsl:if>
			
			<xsl:if test="dataqual/cloud !=''">
				<marc:subfield code="m"><xsl:value-of select="normalize-space(dataqual/cloud/.)" /></marc:subfield>
			</xsl:if>
			</marc:datafield>
		</xsl:if>
		
		<!-- 520 -->
		<xsl:if test="idinfo/descript/abstract != ''">
			<marc:datafield tag="520" ind1=" " ind2=" ">
				<marc:subfield code="a">
				<xsl:value-of select="normalize-space(idinfo/descript/abstract/.)" />
					<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="normalize-space(idinfo/descript/abstract/.)" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
				</marc:subfield>
			</marc:datafield>
		</xsl:if>
		
		<!-- 538 -->
		<xsl:if test="idinfo/native != ''">
			<marc:datafield tag="538" ind1=" " ind2=" ">
				<marc:subfield code="a">
					<xsl:value-of select="normalize-space(idinfo/native/.)" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="normalize-space(idinfo/native/.)" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
				</marc:subfield>
			</marc:datafield>
		</xsl:if>
		
		<!-- 540 -->
		<xsl:if test="metainfo/metuc != ''">
			<marc:datafield tag="540" ind1=" " ind2=" ">
				<marc:subfield code="a">
					<xsl:value-of select="normalize-space(metainfo/metuc/.)" />
						<xsl:call-template name="CheckPunctuationBack">
							<xsl:with-param name="Source" select="normalize-space(metainfo/metuc/.)" />
							<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
							<xsl:with-param name="insert_punct" select="'.'" />
						</xsl:call-template>
				</marc:subfield>
			</marc:datafield>
		</xsl:if>
		
		<!-- 552 -->
		<!-- First, try to decide if this field is even going to be printed -->
		<!-- For paired data, we need to setup a variable and test each 
		     data pair so that the element can be constructed correctly.  However, 
		     An assumption will be made since the data would not be valid in MARC
		     without the first element of the pair, the pair will only be evaluated if 
		     the first element of the pair is present. -->
		<xsl:if test = "eainfo/detailed or eainfo/overview/eadetcit != ''">
			<marc:datafield tag="552" ind1=" " ind2=" ">
				<xsl:if test = "eainfo/detailed/enttype/enttypl !=''">
					<marc:subfield code="a"><xsl:value-of select="normalize-space(eainfo/detailed/enttype/enttypl/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/enttype/enttypd != ''" >
					<xsl:variable name="f552b" select="normalize-space(eainfo/detailed/enttype/enttypd/.)" />
					<xsl:choose>
						<xsl:when test = "eainfo/detailed/enttype/enttypds != ''">
							<marc:subfield code="b"><xsl:value-of select="$f552b" /> (<xsl:value-of select="normalize-space(eainfo/detailed/enttype/enttypds/.)" />)</marc:subfield>
						</xsl:when>
						<xsl:otherwise>
							<marc:subfield code="b"><xsl:value-of select="$f552b" /></marc:subfield>					
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attr/attrlabl != ''">
					<marc:subfield code="c"><xsl:value-of select="normalize-space(eainfo/detailed/attr/attrlabl/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attr/attrdef != ''" >
					<xsl:variable name="f552d" select="normalize-space(eainfo/detailed/attr/attrdef/.)" />
					<xsl:choose>
						<xsl:when test = "eainfo/detailed/attr/attrdefs != ''">
							<marc:subfield code="d"><xsl:value-of select="$f552d" />, <xsl:value-of select="normalize-space(eainfo/detailed/attr/attrdefs/.)" /></marc:subfield>
						</xsl:when>
						<xsl:otherwise>
							<marc:subfield code="d"><xsl:value-of select="$f552d" /></marc:subfield>					
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/edom/edomv != ''">
					<marc:subfield code="e"><xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/edom/edomv/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/edom/edomvd != ''" >
					<xsl:variable name="f552f" select="normalize-space(eainfo/detailed/attrdomv/edom/edomvd/.)" />
					<xsl:choose>
						<xsl:when test = "eainfo/detailed/attrdomv/edom/edomvds != ''">
							<marc:subfield code="f"><xsl:value-of select="$f552f" />, <xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/edom/edomvds/.)" /></marc:subfield>
						</xsl:when>
						<xsl:otherwise>
							<marc:subfield code="f"><xsl:value-of select="$f552f" /></marc:subfield>					
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/rdom/rdommin != ''" >
					<xsl:variable name="f552g" select="normalize-space(eainfo/detailed/attrdomv/rdom/rdommin/.)" />
					<xsl:choose>
						<xsl:when test = "eainfo/detailed/attrdomv/rdom/rdommax != ''">
							<marc:subfield code="g"><xsl:value-of select="$f552g" />-<xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/rdom/rdommax/.)" /></marc:subfield>
						</xsl:when>
						<xsl:otherwise>
							<marc:subfield code="g"><xsl:value-of select="$f552g" /></marc:subfield>					
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/codesetd/codesetn != ''" >
					<xsl:variable name="f552h" select="normalize-space(eainfo/detailed/attrdomv/codesetd/codesetn/.)" />
					<xsl:choose>
						<xsl:when test = "eainfo/detailed/attrdomv/codesetd/codesets != ''">
							<marc:subfield code="h"><xsl:value-of select="$f552h" />, <xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/codesetd/codesets/.)" /></marc:subfield>
						</xsl:when>
						<xsl:otherwise>
							<marc:subfield code="h"><xsl:value-of select="$f552h" /></marc:subfield>					
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/udom != ''">
					<marc:subfield code="i"><xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/udom/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/attrunit != ''" >
					<xsl:variable name="f552j" select="normalize-space(eainfo/detailed/attrdomv/attrunit/.)" />
					<xsl:choose>
						<xsl:when test = "eainfo/detailed/attrdomv/attrmres != ''">
							<marc:subfield code="j"><xsl:value-of select="$f552j" /> (<xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/attrmres/.)" />)</marc:subfield>
						</xsl:when>
						<xsl:otherwise>
							<marc:subfield code="j"><xsl:value-of select="$f552j" /></marc:subfield>					
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/begdatea != ''" >
					<xsl:variable name="f552k" select="normalize-space(eainfo/detailed/attrdomv/begdatea/.)" />
					<xsl:choose>
						<xsl:when test = "eainfo/detailed/attrdomv/enddatea != ''">
							<marc:subfield code="k"><xsl:value-of select="$f552k" />-<xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/enddatea/.)" /></marc:subfield>
						</xsl:when>
						<xsl:otherwise>
							<marc:subfield code="k"><xsl:value-of select="$f552k" /></marc:subfield>					
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/attrvai/attrva != ''">
					<marc:subfield code="l"><xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/attrvai/attrva/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/attrvai/attrvae != ''">
					<marc:subfield code="m"><xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/attrvai/attrvae/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test = "eainfo/detailed/attrdomv/attrmfrq != ''">
					<marc:subfield code="n"><xsl:value-of select="normalize-space(eainfo/detailed/attrdomv/attrmfrq/.)" /></marc:subfield>
				</xsl:if>
				
				<xsl:if test = "eainfo/overview/eadetcit != ''">
					<marc:subfield code="p"><xsl:value-of select="normalize-space(eainfo/overview/eadetcit/.)" /></marc:subfield>
				</xsl:if>
			</marc:datafield>
		</xsl:if>
		
		<!-- 583 -->
		<!-- Because there can be nearly an infinite number of processing instructions, 
			 and I'm a little worried that on very large data sets, these notes could 
			 surpass the MARC record size limit of 99,999 characters so this is one field
			 that will not be mapped into MARC. -->
			 
		<!-- 650/651/653 -->
		<!-- Elements in the theme and (place, stratum) are mapped to 650 and 651 respectively.  Temporal 
			 is mapped to 653. -->
		<xsl:if test="idinfo/keywords">
			<xsl:if test="idinfo/keywords/theme">
				<xsl:variable name="themekt" select="normalize-space(idinfo/keywords/theme/themekt/.)" />
				<xsl:for-each select="idinfo/keywords/theme/themekey">
					<xsl:if test="$themekt != .">
						<xsl:choose>
							<xsl:when test = "$themekt != 'None' and $themekt != ''">
								<marc:datafield tag="650" ind1=" " ind2="7">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
										<xsl:call-template name="CheckPunctuationBack">
											<xsl:with-param name="Source" select="normalize-space(.)" />
											<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
											<xsl:with-param name="insert_punct" select="'.'" />
										</xsl:call-template>
									</marc:subfield>
									<marc:subfield code="2"><xsl:value-of select="$themekt" /></marc:subfield>	
								</marc:datafield>
							</xsl:when>
							<xsl:otherwise>
								<marc:datafield tag="650" ind1=" " ind2="0">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
										<xsl:call-template name="CheckPunctuationBack">
											<xsl:with-param name="Source" select="normalize-space(.)" />
											<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
											<xsl:with-param name="insert_punct" select="'.'" />
										</xsl:call-template>
									</marc:subfield>
								</marc:datafield>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
			</xsl:if>
			
			<xsl:if test="idinfo/keywords/place/placekey">
				<xsl:variable name="placekt" select="normalize-space(idinfo/keywords/place/placekt/.)" />
				<xsl:for-each select="idinfo/keywords/place/placekey">
					<xsl:if test="$placekt != .">
						<xsl:choose>
							<xsl:when test = "$placekt != 'None' and $placekt != ''">
								<marc:datafield tag="651" ind1=" " ind2="7">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
										<xsl:call-template name="CheckPunctuationBack">
											<xsl:with-param name="Source" select="normalize-space(.)" />
											<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
											<xsl:with-param name="insert_punct" select="'.'" />
										</xsl:call-template>
									</marc:subfield>
									<marc:subfield code="2"><xsl:value-of select="$placekt" /></marc:subfield>	
								</marc:datafield>
							</xsl:when>
							<xsl:otherwise>
								<marc:datafield tag="651" ind1=" " ind2="0">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
										<xsl:call-template name="CheckPunctuationBack">
											<xsl:with-param name="Source" select="normalize-space(.)" />
											<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
											<xsl:with-param name="insert_punct" select="'.'" />
										</xsl:call-template>
									</marc:subfield>
								</marc:datafield>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
			</xsl:if>
			
			<xsl:if test="idinfo/keywords/stratum">
				<xsl:variable name="stratumkt" select="normalize-space(idinfo/keywords/place/stratkt/.)" />
				<xsl:for-each select="idinfo/keywords/stratum/stratkey">
					<xsl:if test="$stratumkt != .">
						<xsl:choose>
							<xsl:when test = "$stratumkt != 'None' and $stratumkt != ''">
								<marc:datafield tag="651" ind1=" " ind2="7">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
										<xsl:call-template name="CheckPunctuationBack">
											<xsl:with-param name="Source" select="normalize-space(.)" />
											<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
											<xsl:with-param name="insert_punct" select="'.'" />
										</xsl:call-template>
									</marc:subfield>
									<marc:subfield code="2"><xsl:value-of select="$stratumkt" /></marc:subfield>	
								</marc:datafield>
							</xsl:when>
							<xsl:otherwise>
								<marc:datafield tag="651" ind1=" " ind2="0">
									<marc:subfield code="a">
										<xsl:value-of select="normalize-space(.)" />
										<xsl:call-template name="CheckPunctuationBack">
											<xsl:with-param name="Source" select="normalize-space(.)" />
											<xsl:with-param name="find_punct" select="'.?!&quot;);]'" />
											<xsl:with-param name="insert_punct" select="'.'" />
										</xsl:call-template>
									</marc:subfield>
								</marc:datafield>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:if>
				</xsl:for-each>
			</xsl:if>
			
			<!-- I believe that there can be just one? temporal keyword -->
			<xsl:if test="idinfo/keywords/temporal">
				<xsl:if test = "idinfo/keywords/temporal/tempkey != ''">
					<marc:datafield tag="653" ind1=" " ind2=" ">
						<marc:subfield code="a"><xsl:value-of select="normalize-space(idinfo/keywords/temporal/tempkey/.)" /></marc:subfield>
					</marc:datafield>
				</xsl:if>
			</xsl:if>
		</xsl:if>
		
		<!-- 700, 710, 711 -->
		<!-- The proper mapping would probably be the datacred element, but the problem is that the 
		     definition of this field is a little too loose to go into the 7xx fields.  Also, there is
		     no prescribed data format for this field.  This field can be repeated (for multiple notes) 
		     or it can be compacted into a single field.  Because of these problems, I would choose not to 
		     map this data element. -->
		 
		 <!-- 786 -->
		 <!-- Again, because of the potential for multiple lineage elements, I think that it is best
		      to not map this field into MARC. -->
		 
		 <!-- 856 -->
		 <xsl:for-each select="idinfo/citation/citeinfo/onlink">
			<marc:datafield tag="856" ind1="4" ind2="0">
				<marc:subfield code="z">Connect to this dataset online.</marc:subfield>
				<marc:subfield code="u"><xsl:value-of select="normalize-space(.)" /></marc:subfield>
			</marc:datafield>
		 </xsl:for-each>
		 
		 <!-- I'm not quite sure I want to include these (its distribution links) so I've 
			  put it here, but I'm commenting it out for now.
		 <xsl:for-each select="distinfo/stdorder/digform/digtopt/onlinopt/computer/networka/networkr">
			<marc:datafield tag="856" ind1="4" ind2="0">
				<xsl:if test="distinfo/stdorder/digform/digtinfo/formcont != ''">
					<marc:subfield code="z"><xsl:value-of select="normalize-space(distinfo/stdorder/digform/digtinfo/formcont/.)" /></marc:subfield>
				</xsl:if>
				<marc:subfield code="u"><xsl:value-of select="normalize-space(.)" /></marc:subfield>
			</marc:datafield>
		 </xsl:for-each>
		 -->
		
		</marc:record>
        </marc:collection>
    </xsl:template>
</xsl:stylesheet>

        
		
