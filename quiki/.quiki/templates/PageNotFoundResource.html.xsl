<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0"
 xmlns:php="http://php.net/xsl">
  <xsl:import href="Quiki.html.xsl" />
  
  <!-- page title -->
  <xsl:param name="title" select="'Page not found'" />

  <xsl:template match="/page">
    <xsl:choose>
      <xsl:when test="@edit">
        <h2>Creating Page "<xsl:value-of select="title" />"</h2>
        
        <form method="post" action="{title}">
          <input type="hidden" name="request_method" value="PUT" />
          <dl>
            <dt><label>Content:</label></dt>
              <dd>
                <textarea rows="15" cols="50" name="content" id="content" />
              </dd>
            <dt><label>Tags:</label></dt>
              <dd>
                <input type="text" name="tags" />
              </dd>
          </dl>
          <p class="actions"><input type="submit" value="Save" />&#160;<input type="reset" value="Reset" />&#160;<a href="{title}">Cancel</a></p>
        </form>
      </xsl:when>
      
      <xsl:otherwise>
        <h2>Page not found</h2>

        <div id="page-content" class="notice">
          <p>A page for "<xsl:value-of select="title" />" does not currently exist. Would you like to <a href="{title}?edit">create</a> it?</p>
        </div>
        
        <p class="actions"><a href="{title}?edit">Create</a></p>
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>