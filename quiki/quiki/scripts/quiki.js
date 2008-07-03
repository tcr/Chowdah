$(document).ready(function () {
	// replace action links with buttons
	$('.actions a').each(function (index, link) {
		$(link).replaceWith('<input type="button" value="' + $(link).text() + '" onclick="document.location.href=\'' + link.href + '\'">');
	});
});