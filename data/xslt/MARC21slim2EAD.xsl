<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:marc="http://www.loc.gov/MARC21/slim"  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="marc">
    <xsl:import href="MARC21slimUtils.xsl"/>
    <xsl:output method="xml" encoding="UTF-8" indent="yes"/>

<xsl:template match="/">
        <?filetitle ?>
        <ead>
            <!--The following section is header information for web display of the finding aid-->
            <eadheader langencoding="iso639-2b"
                scriptencoding="iso15924" relatedencoding="Dublin Core"
                repositoryencoding="iso15511" countryencoding="iso3166-1"
                dateencoding="iso8601" id="a0">
                <eadid countrycode="us" encodinganalog="Identifier"><?xm-replace_text {Enter the unique identifier for this finding 
aid}?></eadid>

                <filedesc>
                        <titlestmt>
                        <titleproper encodinganalog="Title">
                        <xsl:text>Guide to the </xsl:text>
				<xsl:for-each select="marc:record">
	                        <xsl:for-each select="marc:datafield[@tag=245]">
			                  <xsl:value-of select="@code='a'" />
                  	      </xsl:for-each>
				</xsl:for-each>
                        <xsl:text> Papers</xsl:text>
                            <date encodinganalog="Date">
                            <xsl:for-each select="marc:record">
					  <xsl:for-each select="marc:datafield[@tag=245]">
                                    <xsl:value-of select="@code='a'" />
                                </xsl:for-each>
                            </xsl:for-each>
                            </date>
                        </titleproper>

                        <author encodinganalog="Creator">Finding aid prepared by MarcEdit</author>

                        </titlestmt>
                        <publicationstmt>
                        <publisher encodinganalog="Publisher">Oregon State University Archives</publisher>

                        <address>
                            <addressline>Corvallis, Oregon  97402</addressline>
                        </address>
                        <date encodinganalog="Date"></date>

                        </publicationstmt>
                        <notestmt>
                        <note encodinganalog="Description">
                            <p>Funding for encoding this finding aid was provided through a grant
                                    awarded by the National Endowment for the Humanities.</p>
                        </note>
                        </notestmt>
                </filedesc>


                <profiledesc>
                        <creation encodinganalog="Description">Finding aid encoded by MarcEdit
                        <date era="ce" calendar="gregorian" normal="2003">2003</date></creation>

                        <langusage>Finding aid written in
                        <language langcode="eng" encodinganalog="Language"
                        scriptcode="Latn">English.</language></langusage>
                </profiledesc>
            </eadheader>
                <xsl:apply-templates/>
        </ead>
    </xsl:template>

    <xsl:template match="marc:record">
        <archdesc level="collection" type="inventory" relatedencoding="MARC21">
            <did id="a1">

            <repository encodinganalog="852">
              <corpname encodinganalog="852$a">Oregon State University<subarea encodinganalog="852$b">Archives</subarea></corpname>

              <address>
                   <addressline>Oregon State University Libraries</addressline>
                   <addressline>121 Valley Library</addressline>
                   <addressline>Corvallis, Oregon  97402</addressline>
                   <addressline>Phone: (541) 737-2165 </addressline>
                   <addressline>Fax: (541) 737-0541</addressline>
                   <addressline>http://osulibrary.orst.edu/archives/</addressline>

              </address> </repository>
            <unitid encodinganalog="099" countrycode="us"><?xm-replace_text {Enter the unique identifier for this finding 
aid}?></unitid>

            <xsl:for-each select="marc:datafield[@tag=100]">
                <origination>
                    <persname encodinganalog="100" source="lcnaf" role="creator"
                        rules="AACR2R"><xsl:value-of select="." />
                    </persname>
                </origination>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=245]">
                <unittitle encodinganalog="245$a" type="collection">
                    <xsl:value-of select="marc:subfield[@code='a']"/>
                </unittitle>

                <xsl:if test="marc:subfield[@code='f']!=''">
                    <unitdate type="inclusive" encodinganalog="245$f">
                        <xsl:value-of select="marc:subfield[@code='f']" />
                    </unitdate>
                </xsl:if>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=300]">
                <physdesc>

                    <xsl:if test="marc:subfield[@code='a']!=''">
                        <extent encodinganalog="300$a">
                            <xsl:value-of select="marc:subfield[@code='a']" />
                        </extent>
                    </xsl:if>

                    <xsl:if test="marc:subfield[@code='b']!=''">
                        <physfacet encodinganalog="300$b">
                            <xsl:value-of select="marc:subfield[@code='b']" />
                        </physfacet>
                    </xsl:if>

                    <xsl:if test="marc:subfield[@code='c']!=''">
                        <dimensions encodinganalog="300$c">
                            <xsl:value-of select="marc:subfield[@code='c']" />
                        </dimensions>
                    </xsl:if>

                </physdesc>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=254]">
                <materialspec encodinganalog="254">
                    <xsl:value-of select="." />
                </materialspec>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=520]">
                <abstract encodinganalog="520$a">
                    <xsl:value-of select="marc:subfield[@code='a']" />
                </abstract>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=852]">
                <physloc encodinganalog="852$z">
                    <xsl:value-of select="marc:subfield[@code='z']" />
                </physloc>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=546]">
                <langmaterial>
                    <language encodinganalog="546">
                        <xsl:value-of select="." />
                    </language>
                </langmaterial>
            </xsl:for-each>

          </did>

            <xsl:for-each select="marc:datafield[@tag=538]">
                <phystech encodinganalog="538">
                    <p><xsl:value-of select="." /></p>
                </phystech>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=535]">
                <originalsloc encodinganalog="535">
                    <p><xsl:value-of select="." /></p>
                </originalsloc>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=545]">
                <bioghist encodinganalog="545" id="a2">
                    <!--Use heading Historical Note for corporate history-->
                    <p><xsl:value-of select="." /></p>
                </bioghist>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=500]">
                <odd encodinganalog="500" id="a5">
                    <p><xsl:value-of select="." /></p>
                </odd>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=351]">
                <arrangement encodinganalog="351" id="a4">
                    <p><xsl:value-of select="." /></p>
                </arrangement>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=530]">
                <altformavail encodinganalog="530" id="a9">
                    <p><xsl:value-of select="." /></p>
                </altformavail>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=506]">
                <accessrestrict encodinganalog="506" id="a14">
                    <p><xsl:value-of select="." /></p>
                </accessrestrict>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=540]">
                <userestrict encodinganalog="540" id="a15">
                    <p><xsl:value-of select="." /></p>
                </userestrict>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=524]">
                <prefercite encodinganalog="524" id="a18">
                    <p><xsl:value-of select="." /></p>
                </prefercite>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=561]">
                <custodhist encodinganalog="561" id="a16">
                    <p><xsl:value-of select="." /></p>
                </custodhist>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=541]">
                <acqinfo encodinganalog="541" id="a19">
                    <p><xsl:value-of select="." /></p>
                </acqinfo>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=584]">
                <accruals encodinganalog="584" id="a10">
                    <p><xsl:value-of select="." /></p>
                </accruals>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=538]">
                <processinfo encodinganalog="583" id="a20">
                    <p><xsl:value-of select="." /></p>
                </processinfo>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=544]">
                <separatedmaterial encodinganalog="544 0" id="a7">
                    <p><xsl:value-of select="." /></p>
                </separatedmaterial>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=581]">
                <bibliography encodinganalog="581" id="a11">
                    <p><xsl:value-of select="." /></p>
                </bibliography>
            </xsl:for-each>

            <xsl:for-each select = "marc:datafield[@tag=555]">
                <otherfindaid encodinganalog="555" id="a8">
                    <p><xsl:value-of select="." /></p>
                </otherfindaid>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=544]">
                <relatedmaterial encodinganalog="544 1" id="a6">
                    <p><xsl:value-of select="." /></p>
                </relatedmaterial>
            </xsl:for-each>

            <controlaccess id="a12">
                <p>This collection is indexed under the following headings in the online
                    catalog. Researchers desiring materials about related topics, persons, or
                    places should search the catalog using these headings.</p>

                <xsl:for-each select="marc:datafield[@tag=600]">
                    <controlaccess>
                        <famname source="lcnaf" rules="aacr2r" role="subject"
                            encodinganalog="600">
                            <xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
                            </xsl:for-each>
                        </famname>
                    </controlaccess>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=610]">
                    <controlaccess>
                        <corpname source="lcnaf" rules="aacr2r" role="subject"
                            encodinganalog="610">
                            <xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
							</xsl:for-each>
                        </corpname>
                    </controlaccess>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=651]">
                    <controlaccess>
                        <geogname source="lcsh" rules="scm" role="subject"
                            encodinganalog="651">
                            <xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
							</xsl:for-each>
                        </geogname>
                    </controlaccess>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=650]">
                    <controlaccess>
                        <subject source="lcsh" encodinganalog="650" rules="scm">
							<xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</subject>
                    </controlaccess>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=655]">
                    <controlaccess>
                        <genreform source="lcsh" encodinganalog="655">
							<xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</genreform>
                    </controlaccess>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=656]">
                    <controlaccess>
                        <occupation source="lcsh" encodinganalog="656">
							<xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
							</xsl:for-each>	
						</occupation>
                    </controlaccess>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=657]">
                    <controlaccess>
                        <function source="aat" encodinganalog="657">
							<xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</function>
                    </controlaccess>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=630]">
                    <controlaccess>
                        <title encodinganalog="630" source="lcnaf" rules="aacr2r">
							<xsl:for-each select="marc:subfield">
								<xsl:value-of select="." />
								<xsl:if test="position()!=last()">
									<xsl:text> -- </xsl:text>
								</xsl:if>
							</xsl:for-each>
						</title>
                    </controlaccess>
                </xsl:for-each>
       </controlaccess>

        <!--This is left empty on purpose-->
        <dsc type="combined" id="a23">
                <p>The following section contains a detailed listing of the materials in
                the collection.</p>
                <c01 level="series">
                <did>
                    <unitid encodinganalog="099"><?xm-replace_text {Unique identifying number or code}?></unitid>

                    <container type="box"><?xm-replace_text {Enter container number if applicable at this level}?></container>

                    <origination><?xm-replace_text {Use persname or corpname to give name of creator if different from parent-level creator}?></origination>

                    <unittitle encodinganalog="245$a"><?xm-replace_text {Title}?></unittitle>

                    <unitdate type="inclusive" encodinganalog="245$f"><?xm-replace_text {Date or range of dates, or "undated"; repeatable}?></unitdate>

                    <physdesc> <extent encodinganalog="300$a"><?xm-replace_text {Quantity}?></extent>
                            <physfacet encodinganalog="300$b"><?xm-replace_text {Appearance, materials, or techniques if applicable}?></physfacet>
                            <dimensions encodinganalog="300$c"><?xm-replace_text {Dimensions if applicable}?></dimensions>
                    </physdesc>
                </did><phystech>
                <p><?xm-replace_text {Physical condition or equipment required if applicable}?></p></phystech>

                <scopecontent encodinganalog="520">
                    <p><?xm-replace_text {Brief description of component being described, such as series}?>
                    </p>
                </scopecontent>
                <c02 level="subseries">
                    <did>
                            <unitid encodinganalog="099"><?xm-replace_text {Unique identifying number or code}?></unitid>

                            <container type="box-folder"><?xm-replace_text {Container number}?></container>

                            <origination><?xm-replace_text {Use persname or corpname to give name of creator if different from parent-level creator}?></origination>

                            <unittitle encodinganalog="245$a"><?xm-replace_text {Title}?></unittitle>

                            <unitdate type="inclusive" encodinganalog="245$f"><?xm-replace_text {Date or range of dates, or "undated"; repeatable}?></unitdate>

                            <physdesc> <extent encodinganalog="300$a"><?xm-replace_text {Quantity if applicable}?></extent>
                            <physfacet encodinganalog="300$b"><?xm-replace_text {Appearance, materials, or techniques if applicable}?></physfacet>
                            <dimensions encodinganalog="300$c"><?xm-replace_text {Dimensions if applicable}?></dimensions>
                            </physdesc>
                    </did><phystech>
                    <p><?xm-replace_text {Physical condition or equipment required if applicable}?></p></phystech>

                    <scopecontent encodinganalog="520">
                            <p><?xm-replace_text {Brief description of component being described}?>
                            </p>
                    </scopecontent>
                </c02>
                </c01>
            </dsc>
        </archdesc>
    </xsl:template>
</xsl:stylesheet>
