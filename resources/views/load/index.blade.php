<!DOCTYPE html>
<html
    lang="en"
    class="light-style layout-navbar-fixed layout-menu-fixed layout-compact"
    dir="ltr"
    data-theme="theme-default"
    data-assets-path="{{url('assets')}}/"
    data-template="vertical-menu-template"
    data-style="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <h1>LISTA DE ELEMETOS OYENTES</h1>
    <button class="listen_calendar" sede="34">Boton 1</button>
    <button class="listen_calendar" sede="4">Boton 2</button>
    <button class="listen_calendar" sede="5">Boton 3</button>
    <h1>CONTENEDOR DEL IFRAME</h1>
    <div id="loadCalendar"></div>
    <script redirect="https://www.google.com/" url="{{url('')}}" dom_content="#loadCalendar" dom_listener=".listen_calendar" src="{{url('assets/js/loadclub.js')}}"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }
    </style>
</body>

</html>