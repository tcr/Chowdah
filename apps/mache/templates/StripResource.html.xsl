<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
 xmlns:php="http://php.net/xsl">
  <xsl:import href="Mache.html.xsl" />

  <xsl:template match="/strip">
    <xsl:choose>
      <xsl:when test="@edit">
        <h2>Edit Strip</h2>
        
        <form method="post" action="{id}">
          <dl>
            <dt><label>Title:</label></dt>
              <dd><input type="text" value="{title}" name="title" /></dd>
            <dt><label>Content:</label></dt>
              <dd>
                <textarea rows="15" cols="50" name="content" id="content"><xsl:value-of select="content" /></textarea>
              </dd>
            <dt><label>Tags:</label></dt>
              <dd>
                <input type="text" name="tags">
                  <xsl:attribute name="value">
                    <xsl:value-of select="tags/tag[1]" />
                    <xsl:for-each select="tags/tag[position()&gt;1]"><xsl:text> </xsl:text><xsl:value-of select="." /></xsl:for-each>
                  </xsl:attribute>
                </input>
              </dd>
          </dl>
          <p id="actions"><input type="submit" value="Save" />&#160;<input type="reset" value="Reset" />&#160;<a href="{id}">[Cancel]</a></p>
        </form>
      </xsl:when>
      <xsl:when test="@delete">
        <h2>Delete Strip</h2>
        
        <form method="post" action="{id}">
	  <input type="hidden" name="method" value="DELETE" />
	  <p>Are you sure you want to delete this strip?</p>
          <p id="actions"><input type="submit" value="Delete" />&#160;<a href="{id}">[Cancel]</a></p>
	</form>
      </xsl:when>
      
      <xsl:otherwise>
        <h2><xsl:value-of select="title" /></h2>

        <div id="strip-content"><xsl:copy-of select="php:function('Strip::parseMarkup', string(content), /)/node()" /></div>
    
        <h3>Tags</h3>
        <ul>
          <xsl:for-each select="tags/tag">
            <li><a href=".?tags={.}"><xsl:value-of select="." /></a></li>
          </xsl:for-each>
        </ul>
        
        <p id="actions"><a href="{id}?edit">[Edit]</a>&#160;<a href="{id}?delete">[Delete]</a></p>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>