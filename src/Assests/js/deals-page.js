var dealClipboard = new Clipboard('.js-deal');
console.log(dealClipboard);
dealClipboard.on('success', function (e) {
    var couponId = $(e.trigger).attr('data-id');
    e.clearSelection();
    var c = window.location.protocol + "//" + window.location.hostname + (window.location.port ? ":" + window.location.port : "");
    var d = document.URL;
    if (d.indexOf('/deals') == -1 && d.indexOf('/deal') == -1) {
        d = c + "/deals";
    }
    if (d.indexOf('/deal') !== -1 ) {
        d = c + "/deal";
    }

    var f = "/c/";
    window.open(c + "/go-deal/" + couponId, '_blank');
    if (d.indexOf('?') != -1) {
        var g = d.split('?');
        if (g[0].slice(-1) == '/') {
            g[0] = g[0].slice(0, -1);
        }

        if (g[0].indexOf('/c/') == -1) {
            window.location.href = g[0] + '/c/' + couponId + '?' + g[1];
        } else {
            var k = g[0].split('/c/');
            window.location.href = k[0] + '/c/' + couponId + '?' + g[1];
        }
    } else {
        var b = d.split(f)[0] + "/";
        var b = (b.replace(/(\/\/)$/, "/"));
        window.location.href = b + "c/" + couponId;
    }
});
dealClipboard.on('error', function (e) {
    console.error('Action:', e.action);
});