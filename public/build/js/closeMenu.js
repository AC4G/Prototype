$(document).mouseup(function(e) {
    $("details").each(function() {
        const container = $(this);
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.attr("open", false);
        }
    });
});