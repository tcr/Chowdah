<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
 xmlns:php="http://php.net/xsl">
  <xsl:import href="Quiki.html.xsl" />
  
  <!-- page title -->
  <xsl:param name="title" select="/page/title" />

  <xsl:template match="/page">
    <xsl:choose>
      <xsl:when test="@edit">
        <h2>Edit Page</h2>
        <p class="instruction">To format your content, you can use <a href="http://textile.thresholdstate.com/">Textile</a> markup.</p>
        
        <form method="post" action="{title}">
          <input type="hidden" name="request_method" value="PUT" />
          <dl>
            <dt><label>Title:</label></dt>
              <dd><input type="text" value="{title}" name="title" /></dd>
            <dt><label>Content:</label></dt>
              <dd>
                <textarea rows="15" cols="50" name="content" id="content"><xsl:value-of select="content" /></textarea>
              </dd>
            <dt><label>Tags:</label></dt>
              <dd>
                <p class="instruction">Tags should be separated by a space (" ").</p>
                <input type="text" name="tags">
                  <xsl:attribute name="value">
                    <xsl:value-of select="tags/tag[1]" />
                    <xsl:for-each select="tags/tag[position()&gt;1]"><xsl:text> </xsl:text><xsl:value-of select="." /></xsl:for-each>
                  </xsl:attribute>
                </input>
              </dd>
          </dl>
          <p class="actions"><input type="submit" value="Save" />&#160;<input type="reset" value="Reset" />&#160;<a href="{title}">Cancel</a></p>
        </form>
      </xsl:when>
      <xsl:when test="@delete">
        <h2>Delete Page</h2>
        
        <form method="post" action="{title}">
	  <input type="hidden" name="request_method" value="DELETE" />
	  <p>Are you sure you want to delete this page?</p>
          <p class="actions"><input type="submit" value="Delete" />&#160;<a href="{title}">Cancel</a></p>
	</form>
      </xsl:when>
      
      <xsl:otherwise>
        <h2><xsl:value-of select="title" /></h2>

        <div id="page-content"><xsl:copy-of select="php:function('Page::parseMarkup', string(content), /)/node()" /></div>
        
        <xsl:if test="tags/tag">
          <p id="tags"><small><strong>Tagged:</strong>
            <xsl:for-each select="tags/tag">
              <xsl:text> </xsl:text><a href=".?tags={.}"><xsl:value-of select="." /></a>
            </xsl:for-each></small>
          </p>
        </xsl:if>
        
        <xsl:if test="$user"><p class="actions"><a href="{title}?edit">Edit</a>&#160;<a href="{title}?delete">Delete</a></p></xsl:if>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>