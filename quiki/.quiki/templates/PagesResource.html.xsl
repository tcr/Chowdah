<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:import href="Quiki.html.xsl" />
  
  <!-- page title -->
  <xsl:param name="title" select="'Pages'" />

  <xsl:template match="/pages">
    <xsl:choose>
      <xsl:when test="@create">
        <h2>Create Page</h2>

        <form method="post" action=".">
          <dl>
            <dt><label>Title:</label></dt>
              <dd><input type="text" name="title" /></dd>
            <dt><label>Content:</label></dt>
              <dd><textarea rows="15" cols="50" name="content" id="content" /></dd>
            <dt><label>Tags:</label></dt>
              <dd><input type="text" name="tags" /></dd>
          </dl>

          <p class="actions"><input type="submit" value="Create" />&#160;<a href=".">Cancel</a></p>
        </form>
      </xsl:when>
      <xsl:otherwise>
        <h2>Pages</h2>

        <table id="pages">
          <thead>
            <th>Title</th>
            <th>Tags</th>
          </thead>
          
          <tbody>
            <xsl:for-each select="page">
              <tr>
	        <td class="title"><strong><a href="{title}"><xsl:value-of select="title" /></a></strong></td>
                <td class="tags">
	          <xsl:for-each select="tags/tag"><xsl:text> </xsl:text><a href="?tags={.}"><xsl:value-of select="." /></a></xsl:for-each>
                </td>
              </tr>
	    </xsl:for-each>
          </tbody>
        </table>

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
          <p class="class"><input type="submit" value="Search" />&#160;<a href=".">Clear Search</a></p>
        </form>   

        <p class="actions"><a href="?create">Create</a></p>     
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>
</xsl:stylesheet>