<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
 xmlns:php="http://php.net/xsl">
  <xsl:import href="Mache.html.xsl" />

  <xsl:template match="/root">
    <xsl:choose>
      <xsl:when test="@edit">
        <h2>Edit Strip</h2>
        
        <form method="post" action=".">
          <dl>
            <dt><label>Title:</label></dt>
              <dd><input type="text" value="{title}" name="title" /></dd>
            <dt><label>Content:</label></dt>
              <dd>
                <textarea rows="15" cols="50" name="content" id="content"><xsl:value-of select="content" /></textarea>
              </dd>
          </dl>
          <p class="actions"><input type="submit" value="Save" />&#160;<input type="reset" value="Reset" />&#160;<a href=".">Cancel</a></p>
        </form>
      </xsl:when>
      <xsl:otherwise>
        <h2><xsl:value-of select="title" /></h2>

        <div id="strip-content"><xsl:copy-of select="php:function('Strip::parseMarkup', string(content), /)/node()" /></div>
        
        <p class="actions"><a href="{id}?edit">Edit</a></p>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>