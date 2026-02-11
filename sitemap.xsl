<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9">
    <xsl:output method="html" indent="yes"/>
    <xsl:template match="/">
        <html lang="ko">
        <head>
            <meta charset="UTF-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <title>사이트맵 - 두손기획인쇄</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Malgun Gothic', 'Apple SD Gothic Neo', sans-serif;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    padding: 20px;
                    line-height: 1.6;
                }
                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                    overflow: hidden;
                }
                .header {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    padding: 30px;
                    text-align: center;
                }
                .header h1 {
                    font-size: 28px;
                    margin-bottom: 8px;
                }
                .header p {
                    opacity: 0.9;
                    font-size: 14px;
                }
                .stats {
                    display: flex;
                    justify-content: center;
                    gap: 30px;
                    padding: 20px;
                    background: #f8f9fa;
                    border-bottom: 1px solid #e9ecef;
                }
                .stat {
                    text-align: center;
                }
                .stat-label {
                    font-size: 12px;
                    color: #6c757d;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }
                .stat-value {
                    font-size: 24px;
                    font-weight: bold;
                    color: #667eea;
                }
                .content {
                    padding: 20px;
                }
                .section {
                    margin-bottom: 30px;
                }
                .section-title {
                    font-size: 16px;
                    font-weight: bold;
                    color: #495057;
                    margin-bottom: 15px;
                    padding-bottom: 8px;
                    border-bottom: 2px solid #667eea;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                }
                th {
                    background: #f8f9fa;
                    padding: 12px;
                    text-align: left;
                    font-weight: 600;
                    color: #495057;
                    border-bottom: 2px solid #dee2e6;
                }
                td {
                    padding: 12px;
                    border-bottom: 1px solid #e9ecef;
                }
                tr:hover {
                    background: #f8f9fa;
                }
                .priority-high { color: #28a745; font-weight: bold; }
                .priority-medium { color: #ffc107; font-weight: bold; }
                .priority-low { color: #6c757d; }
                .changefreq {
                    display: inline-block;
                    padding: 3px 10px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: 600;
                }
                .freq-always { background: #d4edda; color: #155724; }
                .freq-daily { background: #cce5ff; color: #004085; }
                .freq-weekly { background: #e7f5ff; color: #0056b3; }
                .freq-monthly { background: #fff3cd; color: #856404; }
                .freq-yearly { background: #e2e3e5; color: #383d41; }
                .url {
                    color: #667eea;
                    text-decoration: none;
                    word-break: break-all;
                }
                .url:hover {
                    text-decoration: underline;
                }
                .footer {
                    text-align: center;
                    padding: 20px;
                    color: #6c757d;
                    font-size: 12px;
                    background: #f8f9fa;
                }
                @media (max-width: 768px) {
                    .stats { flex-direction: column; gap: 10px; }
                    table { font-size: 12px; }
                    th, td { padding: 8px 4px; }
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🗺️ 사이트맵</h1>
                    <p>두손기획인쇄 (dsp114.com) - Google Search Console용</p>
                </div>

                <div class="stats">
                    <div class="stat">
                        <div class="stat-label">총 페이지</div>
                        <div class="stat-value"><xsl:value-of select="count(//s:url)"/></div>
                    </div>
                    <div class="stat">
                        <div class="stat-label">마지막 업데이트</div>
                        <div class="stat-value" style="font-size: 14px; line-height: 28px;">
                            <xsl:value-of select="//s:url[1]/s:lastmod"/>
                        </div>
                    </div>
                </div>

                <div class="content">
                    <div class="section">
                        <div class="section-title">📄 페이지 목록</div>
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:50%">URL</th>
                                    <th style="width:15%">우선순위</th>
                                    <th style="width:15%">변경 빈도</th>
                                    <th style="width:20%">마지막 수정</th>
                                </tr>
                            </thead>
                            <tbody>
                                <xsl:for-each select="//s:url">
                                    <xsl:sort select="s:priority" order="descending"/>
                                    <tr>
                                        <td>
                                            <a class="url" target="_blank">
                                                <xsl:attribute name="href">
                                                    <xsl:value-of select="s:loc"/>
                                                </xsl:attribute>
                                                <xsl:value-of select="s:loc"/>
                                            </a>
                                        </td>
                                        <td>
                                            <xsl:attribute name="class">
                                                <xsl:choose>
                                                    <xsl:when test="s:priority &gt;= 0.8">priority-high</xsl:when>
                                                    <xsl:when test="s:priority &gt;= 0.5">priority-medium</xsl:when>
                                                    <xsl:otherwise>priority-low</xsl:otherwise>
                                                </xsl:choose>
                                            </xsl:attribute>
                                            <xsl:value-of select="s:priority"/>
                                        </td>
                                        <td>
                                            <span class="changefreq">
                                                <xsl:attribute name="class">
                                                    changefreq freq-<xsl:value-of select="s:changefreq"/>
                                                </xsl:attribute>
                                                <xsl:choose>
                                                    <xsl:when test="s:changefreq = 'always'">항상</xsl:when>
                                                    <xsl:when test="s:changefreq = 'hourly'">시간별</xsl:when>
                                                    <xsl:when test="s:changefreq = 'daily'">일간</xsl:when>
                                                    <xsl:when test="s:changefreq = 'weekly'">주간</xsl:when>
                                                    <xsl:when test="s:changefreq = 'monthly'">월간</xsl:when>
                                                    <xsl:when test="s:changefreq = 'yearly'">연간</xsl:when>
                                                    <xsl:otherwise><xsl:value-of select="s:changefreq"/></xsl:otherwise>
                                                </xsl:choose>
                                            </span>
                                        </td>
                                        <td style="font-size:12px; color:#6c757d;">
                                            <xsl:value-of select="s:lastmod"/>
                                        </td>
                                    </tr>
                                </xsl:for-each>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="footer">
                    <p>이 사이트맵은 Google Search Console 등록용입니다.</p>
                    <p style="margin-top:5px;">
                        <a href="https://search.google.com/search-console" target="_blank" style="color:#667eea;">Google Search Console</a>에
                        <strong style="color:#667eea;">sitemap.xml</strong>을 제출하세요.
                    </p>
                </div>
            </div>
        </body>
        </html>
    </xsl:template>
</xsl:stylesheet>
