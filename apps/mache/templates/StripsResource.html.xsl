<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:import href="Mache.html.xsl" />

  <xsl:template match="/strips">
    <xsl:choose>
      <xsl:when test="@create">
        <h2>Create Strip</h2>

        <form method="post" action=".">
          <dl>
            <dt><label>Title:</label></dt>
              <dd><input type="text" name="title" /></dd>
            <dt><label>Content:</label></dt>
              <dd><textarea rows="15" cols="50" name="content" id="content" /></dd>
            <dt><label>Tags:</label></dt>
              <dd><input type="text" name="tags" /></dd>
          </dl>

          <p><input type="submit" value="Create" />&#160;<a href=".">[Cancel]</a></p>
        </form>
      </xsl:when>
      <xsl:otherwise>
        <h2>Strips</h2>

        <ul id="strips">
          <xsl:for-each select="strip">
            <li>
	      <a href="{id}"><xsl:value-of select="title" /></a>
	      <xsl:if test="tags/tag"><xsl:text> </xsl:text>
	        <small><em>(tags:<xsl:for-each select="tags/tag"><xsl:text> </xsl:text><a href="?tags={.}"><xsl:value-of select="." /></a></xsl:for-each>)</em></small>
	      </xsl:if>
	    </li>
          </xsl:for-each>
        </ul>

        <p id="actions"><a href="?create">[Create]</a></p>

        <h3>Search</h3>
        <form method="get" action=".">
          <dl>
            <dt><label>Title:</label></dt>
              <dd><input type="text" name="title" value="{@title}" /></dd>
            <dt><label>Content:</label></dt>
              <dd><input type="text" name="content" value="{@content}" /></dd>
            <dt><label>Tags:</label></dt>
              <dd><input type="text" name="tags" value="{@tags}" /></dd>
          </dl>
          <p><input type="submit" value="Search" />&#160;<a href=".">[Clear Search]</a></p>
        </form>        
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>