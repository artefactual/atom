<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/oai_dc/
		http://www.openarchives.org/OAI/2.0/oai_dc.xsd" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
	xmlns:mods="http://www.loc.gov/mods/v3"
	xmlns:xlink="http://www.w3.org/1999/xlink" 
	xmlns:marc="http://www.loc.gov/MARC21/slim"
	exclude-result-prefixes="mods xlink marc">
	<xsl:import href="MARC21slimUtils.xsl" />
	<xsl:output method="xml" encoding="UTF-8" indent="yes" />
	<xsl:template match="/">
		<marc:collection xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">
			<xsl:apply-templates />
		</marc:collection>
	</xsl:template>
	<xsl:template match="OAI-PMH">
		<xsl:for-each select="ListRecords/record/metadata">
				<xsl:apply-templates />
		</xsl:for-each>
	</xsl:template>
	<!--<xsl:template match="text()" />-->
		<!-- 1/04 fix -->
	<!--<xsl:template match="targetAudience/listValue" mode="ctrl008">-->
	<xsl:template match="targetAudience[@authority='marctarget']" mode="ctrl008">
		<xsl:choose>
		<xsl:when test=".='adolescent'">d</xsl:when>
		<xsl:when test=".='adult'">e</xsl:when>
		<xsl:when test=".='general'">g</xsl:when>
		<xsl:when test=".='juvenile'">j</xsl:when>
		<xsl:when test=".='preschool'">a</xsl:when>
		<xsl:when test=".='specialized'">f</xsl:when>
		<xsl:otherwise><xsl:text>|</xsl:text></xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="typeOfResource" mode="leader">
		<xsl:choose>
			<xsl:when test="text()='text' and @manuscript='yes'">t</xsl:when>
			<xsl:when test="text()='text'">a</xsl:when>
			<xsl:when test="text()='cartographic' and @manuscript='yes'">f</xsl:when>
			<xsl:when test="text()='cartographic'">e</xsl:when>
			<xsl:when test="text()='notated music' and @manuscript='yes'">d</xsl:when>
			<xsl:when test="text()='notated music'">c</xsl:when>
			<!-- v3 musical/non -->
			<xsl:when test="text()='sound recording-nonmusical'">i</xsl:when>
			<xsl:when test="text()='sound recording'">j</xsl:when>
			<xsl:when test="text()='sound recording-musical'">j</xsl:when>
			<xsl:when test="text()='still image'">k</xsl:when>
			<xsl:when test="text()='moving image'">g</xsl:when>
			<xsl:when test="text()='three dimensional object'">r</xsl:when>
			<xsl:when test="text()='software, multimedia'">m</xsl:when>
			<xsl:when test="text()='mixed material'">p</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="typeOfResource" mode="ctrl008">
		<xsl:choose>
			<xsl:when test="text()='text' and @manuscript='yes'">BK</xsl:when>
			<xsl:when test="text()='text'">
			<xsl:choose> 
				<xsl:when test="../originInfo/issuance='monographic'">BK</xsl:when>
				<xsl:when test="../originInfo/issuance='continuing'">SE</xsl:when>
			</xsl:choose>
			</xsl:when>
			<xsl:when test="text()='cartographic' and @manuscript='yes'">MP</xsl:when>
			<xsl:when test="text()='cartographic'">MP</xsl:when>
			<xsl:when test="text()='notated music' and @manuscript='yes'">MU</xsl:when>
			<xsl:when test="text()='notated music'">MU</xsl:when>
			<xsl:when test="text()='sound recording'">MU</xsl:when>
			<!-- v3 musical/non -->
			<xsl:when test="text()='sound recording-nonmusical'">MU</xsl:when>
			<xsl:when test="text()='sound recording-musical'">MU</xsl:when>
			<xsl:when test="text()='still image'">VM</xsl:when>
			<xsl:when test="text()='moving image'">VM</xsl:when>
			<xsl:when test="text()='three dimensional object'">VM</xsl:when>
			<xsl:when test="text()='software, multimedia'">CF</xsl:when>
			<xsl:when test="text()='mixed material'">MM</xsl:when>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="controlField008-24-27">
		<xsl:variable name="chars">
			<xsl:for-each select="genre[@authority='marc']">
				<xsl:choose>
					<xsl:when test=".='abstract of summary'">a</xsl:when>
					<xsl:when test=".='bibliography'">b</xsl:when>
					<xsl:when test=".='catalog'">c</xsl:when>
					<xsl:when test=".='dictionary'">d</xsl:when>
					<xsl:when test=".='directory'">r</xsl:when>
					<xsl:when test=".='discography'">k</xsl:when>
					<xsl:when test=".='encyclopedia'">e</xsl:when>
					<xsl:when test=".='filmography'">q</xsl:when>
					<xsl:when test=".='handbook'">f</xsl:when>
					<xsl:when test=".='index'">i</xsl:when>
					<xsl:when test=".='law report or digest'">w</xsl:when>
					<xsl:when test=".='legal article'">g</xsl:when>
					<xsl:when test=".='legal case and case notes'">v</xsl:when>
					<xsl:when test=".='legislation'">l</xsl:when>
					<xsl:when test=".='patent'">j</xsl:when>
					<xsl:when test=".='programmed text'">p</xsl:when>
					<xsl:when test=".='review'">o</xsl:when>
					<xsl:when test=".='statistics'">s</xsl:when>
					<xsl:when test=".='survey of literature'">n</xsl:when>
					<xsl:when test=".='technical report'">t</xsl:when>
					<xsl:when test=".='theses'">m</xsl:when>
					<xsl:when test=".='treaty'">z</xsl:when>
				</xsl:choose>
			</xsl:for-each>
		</xsl:variable>
		<xsl:call-template name="makeSize">
			<xsl:with-param name="string" select="$chars"/>
			<xsl:with-param name="length" select="4"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template name="controlField008-30-31">
		<xsl:variable name="chars">
			<xsl:for-each select="genre[@authority='marc']">
				<xsl:choose>
					<xsl:when test=".='biography'">b</xsl:when>
					<xsl:when test=".='conference publication'">c</xsl:when>
					<xsl:when test=".='drama'">d</xsl:when>
					<xsl:when test=".='essay'">e</xsl:when>
					<xsl:when test=".='fiction'">f</xsl:when>
					<xsl:when test=".='folktale'">o</xsl:when>
					<xsl:when test=".='history'">h</xsl:when>
					<xsl:when test=".='humor, satire'">k</xsl:when>
					<xsl:when test=".='instruction'">i</xsl:when>
					<xsl:when test=".='interview'">t</xsl:when>
					<xsl:when test=".='language instruction'">j</xsl:when>
					<xsl:when test=".='memoir'">m</xsl:when>
					<xsl:when test=".='rehersal'">r</xsl:when>
					<xsl:when test=".='reporting'">g</xsl:when>
					<xsl:when test=".='sound'">s</xsl:when>
					<xsl:when test=".='speech'">l</xsl:when>
				</xsl:choose>
			</xsl:for-each>
		</xsl:variable>
		<xsl:call-template name="makeSize">
			<xsl:with-param name="string" select="$chars"/>
			<xsl:with-param name="length" select="2"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template name="makeSize">
		<xsl:param name="string"/>
		<xsl:param name="length"/>
		<xsl:variable name="nstring" select="normalize-space($string)"/>
		<xsl:variable name="nstringlength" select="string-length($nstring)"/>
		<xsl:choose>
			<xsl:when test="$nstringlength&gt;$length">
				<xsl:value-of select="substring($nstring,1,$length)"/>
			</xsl:when>
			<xsl:when test="$nstringlength&lt;$length">
				<xsl:value-of select="$nstring"/>
				<xsl:call-template name="buildSpaces">
					<xsl:with-param name="spaces" select="$length - $nstringlength"/>
					<xsl:with-param name="char">|</xsl:with-param>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$nstring"/>
			</xsl:otherwise>
		</xsl:choose>		
	</xsl:template>

	<xsl:template match="mods">
		<marc:record>
			<marc:leader>
				<!-- 00-04 -->				
				<xsl:text>     </xsl:text>
				<!-- 05 -->
				<xsl:text>n</xsl:text>
				<!-- 06 -->
				<xsl:apply-templates mode="leader" select="typeOfResource[1]"/>
				<!-- 07 -->
				<xsl:choose>
					<xsl:when test="originInfo/issuance='monographic'">m</xsl:when>
					<xsl:when test="originInfo/issuance='continuing'">s</xsl:when>
					<xsl:when test="typeOfResource/@collection='yes'">c</xsl:when>
					<xsl:otherwise>m</xsl:otherwise>
				</xsl:choose>
				<!-- 08 -->
				<xsl:text> </xsl:text>
				<!-- 09 -->
				<xsl:text> </xsl:text>
				<!-- 10 -->
				<xsl:text>2</xsl:text>
				<!-- 11 -->
				<xsl:text>2</xsl:text>
				<!-- 12-16 -->				
				<xsl:text>     </xsl:text>
				<!-- 17 -->
				<xsl:text>u</xsl:text>
				<!-- 18 -->
				<xsl:text>u</xsl:text>
				<!-- 19 -->				
				<xsl:text> </xsl:text>
				<!-- 20-23 -->
				<xsl:text>4500</xsl:text>
			</marc:leader>
			<xsl:call-template name="controlRecordInfo"/>
			<xsl:if test="genre[@authority='marc']='atlas'">
				<marc:controlfield tag="007">ad||||||</marc:controlfield>
			</xsl:if>
			<xsl:if test="genre[@authority='marc']='model'">
				<marc:controlfield tag="007">aq||||||</marc:controlfield>
			</xsl:if>
			<xsl:if test="genre[@authority='marc']='remote sensing image'">
				<marc:controlfield tag="007">ar||||||</marc:controlfield>
			</xsl:if>
			<xsl:if test="genre[@authority='marc']='map'">
				<marc:controlfield tag="007">aj||||||</marc:controlfield>
			</xsl:if>
			<xsl:if test="genre[@authority='marc']='globe'">
				<marc:controlfield tag="007">d|||||</marc:controlfield>
			</xsl:if>
			<marc:controlfield tag="008">
				<xsl:variable name="typeOf008"><xsl:apply-templates mode="ctrl008" select="typeOfResource"/></xsl:variable>
				<!-- 00-05 -->	
				<xsl:choose>
					<!-- 1/04 fix -->
					<xsl:when test="recordInfo/recordContentSource[@authority='marcorg']">
						<xsl:value-of select="recordInfo/recordCreationDate[@encoding='marc']"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>      </xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<!-- 06 -->	
				<xsl:choose>
					<xsl:when test="originInfo/issuance='monographic' and count(originInfo/dateIssued)=1">s</xsl:when>
					<!-- v3 questionable -->
					<xsl:when test="originInfo/dateIssued[@qualifier='questionable']">q</xsl:when>
					<xsl:when test="originInfo/issuance='monographic' and originInfo/dateIssued[@point='start'] and originInfo/dateIssued[@point='end']">m</xsl:when>
					<xsl:when test="originInfo/issuance='continuing' and originInfo/dateIssued[@point='end' and @encoding='marc']='9999'">c</xsl:when>
					<xsl:when test="originInfo/issuance='continuing' and originInfo/dateIssued[@point='end' and @encoding='marc']='uuuu'">u</xsl:when>
					<xsl:when test="originInfo/issuance='continuing' and originInfo/dateIssued[@point='end' and @encoding='marc']">d</xsl:when>
					<xsl:when test="not(originInfo/issuance) and originInfo/dateIssued">s</xsl:when>
					<!-- v3 copyright date-->
					<xsl:when test="originInfo/copyrightDate">s</xsl:when>
					<xsl:otherwise>|</xsl:otherwise>
				</xsl:choose>						
				<!-- 07-14          -->
				<!-- 07-10 -->
				<xsl:choose>
					<xsl:when test="originInfo/dateIssued[@point='start' and @encoding='marc']">
						<xsl:value-of select="originInfo/dateIssued[@point='start' and @encoding='marc']"/>
					</xsl:when>
					<xsl:when test="originInfo/dateIssued[@encoding='marc']">
						<xsl:value-of select="originInfo/dateIssued[@encoding='marc']"/>
					</xsl:when>
					<xsl:otherwise>					
						<xsl:text>    </xsl:text>
					</xsl:otherwise>
				</xsl:choose>				
				<!-- 11-14 -->
				<xsl:choose>
					<xsl:when test="originInfo/dateIssued[@point='end' and @encoding='marc']">
						<xsl:value-of select="originInfo/dateIssued[@point='end' and @encoding='marc']"/>
					</xsl:when>					
					<xsl:otherwise>
						<xsl:text>    </xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<!-- 15-17 -->	
				<xsl:choose>
					<!-- v3 place -->
					<xsl:when test="originInfo/place/placeTerm[@type='code'][@authority='marccountry']">
						<!-- v3 fixed marc:code reference and authority change-->
						<xsl:value-of select="originInfo/place/placeTerm[@type='code'][@authority='marccountry']"/>
						<!-- 1/04 fix -->
						<xsl:if test="string-length(originInfo/place/placeTerm[@type='code'][@authority='marccountry'])=2">
							<xsl:text> </xsl:text>
						</xsl:if>					
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>   </xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<!-- 18-20 -->	
				<xsl:text>|||</xsl:text>
				<!-- 21 -->
				<xsl:choose>
					<xsl:when test="$typeOf008='SE'">
						<xsl:choose>
							<xsl:when test="genre[@authority='marc']='database'">d</xsl:when>
							<xsl:when test="genre[@authority='marc']='loose-leaf'">l</xsl:when>
							<xsl:when test="genre[@authority='marc']='newspaper'">n</xsl:when>
							<xsl:when test="genre[@authority='marc']='periodical'">p</xsl:when>
							<xsl:when test="genre[@authority='marc']='series'">m</xsl:when>
							<xsl:when test="genre[@authority='marc']='web site'">w</xsl:when>
							<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>|</xsl:otherwise>
				</xsl:choose>
				<!-- 22 -->	
				<!-- 1/04 fix -->
				<xsl:choose>
					<xsl:when test="targetAudience[@authority='marctarget']">
						<xsl:apply-templates mode="ctrl008" select="targetAudience[@authority='marctarget']"/>
					</xsl:when>
					<xsl:otherwise>|</xsl:otherwise>
				</xsl:choose>
				<!-- 23 -->	
				<xsl:choose>
					<xsl:when test="$typeOf008='BK' or $typeOf008='MU' or $typeOf008='SE' or $typeOf008='MM'">
						<xsl:choose>
							<xsl:when test="physicalDescription/form[@authority='marcform']='braille'">f</xsl:when>
							<xsl:when test="physicalDescription/form[@authority='marcform']='electronic'">s</xsl:when>
							<xsl:when test="physicalDescription/form[@authority='marcform']='microfiche'">b</xsl:when>
							<xsl:when test="physicalDescription/form[@authority='marcform']='microfilm'">a</xsl:when>
							<xsl:when test="physicalDescription/form[@authority='marcform']='print'"><xsl:text> </xsl:text></xsl:when>
							<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>|</xsl:otherwise>
				</xsl:choose>
				<!-- 24-27 -->	
				<xsl:choose>
					<xsl:when test="$typeOf008='BK'">
						<xsl:call-template name="controlField008-24-27"/>
					</xsl:when>
					<xsl:when test="$typeOf008='MP'">
						<xsl:text>|</xsl:text>
						<xsl:choose>
							<xsl:when test="genre[@authority='marc']='atlas'">e</xsl:when>
							<xsl:when test="genre[@authority='marc']='globe'">d</xsl:when>
							<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
						<xsl:text>||</xsl:text>
					</xsl:when>
					<xsl:when test="$typeOf008='CF'">
						<xsl:text>||</xsl:text>
						<xsl:choose>
							<xsl:when test="genre[@authority='marc']='database'">e</xsl:when>
							<xsl:when test="genre[@authority='marc']='font'">f</xsl:when>
							<xsl:when test="genre[@authority='marc']='game'">g</xsl:when>
							<xsl:when test="genre[@authority='marc']='numerical data'">a</xsl:when>
							<xsl:when test="genre[@authority='marc']='sound'">h</xsl:when>
							<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
						<xsl:text>|</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>||||</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<!-- 28 -->					
				<xsl:text>|</xsl:text>
				<!-- 29 -->
				<xsl:choose>
					<xsl:when test="$typeOf008='BK' or $typeOf008='SE'">
						<xsl:choose>
							<xsl:when test="genre[@authority='marc']='conference publication'">1</xsl:when>
							<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:when test="$typeOf008='MP' or $typeOf008='VM'">
						<xsl:choose>
						<xsl:when test="physicalDescription/form='braille'">f</xsl:when>
						<xsl:when test="physicalDescription/form='electronic'">m</xsl:when>
						<xsl:when test="physicalDescription/form='microfiche'">b</xsl:when>
						<xsl:when test="physicalDescription/form='microfilm'">a</xsl:when>
						<xsl:when test="physicalDescription/form='print'"><xsl:text> </xsl:text></xsl:when>
						<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
					</xsl:when>					
					<xsl:otherwise>|</xsl:otherwise>
				</xsl:choose>
				<!-- 30-31 -->
				<xsl:choose>
					<xsl:when test="$typeOf008='MU'">
						<xsl:call-template name="controlField008-30-31"/>
					</xsl:when>
					<xsl:when test="$typeOf008='BK'">
						<xsl:choose>
							<xsl:when test="genre[@authority='marc']='festschrift'">1</xsl:when>
							<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
						<xsl:text>|</xsl:text>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>||</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<!-- 32 -->					
				<xsl:text>|</xsl:text>
				<!-- 33 -->
				<xsl:choose>
					<xsl:when test="$typeOf008='VM'">
						<xsl:choose>
						<xsl:when test="genre[@authority='marc']='art originial'">a</xsl:when>
						<xsl:when test="genre[@authority='marc']='art reproduction'">c</xsl:when>
						<xsl:when test="genre[@authority='marc']='chart'">n</xsl:when>
						<xsl:when test="genre[@authority='marc']='diorama'">d</xsl:when>
						<xsl:when test="genre[@authority='marc']='filmstrip'">f</xsl:when>
						<xsl:when test="genre[@authority='marc']='flash card'">o</xsl:when>
						<xsl:when test="genre[@authority='marc']='graphic'">k</xsl:when>
						<xsl:when test="genre[@authority='marc']='kit'">b</xsl:when>
						<xsl:when test="genre[@authority='marc']='technical drawing'">l</xsl:when>
						<xsl:when test="genre[@authority='marc']='slide'">s</xsl:when>
						<xsl:when test="genre[@authority='marc']='realia'">r</xsl:when>
						<xsl:when test="genre[@authority='marc']='picture'">i</xsl:when>
						<xsl:when test="genre[@authority='marc']='motion picture'">m</xsl:when>
						<xsl:when test="genre[@authority='marc']='model'">q</xsl:when>
						<xsl:when test="genre[@authority='marc']='microscope slide'">p</xsl:when>
						<xsl:when test="genre[@authority='marc']='toy'">w</xsl:when>
						<xsl:when test="genre[@authority='marc']='transparency'">t</xsl:when>
						<xsl:when test="genre[@authority='marc']='videorecording'">v</xsl:when>
						<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:when test="$typeOf008='BK'">
						<xsl:choose>
						<xsl:when test="genre[@authority='marc']='comic strip'">c</xsl:when>
						<xsl:when test="genre[@authority='marc']='fiction'">1</xsl:when>
						<xsl:when test="genre[@authority='marc']='essay'">e</xsl:when>
						<xsl:when test="genre[@authority='marc']='drama'">d</xsl:when>
						<xsl:when test="genre[@authority='marc']='humor, satire'">h</xsl:when>
						<xsl:when test="genre[@authority='marc']='letter'">i</xsl:when>
						<xsl:when test="genre[@authority='marc']='novel'">f</xsl:when>
						<xsl:when test="genre[@authority='marc']='short story'">j</xsl:when>
						<xsl:when test="genre[@authority='marc']='speech'">s</xsl:when>
						<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>|</xsl:otherwise>
				</xsl:choose>
				<!-- 34 -->	
				<xsl:choose>
					<xsl:when test="$typeOf008='BK'">
						<xsl:choose>
							<xsl:when test="genre[@authority='marc']='biography'">d</xsl:when>
							<xsl:otherwise>|</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>|</xsl:otherwise>
				</xsl:choose>
				<!-- 35-37 -->	
				<xsl:choose>
				<!-- v3 language -->
					<xsl:when test="language/languageTerm[@authority='iso639-2b']">
						<xsl:value-of select="language/languageTerm[@authority='iso639-2b']"/>
					</xsl:when>
					<xsl:otherwise>
						<xsl:text>|||</xsl:text>
					</xsl:otherwise>
				</xsl:choose>
				<!-- 38-39 -->	
				<xsl:text>||</xsl:text>
			</marc:controlfield>
			<!-- 1/04 fix sort -->
			<xsl:call-template name="source"/>
			<xsl:apply-templates/>
			<xsl:if test="classification[@authority='lcc']">
				<xsl:call-template name="lcClassification"/>
			</xsl:if>
		</marc:record>
	</xsl:template>

	<xsl:template match="*"/>

	<!-- v3 language -->
	<xsl:template match="language/languageTerm[@authority='iso639-2b']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">041</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='a'>
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<!-- v3 language -->
	<xsl:template match="language/languageTerm[@authority='rfc3066']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">041</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="ind2">7</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='a'>
					<xsl:value-of select="."/>
				</marc:subfield>
				<marc:subfield code='2'>rfc3066</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
<!-- 1/04 fix -->
<!--	<xsl:template match="targetAudience">
		<xsl:apply-templates/>
	</xsl:template>-->
	
	<!--<xsl:template match="targetAudience/otherValue"> -->
	<xsl:template match="targetAudience[not(@authority)] | targetAudience[@authority!='marctarget']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">521</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='a'>
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="physicalDescription">
		<xsl:apply-templates/>
	</xsl:template>
	<!-- 1/04 fix -->
	<!--<xsl:template match="physicalDescription/extent">-->
	<xsl:template match="extent">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">300</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='a'>
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="note[not(@type='statement of responsibility')]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">
				<xsl:choose>
					<xsl:when test="@type='performers'">511</xsl:when>
					<xsl:when test="@type='venue'">518</xsl:when>
					<xsl:otherwise>500</xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='a'>
					<xsl:value-of select="."/>
				</marc:subfield>
				<!-- 1/04 fix: 856$u instead -->
				<!--<xsl:for-each select="@xlink:href">
					<marc:subfield code='u'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>-->
			</xsl:with-param>
		</xsl:call-template>
		<xsl:for-each select="@xlink:href">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">856</xsl:with-param>
				<xsl:with-param name="subfields">
					<marc:subfield code='u'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	<!-- 1/04 fix -->
	<!--<xsl:template match="note[@type='statement of responsibility']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">245</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='c'>
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
-->
	<xsl:template match="accessCondition">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">
			<xsl:choose>
				<xsl:when test="@type='restrictionOnAccess'">506</xsl:when>
				<xsl:when test="@type='useAndReproduction'">540</xsl:when>
			</xsl:choose>
			</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='a'>
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<!-- 1/04 fix -->
	<xsl:template name="controlRecordInfo">
	<!--<xsl:template match="recordInfo">-->
		<xsl:for-each select="recordInfo/recordIdentifier">
			<marc:controlfield tag="001"><xsl:value-of select="."/></marc:controlfield>
			<xsl:for-each select="@source">
				<marc:controlfield tag="003"><xsl:value-of select="."/></marc:controlfield>			
			</xsl:for-each>
		</xsl:for-each>
		<xsl:for-each select="recordInfo/recordChangeDate[@encoding='iso8601']">
			<marc:controlfield tag="005"><xsl:value-of select="."/></marc:controlfield>
		</xsl:for-each>		
	</xsl:template>
	<!-- v3 authority -->

	<xsl:template name="source">
		<xsl:for-each select="recordInfo/recordContentSource[@authority='marcorg']">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">040</xsl:with-param>
				<xsl:with-param name="subfields">
					<marc:subfield code="a">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>
	<xsl:template match="genre[@authority!='marc' or not(@authority)]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">655</xsl:with-param>
			<xsl:with-param name="ind2">7</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code='a'>
					<xsl:value-of select="."/>
				</marc:subfield>
				<xsl:for-each select="@authority">
					<marc:subfield code='2'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<!-- v3 geographicCode -->
	<xsl:template match="subject/geographicCode[@authority]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">043</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:for-each select="self::geographicCode[@authority='marcgac']">
					<marc:subfield code='a'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="self::geographicCode[@authority='iso3166']">
					<marc:subfield code='c'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="originInfo">
		<!-- v3 place, and fixed "placeCode (v1?) -->		
		<xsl:for-each select="place/placeTerm[@type='code'][@authority='iso3166']">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">044</xsl:with-param>
				<xsl:with-param name="subfields">
					<marc:subfield code='c'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>		
		<!-- v3 dates -->
		<xsl:if test="dateModified|dateCreated|dateValid">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">046</xsl:with-param>
				<xsl:with-param name="subfields">
					<xsl:for-each select="dateModified">
						<marc:subfield code='j'>
							<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:for-each>
					<xsl:for-each select="dateCreated[@point='start']|dateCreated[not(@point)]">
						<marc:subfield code='k'>
							<xsl:value-of select="."/>
						</marc:subfield>				
					</xsl:for-each>
					<xsl:for-each select="dateCreated[@point='end']">
						<marc:subfield code='l'>
							<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:for-each>
					<xsl:for-each select="dateValid[@point='start']|dateValid[not(@point)]">
						<marc:subfield code='m'>
							<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:for-each>
					<xsl:for-each select="dateValid[@point='end']">
						<marc:subfield code='n'>
							<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:for-each>
				</xsl:with-param>			
			</xsl:call-template>	
		</xsl:if>	
		<xsl:for-each select="edition">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">250</xsl:with-param>
				<xsl:with-param name="subfields">
					<marc:subfield code='a'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
		<xsl:for-each select="frequency">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">310</xsl:with-param>
				<xsl:with-param name="subfields">
					<marc:subfield code='a'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">260</xsl:with-param>
			<xsl:with-param name="subfields">
				<!-- v3 place; changed to text  -->
				<xsl:for-each select="place/placeTerm[@type='text']">
					<marc:subfield code='a'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="publisher">
					<marc:subfield code='b'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="dateIssued">
					<marc:subfield code='c'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="dateCreated">
					<marc:subfield code='g'>
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="titleInfo[@type='abbreviated']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">210</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="titleInfo"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="titleInfo[@type='translated']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">242</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="ind2" select="string-length(nonSort)"/>
			<xsl:with-param name="subfields">
				<xsl:call-template name="titleInfo"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="titleInfo[@type='alternative']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">246</xsl:with-param>
			<xsl:with-param name="ind1">3</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="titleInfo"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="titleInfo[@type='uniform'][1]">
		<xsl:choose>
		<!-- v3 role -->
		<xsl:when test="../name/role/roleTerm[@type='text']='creator' or name/role/roleTerm[@type='code']='cre'">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">240</xsl:with-param>
				<xsl:with-param name="ind1">1</xsl:with-param>
				<xsl:with-param name="ind2" select="string-length(nonSort)"/>
				<xsl:with-param name="subfields">
					<xsl:call-template name="titleInfo"/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:when>
		<xsl:otherwise>
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">130</xsl:with-param>
				<xsl:with-param name="ind1" select="string-length(nonSort)"/>
				<xsl:with-param name="subfields">
					<xsl:call-template name="titleInfo"/>
				</xsl:with-param>
			</xsl:call-template>
		</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
	<!-- 1/04 fix: 2nd uniform title to 730 -->
	<xsl:template match="titleInfo[@type='uniform'][position()>1]">		
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">730</xsl:with-param>
			<xsl:with-param name="ind1" select="string-length(nonSort)"/>
			<xsl:with-param name="subfields">
				<xsl:call-template name="titleInfo"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<!-- 1/04 fix -->

	<!--<xsl:template match="titleInfo[not(ancestor-or-self::subject)][not(@type)]">-->
	<xsl:template match="titleInfo[not(ancestor-or-self::subject)][not(@type)][1]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">245</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="ind2" select="string-length(nonSort)"/>
			<xsl:with-param name="subfields">
				<xsl:call-template name="titleInfo"/>
				<!-- 1/04 fix -->
				<xsl:call-template name="stmtOfResponsibility"/>
				<xsl:call-template name="form"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<xsl:template match="titleInfo[not(ancestor-or-self::subject)][not(@type)][position()>1]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">246</xsl:with-param>
			<xsl:with-param name="ind1">3</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="titleInfo"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<xsl:template match="abstract">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">520</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
				<xsl:for-each select="@xlink:href">
					<marc:subfield code="u">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="tableOfContents">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">505</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
				<xsl:for-each select="@xlink:href">
					<marc:subfield code="u">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="subject">
		<xsl:apply-templates/>
	</xsl:template>

	<!-- 1/04 fix was 630 -->
	<xsl:template match="subject/heirarchialGeographic">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">752</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:for-each select="country">
					<marc:subfield code="a">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="state">
					<marc:subfield code="b">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="county">
					<marc:subfield code="c">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="city">
					<marc:subfield code="d">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="subject/cartographics">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">255</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:for-each select="coordinates">
					<marc:subfield code="c">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="scale">
					<marc:subfield code="a">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="projection">
					<marc:subfield code="b">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template name="titleInfo">
		<xsl:for-each select="title">
			<marc:subfield code="a">
				<xsl:value-of select="../nonSort"/><xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
		<!-- 1/04 fix -->
		<xsl:for-each select="subTitle">
			<marc:subfield code="b">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
		<xsl:for-each select="partNumber">
			<marc:subfield code="n">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
		<xsl:for-each select="partName">
			<marc:subfield code="p">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="stmtOfResponsibility">
		<xsl:for-each select="following-sibling::note[@type='statement of responsibility']">		
			<marc:subfield code='c'>
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
	</xsl:template>

	<xsl:template name="lcClassification">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">050</xsl:with-param>
			<xsl:with-param name="ind2">
				<xsl:choose>
				<xsl:when test="../recordInfo/recordContentSource='DLC' or ../recordInfo/recordContentSource='Library of Congress'">0</xsl:when>
				<xsl:otherwise>2</xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:for-each select="classification[@authority='lcc']">
					<marc:subfield code="a">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>

	</xsl:template>

	<!--<xsl:template match="classification[@authority='lcc']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">050</xsl:with-param>
			<xsl:with-param name="ind2">
				<xsl:choose>
				<xsl:when test="../recordInfo/recordContentSource='DLC' or ../recordInfo/recordContentSource='Library of Congress'">0</xsl:when>
				<xsl:otherwise>2</xsl:otherwise>
				</xsl:choose>
			</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
-->
	
	<xsl:template match="classification[@authority='ddc']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">082</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
				<xsl:for-each select="@edition">
					<marc:subfield code="2">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="classification[@authority='udc']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">080</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="classification[@authority='nlm']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">060</xsl:with-param>
			<xsl:with-param name="ind2">4</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="classification[@authority='sudocs']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">086</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="classification[@authority='candocs']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">086</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='doi']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">856</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="u">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<!--v3 location/url -->
	
	<xsl:template match="location[url]">
		<xsl:for-each select="url">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">856</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="u">
					<xsl:value-of select="."/>
				</marc:subfield>
				<!-- v3 displayLabel -->
				<xsl:for-each select="@displayLabel">
					<marc:subfield code="3">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="@dateLastAccessed">
					<marc:subfield code="z">
						<xsl:value-of select="concat('Last accessed: ',.)"/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
			</xsl:call-template>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="identifier[@type='isbn']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">020</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='isrc']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">024</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='ismn']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">024</xsl:with-param>
			<xsl:with-param name="ind1">2</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='issn']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">022</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='issue number']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">028</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="ind2">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='lccn']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">010</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='matrix number']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">028</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="ind2">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='music publisher']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">028</xsl:with-param>
			<xsl:with-param name="ind1">3</xsl:with-param>
			<xsl:with-param name="ind2">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='music plate']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">028</xsl:with-param>
			<xsl:with-param name="ind1">2</xsl:with-param>
			<xsl:with-param name="ind2">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='sici']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">024</xsl:with-param>
			<xsl:with-param name="ind1">4</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='uri']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">856</xsl:with-param>
			<xsl:with-param name="ind2"><xsl:text> </xsl:text></xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="u">
					<xsl:value-of select="."/>
				</marc:subfield>
				<xsl:call-template name="mediaType"/>				
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='upc']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">024</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="identifier[@type='videorecording']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">028</xsl:with-param>
			<xsl:with-param name="ind1">4</xsl:with-param>
			<xsl:with-param name="ind2">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="name">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">720</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="namePart"/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	<!-- v3 role-->
	<xsl:template match="name[@type='personal'][role/roleTerm[@type='text']='creator']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">100</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="namePart"/>
				</marc:subfield><!-- v3 termsOfAddress -->
				<xsl:for-each select="namePart[@type='termsOfAddress']">
					<marc:subfield code="c">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="namePart[@type='date']">
					<marc:subfield code="d">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<!-- v3 role -->
				<xsl:for-each select="role/roleTerm[@type='text']">
					<marc:subfield code="e">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="affiliation">
					<marc:subfield code="u">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="description">
					<marc:subfield code="g">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	<!-- v3 role -->
	<xsl:template match="name[@type='corporate'][role/roleTerm[@type='text']='creator']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">110</xsl:with-param>
			<xsl:with-param name="ind1">2</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="namePart[1]"/>
				</marc:subfield>
				<xsl:for-each select="namePart[position()>1]">
					<marc:subfield code="b">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<!-- v3 role -->
				<xsl:for-each select="role/roleTerm[@type='text']">
					<marc:subfield code="e">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="description">
					<marc:subfield code="g">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	<!-- v3 role -->
	<xsl:template match="name[@type='conference'][role/roleTerm[@type='text']='creator']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">111</xsl:with-param>
			<xsl:with-param name="ind1">2</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="namePart[1]"/>
				</marc:subfield>
				<!-- v3 role -->
				<xsl:for-each select="role/roleTerm[@type='code']">
					<marc:subfield code="4">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	<!-- v3 role -->
	<xsl:template match="name[@type='personal'][role/roleTerm[@type='text']!='creator' or not(role)]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">700</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="namePart"/>
				</marc:subfield>
				<!-- v3 termsofAddress -->
				<xsl:for-each select="namePart[@type='termsOfAddress']">
					<marc:subfield code="c">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="namePart[@type='date']">
					<marc:subfield code="d">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<!-- v3 role -->
				<xsl:for-each select="role/roleTerm[@type='text']">
					<marc:subfield code="e">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="affiliation">
					<marc:subfield code="u">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	<!-- v3 role -->
	<xsl:template match="name[@type='corporate'][role/roleTerm[@type='text']!='creator' or not(role)]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">710</xsl:with-param>
			<xsl:with-param name="ind1">2</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<!-- 1/04 fix -->
					<xsl:value-of select="namePart[1]"/>
				</marc:subfield>
				<xsl:for-each select="namePart[position()>1]">
					<marc:subfield code="b"><xsl:value-of select="."/></marc:subfield>
				</xsl:for-each><!-- v3 role -->
				<xsl:for-each select="role/roleTerm[@type='text']">
					<marc:subfield code="e">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<xsl:for-each select="description">
					<marc:subfield code="g">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	<!-- v3 role -->
	<xsl:template match="name[@type='conference'][role/roleTerm[@type='text']!='creator' or not(role)]">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">711</xsl:with-param>
			<xsl:with-param name="ind1">2</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="namePart[1]"/>
				</marc:subfield>
				<!-- v3 role -->
				<xsl:for-each select="role/roleTerm[@type='code']">
					<marc:subfield code="4">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>

	<xsl:template name="relatedItemNames">
		<xsl:if test="name">
			<marc:subfield code="a">
			<xsl:variable name="nameString">
				<xsl:for-each select="name">			
					<xsl:value-of select="namePart[1][not(@type='date')]"/>
					<xsl:if test="namePart[position()&gt;1][@type='date']">
						<xsl:value-of select="concat(' ',namePart[position()&gt;1][@type='date'])"/>
					</xsl:if>
					<xsl:choose>
						<xsl:when test="role/roleTerm[@type='text']">			
							<xsl:value-of select="concat(', ',role/roleTerm)"/>
						</xsl:when>	
						<xsl:when test="role/roleTerm[@type='code']">
							<xsl:value-of select="concat(', ',role/roleTerm)"/>
						</xsl:when>
					</xsl:choose>
				</xsl:for-each>
				<xsl:text>, </xsl:text>
			</xsl:variable>
			<xsl:value-of select="substring($nameString, 1,string-length($nameString)-2)"/>
			</marc:subfield>		
		</xsl:if>
	</xsl:template>

	<xsl:template name="authorityInd">
		<xsl:choose>
			<xsl:when test="@authority='lcsh'">0</xsl:when>
			<xsl:when test="@authority='lcshac'">1</xsl:when>
			<xsl:when test="@authority='mesh'">2</xsl:when>
			<xsl:when test="@authority='csh'">3</xsl:when>
			<xsl:when test="@authority='nal'">5</xsl:when>
			<xsl:when test="@authority='rvm'">6</xsl:when>
			<xsl:when test="@authority">7</xsl:when>
			<xsl:otherwise><xsl:text> </xsl:text></xsl:otherwise><!-- v3 blank ind2 fix-->
		</xsl:choose>
	</xsl:template>

	<xsl:template match="subject[local-name(*[1])='topic']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">650</xsl:with-param>
			<xsl:with-param name="ind1">1</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="*[1]"/>
				</marc:subfield>
				<xsl:apply-templates select="*[position()>1]"/>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
<xsl:template match="subject[local-name(*[1])='titleInfo']">

<!--	<xsl:template match="subject[@authority='lcsh'][title]">-->

		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">630</xsl:with-param>
			<xsl:with-param name="ind1"><xsl:value-of select="string-length(titleInfo/nonSort)"/></xsl:with-param>
			<xsl:with-param name="ind2"><xsl:call-template name="authorityInd"/></xsl:with-param>
			<xsl:with-param name="subfields">				
				<xsl:for-each select="titleInfo">
					<xsl:call-template name="titleInfo"/>
				</xsl:for-each>
				<xsl:apply-templates select="*[position()>1]"/>				
				
			</xsl:with-param>
		</xsl:call-template>	
		
	</xsl:template>

	<xsl:template match="subject[local-name(*[1])='name']">
		<xsl:for-each select="*[1]">
			<xsl:choose>
			<xsl:when test="@type='personal'">
				<xsl:call-template name="datafield">
					<xsl:with-param name="tag">600</xsl:with-param>
					<xsl:with-param name="ind1">1</xsl:with-param>
					<xsl:with-param name="ind2"><xsl:call-template name="authorityInd"/></xsl:with-param>
					<xsl:with-param name="subfields">
						<marc:subfield code="a">
							<xsl:value-of select="namePart"/>
						</marc:subfield>
						<!-- v3 termsofAddress -->
						<xsl:for-each select="namePart[@type='termsOfAddress']">
							<marc:subfield code="c">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
						<xsl:for-each select="namePart[@type='date']">
						<!-- v3 namepart/date was $a; fixed to $d -->
							<marc:subfield code="d">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
						<!-- v3 role -->
						<xsl:for-each select="role/roleTerm[@type='text']">
							<marc:subfield code="e">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
						<xsl:for-each select="affiliation">
							<marc:subfield code="u">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
						<xsl:apply-templates select="*[position()>1]"/>
					</xsl:with-param>
				</xsl:call-template>	
			</xsl:when>
			<xsl:when test="@type='corporate'">
				<xsl:call-template name="datafield">
					<xsl:with-param name="tag">610</xsl:with-param>
					<xsl:with-param name="ind1">2</xsl:with-param>
					<xsl:with-param name="ind2"><xsl:call-template name="authorityInd"/></xsl:with-param>
					<xsl:with-param name="subfields">
						<marc:subfield code="a">
							<xsl:value-of select="namePart"/>
						</marc:subfield>
						<xsl:for-each select="namePart[position()>1]">
							<marc:subfield code="a">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
						<!-- v3 role -->
						<xsl:for-each select="role/roleTerm[@type='text']">
							<marc:subfield code="e">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
						<!--<xsl:apply-templates select="*[position()>1]"/>-->
						<xsl:apply-templates select="ancestor-or-self::subject/*[position()>1]"/>

					</xsl:with-param>
				</xsl:call-template>	
			</xsl:when>
			<xsl:when test="@type='conference'">
				<xsl:call-template name="datafield">
					<xsl:with-param name="tag">611</xsl:with-param>
					<xsl:with-param name="ind1">2</xsl:with-param>
					<xsl:with-param name="ind2"><xsl:call-template name="authorityInd"/></xsl:with-param>
					<xsl:with-param name="subfields">
						<marc:subfield code="a">
							<xsl:value-of select="namePart"/>
						</marc:subfield>
						<!-- v3 role -->
						<xsl:for-each select="role/roleTerm[@type='code']">
							<marc:subfield code="4">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
						<xsl:apply-templates select="*[position()>1]"/>
					</xsl:with-param>
				</xsl:call-template>	
			</xsl:when>
			</xsl:choose>
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="subject[local-name(*[1])='geographic']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">651</xsl:with-param>
			<xsl:with-param name="ind2"><xsl:call-template name="authorityInd"/></xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="*[1]"/>
				</marc:subfield>
				<xsl:apply-templates select="*[position()>1]"/>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	
	<xsl:template match="subject[local-name(*[1])='temporal']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">650</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="*[1]"/>
				</marc:subfield>
				<xsl:apply-templates select="*[position()>1]"/>
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	<!-- v3 occupation -->
	<xsl:template match="subject/occupation">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">656</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>				
			</xsl:with-param>
		</xsl:call-template>	
	</xsl:template>
	
	<xsl:template match="subject/topic">
		<marc:subfield code="x">
			<xsl:value-of select="."/>
		</marc:subfield>
	</xsl:template>
	
	<xsl:template match="subject/temporal">
		<marc:subfield code="y">
			<xsl:value-of select="."/>
		</marc:subfield>
	</xsl:template>

	<xsl:template match="subject/geographic">
		<marc:subfield code="z">
			<xsl:value-of select="."/>
		</marc:subfield>
	</xsl:template>
	<!-- v3 physicalLocation -->
	<xsl:template match="location[physicalLocation]">
		<xsl:for-each select="physicalLocation">
			<xsl:call-template name="datafield">
				<xsl:with-param name="tag">852</xsl:with-param>
				<xsl:with-param name="subfields">
					<marc:subfield code="a">
						<xsl:value-of select="."/>
					</marc:subfield>
					<!-- v3 displayLabel -->
					<xsl:for-each select="@displayLabel">
						<marc:subfield code="3">
							<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:for-each>
				</xsl:with-param>
			</xsl:call-template>		
		</xsl:for-each>
	</xsl:template>

	<xsl:template match="extension">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">887</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="a">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>		
	</xsl:template>
	<!-- v3 isReferencedBy -->
	<xsl:template match="relatedItem[@type='isReferencedBy']">	
		   	<xsl:call-template name="datafield">
			<xsl:with-param name="tag">510</xsl:with-param>		
			<xsl:with-param name="subfields">
				<xsl:variable name="noteString">
					<xsl:for-each select="*">
						<xsl:value-of select="concat(.,', ')"/>
					</xsl:for-each>
				</xsl:variable>
				<marc:subfield code="a">
					<xsl:value-of select="substring($noteString, 1,string-length($noteString)-2)"/>
				</marc:subfield>
				<!--<xsl:call-template name="relatedItem76X-78X"/>-->
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	<!-- 1/04 fix -->
	<!--<xsl:template match="internetMediaType">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">856</xsl:with-param>
			<xsl:with-param name="ind2">2</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="q">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:with-param>
		</xsl:call-template>		
	</xsl:template>	-->

	<xsl:template name="mediaType">
		<xsl:if test="../physicalDescription/internetMediaType">
			<marc:subfield code="q">
				<xsl:value-of select="../physicalDescription/internetMediaType"/>
			</marc:subfield>
		</xsl:if>
	</xsl:template>	
	
	<xsl:template name="form">
		<xsl:if test="../physicalDescription/form[@authority='gmd']">
			<marc:subfield code="h">
				<xsl:value-of select="../physicalDescription/form[@authority='gmd']"/>
			</marc:subfield>
		</xsl:if>
	</xsl:template>	
	
	<xsl:template match="relatedItem/identifier[@type='uri']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">856</xsl:with-param>
			<xsl:with-param name="ind2">2</xsl:with-param>
			<xsl:with-param name="subfields">
				<marc:subfield code="u">
					<xsl:value-of select="."/>
				</marc:subfield>
				<xsl:call-template name="mediaType"/>
			</xsl:with-param>
		</xsl:call-template>		
	</xsl:template>

	<xsl:template match="relatedItem[@type='series']">
		<!-- v3 build series type -->
			<xsl:for-each select="titleInfo">
				<xsl:call-template name="datafield">
					<xsl:with-param name="tag">440</xsl:with-param>					
					<xsl:with-param name="subfields">
						<xsl:call-template name="titleInfo"/>
					</xsl:with-param>
				</xsl:call-template>					
			</xsl:for-each>
			<xsl:for-each select="name">
				<xsl:call-template name="datafield">
					<xsl:with-param name="tag">
						<xsl:choose>
							<xsl:when test="@type='personal'">800</xsl:when>
							<xsl:when test="@type='corporate'">810</xsl:when>
							<xsl:when test="@type='conference'">811</xsl:when>
						</xsl:choose>
					</xsl:with-param>
					<xsl:with-param name="subfields">
						<marc:subfield code="a">
							<xsl:value-of select="namePart"/>
						</marc:subfield>
						<xsl:if test="@type='corporate'">
							<xsl:for-each select="namePart[position()>1]">
								<marc:subfield code="b">
									<xsl:value-of select="."/>
								</marc:subfield>
							</xsl:for-each>
						</xsl:if>
						<xsl:if test="@type='personal'">
							<xsl:for-each select="namePart[@type='termsOfAddress']">
								<marc:subfield code="c">
									<xsl:value-of select="."/>
								</marc:subfield>
							</xsl:for-each>								
							<xsl:for-each select="namePart[@type='date']">
								<!-- v3 namepart/date was $a; fixed to $d -->
								<marc:subfield code="d">
									<xsl:value-of select="."/>
								</marc:subfield>
							</xsl:for-each>
						</xsl:if>
						<!-- v3 role -->
						<xsl:if test="@type!='conference'">
							<xsl:for-each select="role/roleTerm[@type='text']">
								<marc:subfield code="e">
									<xsl:value-of select="."/>
								</marc:subfield>
							</xsl:for-each>
						</xsl:if>
						<xsl:for-each select="role/roleTerm[@type='code']">
							<marc:subfield code="4">
								<xsl:value-of select="."/>
							</marc:subfield>
						</xsl:for-each>
					</xsl:with-param>
				</xsl:call-template>			
			</xsl:for-each>
	</xsl:template>

	<xsl:template match="relatedItem[not(@type)]">
	<!-- v3 was type="related" -->
		   	<xsl:call-template name="datafield">
			<xsl:with-param name="tag">787</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>			
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="relatedItem[@type='preceding']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">780</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="ind2">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="relatedItem[@type='succeeding']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">785</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="ind2">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
	
	<xsl:template match="relatedItem[@type='otherVersion']">	
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">775</xsl:with-param>			
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="relatedItem[@type='otherFormat']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">776</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="relatedItem[@type='original']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">534</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="relatedItem[@type='host']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">773</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<!-- v3 displaylabel -->
				<xsl:for-each select="@displaylabel">
					<marc:subfield code="3">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<!-- v3 part/text -->
				<xsl:for-each select="part/text">
					<marc:subfield code="g">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
				<!-- v3 sici part/detail 773$q 	1:2:3<4-->			
				<xsl:if test="part/detail">
					<xsl:variable name="parts">				
						<xsl:for-each select="part/detail">
							<xsl:value-of select="concat(number,':')"/>
						</xsl:for-each>
					</xsl:variable>					
					<marc:subfield code="q">						
						<xsl:value-of select="concat(substring($parts,1,string-length($parts)-1),'&lt;',part/extent/start)"/>
					</marc:subfield>
				</xsl:if>
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>		
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="relatedItem[@type='constituent']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">774</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>

	<!-- v3 changed this to not@type -->
	<!--<xsl:template match="relatedItem[@type='related']">
		<xsl:call-template name="datafield">
			<xsl:with-param name="tag">787</xsl:with-param>
			<xsl:with-param name="ind1">0</xsl:with-param>
			<xsl:with-param name="subfields">
				<xsl:call-template name="relatedItem76X-78X"/>
			</xsl:with-param>
		</xsl:call-template>
	</xsl:template>
-->
	<xsl:template name="relatedItem76X-78X">
		<xsl:for-each select="titleInfo">
			<xsl:for-each select="title">
				<xsl:choose>
					<xsl:when test="not(ancestor-or-self::titleInfo/@type)">
						<marc:subfield code="t">
							<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:when>
					<xsl:when test="ancestor-or-self::titleInfo/@type='uniform'">
						<marc:subfield code="s">
							<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:when>
					<xsl:when test="ancestor-or-self::titleInfo/@type='abbreviated'">
						<marc:subfield code="p">
						<xsl:value-of select="."/>
						</marc:subfield>
					</xsl:when>
				</xsl:choose>			
			</xsl:for-each>	
			<xsl:for-each select="partNumber">
				<marc:subfield code="g">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:for-each>	
			<xsl:for-each select="partName">
				<marc:subfield code="g">
					<xsl:value-of select="."/>
				</marc:subfield>
			</xsl:for-each>	
		</xsl:for-each>		
		<!-- 1/04 fix -->
		<xsl:call-template name="relatedItemNames"/>
		<!-- 1/04 fix -->
		<xsl:choose>		
			<xsl:when test="@type='original'"><!-- 534 -->
				<xsl:for-each select="physicalDescription/extent">
					<marc:subfield code="e">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:when>
			<xsl:when test="@type!='original'">
				<xsl:for-each select="physicalDescription/extent">
					<marc:subfield code="h">
						<xsl:value-of select="."/>
					</marc:subfield>
				</xsl:for-each>
			</xsl:when>
		</xsl:choose>
		<!-- v3 displaylabel -->
		<xsl:for-each select="@displayLabel">
			<marc:subfield code="i">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>		
		<xsl:for-each select="note">
			<marc:subfield code="n">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>				
		<xsl:for-each select="identifier[not(@type)]">
			<marc:subfield code="o">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
		<xsl:for-each select="identifier[@type='issn']">
			<marc:subfield code="x">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
		<xsl:for-each select="identifier[@type='isbn']">
			<marc:subfield code="z">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
		<xsl:for-each select="identifier[@type='local']">
			<marc:subfield code="w">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>
		<xsl:for-each select="note">
			<marc:subfield code="n">
				<xsl:value-of select="."/>
			</marc:subfield>
		</xsl:for-each>				
	</xsl:template>
<!-- v3 not used?
		<xsl:variable name="leader06">
			<xsl:choose>
				<xsl:when test="typeOfResource='text'">
					<xsl:choose>
						<xsl:when test="@manuscript='yes'">t</xsl:when>
						<xsl:otherwise>a</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:when test="typeOfResource='cartographic'">
					<xsl:choose>
						<xsl:when test="@manuscript='yes'">f</xsl:when>
						<xsl:otherwise>e</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:when test="typeOfResource='notated music'">
					<xsl:choose>
						<xsl:when test="@manuscript='yes'">d</xsl:when>
						<xsl:otherwise>c</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:when test="typeOfResource='sound recording'">j</xsl:when>
				<xsl:when test="typeOfResource='still image'">k</xsl:when>
				<xsl:when test="typeOfResource='moving image'">g</xsl:when>
				<xsl:when test="typeOfResource='three dimensional object'">r</xsl:when>
				<xsl:when test="typeOfResource='software, multimedia'">m</xsl:when>
				<xsl:when test="typeOfResource='mixed material'">p</xsl:when>
			</xsl:choose>
		</xsl:variable>
-->
</xsl:stylesheet>
