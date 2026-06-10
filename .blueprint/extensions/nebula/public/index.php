<html>
  <head>
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Inter&display=swap&version=5737');
      @import url("https://cdn.nebula.style/icons/bootstrap/bootstrap-icons.css");
    </style>
    <script src="/extensions/nebula/libraries/errorHandler.js"></script>
  </head>
  <body>
    <div id="app"></div>
    <script>
      HandleNebulaError(`
        <code>${window.location.hostname}</code>
        utilizes Nebula to bring users an optimal experience.
        This page contains information that might be useful for
        debugging certain problems administrators might encounter
        with modifications such as Nebula.

        <div style="
          background-color: #241e2f;
          border-radius: 7px;
          padding: 10px;
          width: max-content;
        ">
          <p style="margin: 0">
            <code>
              {identifier} nebula <br>
              {name} Nebula <br>
              {author} prplwtf <br>
              {version} 2.2-2 <br>
              {random} 2265 <br>
              {timestamp} 1780991480 <br>
              {mode} local <br>
              {target} beta-2026-05 <br>
              {root} /home/bagelhost/panel-nebula
            </code>
          </p>
        </div>
      `, ` `)
    </script>
  </body>
</html>