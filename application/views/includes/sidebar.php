<<<<<<< 3d66e457cfc96dd56b2d25f67dc14d9fce32ea42
<div class="row">
<div id="sidebar" class="col-md-2" role="navigation">
    <h2>Menu</h2>
    <ul class="nav nav-pills nav-stacked">
      <li><?= anchor('Curso','Cursos') ?></li>
      <li><?= anchor('Disciplina','Disciplinas') ?></li>
      <li><?= anchor('Professor', 'Professores') ?></li>
      <li><?= anchor('Sala','Salas') ?></li>
      <hr>
      <li><a href="index.html"><span class="glyphicon glyphicon-log-out"></span> Sair do Sistema</a></li>
    </ul>
    
     <script>
        var active = 0;
        for (var i = 0; i < document.links.length; i++) {
            if (document.links[i].href === document.URL) {
                active = i;
            }
        }
        document.links[active].className = 'active';
    </script>
</div>
=======
<div class="row">
<div id="sidebar" class="col-md-2" role="navigation">
    <h2>Menu</h2>
    <ul class="nav nav-pills nav-stacked">
      <li><?= anchor('Curso','Cursos') ?></li>
      <li><?= anchor('Disciplina','Disciplinas') ?></li>
      <li><?= anchor('Professor', 'Professores') ?></li>
      <li><?= anchor('Sala','Salas') ?></li>
      <hr>
      <li><?= anchor('Login/logout','Sair do Sistema') ?></li>
    </ul>

     <script>
        var active = 0;
        for (var i = 0; i < document.links.length; i++) {
            if (document.links[i].href === document.URL) {
                active = i;
            }
        }
        document.links[active].className = 'active';
    </script>
</div>
>>>>>>> Criado helper auth_helper para controle de sessão
