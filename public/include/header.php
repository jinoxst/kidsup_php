<!doctype html>
<head>
	<meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>KidsUp</title>
    <link rel='stylesheet' href='/css/style.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/jquery-ui.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/theme.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/common.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/dropzone.min.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/jquery.fancybox.css?v=2.1.5' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/jquery.contextMenu.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/fullcalendar.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/tooltipster.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/themes/tooltipster-shadow.css' type='text/css' media='all' />
    <link rel='stylesheet' href='/css/switchery.min.css' type='text/css' media='all' />
    <link rel="icon" href="/img/favicon.ico" type="image/x-icon">
    <link rel="shortcut icon" href="/img/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="57x57" href="/img/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/img/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/img/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/img/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/img/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/img/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/img/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/img/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192"  href="/img/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="/img/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon-16x16.png">
    <link rel="manifest" href="/include/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/img/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <script src="/js/jquery.js"></script>
    <script src="/js/jquery-ui.min.js"></script>
    <script src="/js/common.js"></script>
    <script src="/js/dropzone.min.js"></script>
    <script src="/js/history.adapter.jquery.js"></script>
    <script src="/js/history.js"></script>
    <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>
    <script src="/js/gmaps.min.js"></script>
    <script src="/js/jquery.fancybox.js?v=2.1.5"></script>
    <script src="/js/jquery.mousewheel-3.0.6.pack.js"></script>
    <script src="/js/jquery.contextMenu.js"></script>
    <script src="/js/moment.min.js"></script>
    <script src="/js/fullcalendar.min.js"></script>
    <script src="/js/lang-all.js"></script>
    <script src="/js/ui/i18n/datepicker-en-GB.js"></script>
    <script src="/js/ui/i18n/datepicker-ja.js"></script>
    <script src="/js/ui/i18n/datepicker-ko.js"></script>
    <script src="/js/jquery.tooltipster.js"></script>
    <script src="/js/switchery.min.js"></script>
</head>
<body>
<div id="common_alert" class="common_dialog">
    <div id="common_alert_contents"></div>
    <div style='margin-top:10px;text-align:center;'>
        <button id="close_common_alert">閉じる</button>
    </div>
</div>
<div id="yes_no_common_alert" class="common_dialog"></div>
<div id="one_button_common_alert" class="common_dialog"></div>
<div id="info-message" class="ui-widget" style="display:none;z-index:1;">
    <div class="ui-state-highlight ui-corner-all" style="padding: 0 .7em;">
        <div style="margin:10px 0 10px 0"><span class="ui-icon ui-icon-info" style="float: left; margin-right: .3em;"></span><div id="_contents_"></div></div>
    </div>
</div>
<div id="error-message" class="ui-widget" style="display:none;z-index:1;">
    <div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
        <div style="margin:10px 0 10px 0"><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span><div id="_contents_"></div></div>
    </div>
</div>
<div id="tmp-work-layer"></div>