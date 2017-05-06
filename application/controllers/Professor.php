<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 *  Essa classe é responsavel por todas regras de negócio sobre professores.
 *  @since 2017/04/03
 *  @author Yasmin Sayad
 */
class Professor extends CI_Controller {

	public function index () {
      if (verificaSessao() && verificaNivelPagina(array(1)))
        $this->cadastrar();
      else
        redirect('/');
	}
    // =========================================================================
    // ==========================CRUD de professores============================
    // =========================================================================

    /**
     * Valida os dados do forumulário de cadastro de professores.
     * Caso o formulario esteja valido, envia os dados para o modelo realizar
     * a persistencia dos dados.
     * @author Yasmin Sayad
     * @since 2017/04/03
     */
    public function cadastrar() {
      if (verificaSessao() && verificaNivelPagina(array(1))){
        // Carrega a biblioteca para validação dos dados.
        $this->load->library(array('form_validation','My_PHPMailer'));
        $this->load->helper(array('form','dropdown','date','password'));
        $this->load->model(array(
          'Professor_model',
          'Disciplina_model',
          'Competencia_model',
          'Nivel_model',
          'Contrato_model',
          'Usuario_model'
        ));
        // Definir as regras de validação para cada campo do formulário.
        $this->form_validation->set_rules('nome', 'nome do professor', array('required','min_length[5]','max_length[255]','ucwords'));
        $this->form_validation->set_rules('matricula', 'matrícula', array('required','exact_length[8]','is_unique[Usuario.matricula]','strtoupper'));
				$this->form_validation->set_rules('email','e-mail',array('required','valid_email','is_unique[Usuario.email]'));
        $this->form_validation->set_rules('nascimento', 'data de nascimento', array('callback_date_check'));
        $this->form_validation->set_rules('nivel', 'nivel', array('greater_than[0]'),array('greater_than'=>'Selecione o nível acadêmico.'));
        $this->form_validation->set_rules('contrato','contrato',array('greater_than[0]'),array('greater_than'=>'Selecione um contrato.'));

        // Definição dos delimitadores
        $this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');
        // Verifica se o formulario é valido
        if ($this->form_validation->run() == FALSE) {
          $this->session->set_flashdata('formDanger','<strong>Não foi possível cadastrar o professor, pois existe(m) erro(s) no formulário:</strong>');
          $dados['contrato']        = convert($this->Contrato_model->getAll(), TRUE);
          $dados['nivel']           = convert($this->Nivel_model->getAll(), TRUE);
          $dados['disciplinas']     = convert($this->Disciplina_model->getAll(TRUE));
          $dados['professores']     = $this->Professor_model->getAll();
          $this->load->view('includes/header', $dados);
          $this->load->view('includes/sidebar');
          $this->load->view('professores/professores');
					$this->load->view('includes/footer');
					$this->load->view('professores/js_professores');
        } else {
          // Gera uma senha para o usuário
          $senha = gerate(10);
          // Pega os dados do formulário
          $professor = array(
            'nome'            => $this->input->post("nome"),
            'matricula'       => $this->input->post('matricula'),
            'nascimento'      => brToSql($this->input->post("nascimento")),
            'email'           => $this->input->post('email'),
            'senhaLimpa'      => $senha,
            'senha'           => hash('sha256',$senha),
            'coordenador'     => ($this->input->post("coordenador") == null) ? 0 : 1,
            'idContrato'      => $this->input->post("contrato"),
            'idNivel'         => $this->input->post("nivel"),
            'disciplinas'     => $this->input->post('disciplinas[]')
          );
          $content = $this->load->view('email/novo',array('professor'=>$professor),TRUE);
          $mail = new PHPMailer();
          $mail->CharSet = 'UTF-8';
          $mail->isSMTP();
          $mail->Host = 'smtp.gmail.com';
          $mail->Port = 587;
          $mail->SMTPSecure = 'tls';
          $mail->SMTPAuth = true;
          $mail->Username = "metalcodeifsp@gmail.com";
          $mail->Password = "#metalcode2017#";
          $mail->setFrom('metalcodeifsp@gmail.com', 'Suporte Metalcode');
          $mail->addAddress($professor['email'], $professor['nome']);
          $mail->Subject = 'Novo usuário';
          $mail->msgHTML($content);
          $mail->send();
          if ($this->Usuario_model->insert($professor)) {
            $idUsuario = $this->db->insert_id(); // Pega o ID do Professor cadastrado
            $this->Professor_model->insert($idUsuario, $professor);
            foreach ($professor['disciplinas'] as $idDisciplina)
              $this->Competencia_model->insert($idUsuario,$idDisciplina);
            $this->session->set_flashdata('success','Professor cadastrado com sucesso');
          } else {
            $this->session->set_flashdata('danger','Não foi possível cadastrar o professor, tente novamente ou entre em contato com o administrador do sistema.');
          }
          redirect('Professor/cadastrar');
        }
      }else{
        redirect('/');
      }
    }

    /**
      * Valida a data no padrão BR
      * @author Caio de Freitas
      * @since 2017/04/05
      * @param Data
      * @return Retorna um boolean true caso a data sejá valida.
      */
    public function date_check ($date) {
      if ($date == null) {
        $this->form_validation->set_message('date_check','Informe a data de nascimento.');
        return FALSE;
      }
      $d = explode('/',$date);
      if (!checkdate($d[1],$d[0],$d[2])) {
        $this->form_validation->set_message('date_check','Informe uma data válida.');
        return FALSE;
      } else {
        return TRUE;
      }
    }
    /**
      * Deleta um professor.
      * @author Yasmin Sayad
      * @since  2017/04/03
      * @param $id ID do professor
      */
    public function desativar ($id) {
      if (verificaSessao() && verificaNivelPagina(array(1))){
        // Carrega os modelos necessarios
        $this->load->model(array('Professor_model'));
        if ( $this->Professor_model->disable($id) )
          $this->session->set_flashdata('success','Professor desativado com sucesso');
        else
          $this->session->set_flashdata('danger','Não foi possível desativar o professor, tente novamente ou entre em contato com o administrador do sistema.');
        redirect('Professor');
      }else{
        redirect('/');
      }
        redirect('Professor');
    }
    public function ativar ($id) {
      if (verificaSessao() && verificaNivelPagina(array(1))){
        $this->load->model('Professor_model');
        if ( $this->Professor_model->able($id) )
          $this->session->set_flashdata('success','Professor ativado com sucesso');
        else
          $this->session->set_flashdata('danger','Não foi possível ativar o professor, tente novamente ou entre em contato com o administrador do sistema.');
        redirect('Professor');
      }else{
        redirect('/');
      }
    }
    /**
      * Altera os dados do professor.
      * @author Yasmin Sayad
      * @since 2017/04/03
      * @param $id ID do professor
      */
    public function atualizar () {
      if (verificaSessao() && verificaNivelPagina(array(1))){
        // Carrega a biblioteca para validação dos dados.
        $this->load->library(array('form_validation'));
        $this->load->helper(array('form', 'dropdown', 'date'));
        $this->load->model(array(
          'Usuario_model',
          'Professor_model',
          'Disciplina_model',
          'Competencia_model',
          'Nivel_model',
          'Contrato_model'
        ));
        // Definir as regras de validação para cada campo do formulário.
        $this->form_validation->set_rules('recipient-nome', 'nome do professor', array('required','min_length[5]','max_length[255]','ucwords'));
        $this->form_validation->set_rules('recipient-matricula', 'matrícula', array('required','exact_length[8]','strtoupper'));
				$this->form_validation->set_rules('recipient-email','e-mail',array('required','valid_email'));
        $this->form_validation->set_rules('recipient-nascimento', 'data de nascimento', array('callback_date_check'));
        $this->form_validation->set_rules('recipient-nivelAcademico', 'nivel', array('greater_than[0]'),array('greater_than'=>'Selecione o nível acadêmico.'));
        $this->form_validation->set_rules('recipient-contrato','contrato',array('greater_than[0]'),array('greater_than'=>'Selecione um contrato.'));
        // Definição dos delimitadores
        $this->form_validation->set_error_delimiters('<p class="text-danger">', '</p>');
        // Verifica se o formulario é valido
        if ($this->form_validation->run() == FALSE) {
          $this->session->set_flashdata('formDanger','<strong>Não foi possível cadastrar o professor, pois existe(m) erro(s) no formulário:</strong>');
          $dados['contrato']        = convert($this->Contrato_model->getAll(), TRUE);
          $dados['nivel']           = convert($this->Nivel_model->getAll(), TRUE);
          $dados['disciplinas']     = convert($this->Disciplina_model->getAll(TRUE));
          $dados['professores']     = $this->Professor_model->getAll();
          $this->load->view('includes/header', $dados);
          $this->load->view('includes/sidebar');
          $this->load->view('professores/professores');
					$this->load->view('includes/footer');
					$this->load->view('professores/js_professores');

        } else {
            $id = $this->input->post('recipient-id');
          // Pega os dados do formulário
          $professor = array(
            'nome'            => $this->input->post("recipient-nome"),
            'matricula'       => $this->input->post('recipient-matricula'),
            'nascimento'      => brToSql($this->input->post("recipient-nascimento")),
            'email'           => $this->input->post('recipient-email'),
            'coordenador'     => ($this->input->post("recipient-coordenador") == null) ? 0 : 1,
            'idContrato'      => $this->input->post("recipient-contrato"),
            'idNivel'         => $this->input->post("recipient-nivelAcademico"),
            'disciplinas'     => $this->input->post('professorDisciplinas[]')
          );
          if ($this->Usuario_model->update($id, $professor)) {
            $this->Professor_model->update($id, $professor);
            $this->Competencia_model->delete($id);
            foreach ($professor['disciplinas'] as $idDisciplina)
              $this->Competencia_model->insert($id,$idDisciplina);
            $this->session->set_flashdata('success','Dados atualizados com sucesso');
          } else {
            $this->session->set_flashdata('danger','Não foi possível atualizar os dados do professor, tente novamente ou entre em contato com o administrador do sistema. Caso tenha alterado a <strong>matrícula</strong>, verifique se a mesma já existe.');
          }
          redirect('Professor/atualizar');
        }
      }else{
        redirect('/');
      }
    }

    /**
     * Busca as disciplinas vinculada ao professor.
     * @author Caio de Freitas
     * @since 2017/04/07
     * @param INT $id - ID do professor
     */
    public function disciplinas($id, $json=TRUE) {
        $this->load->model(array('Competencia_model'));
        $disciplinas = $this->Competencia_model->getAllDisciplinas($id);
        if ($json)
					echo json_encode($disciplinas);
				else
					return $disciplinas;
    }

		/**
		 * busca todas as preferências de disciplinas selecionadas pelo professor
		 * @author Caio de Freitas
		 * @since 2017/04/21
		 * @param INT $idProfessor - ID do professor
		 */
		public function getPreferencia($idProfessor) {
			$this->load->model('Competencia_model');
			$preferencias = $this->Competencia_model->getPreferencia($idProfessor);

			echo json_encode($preferencias);
		}

		/**
			*	Busca todas as disciplinas vinculadas ao professor e enviar para view de preferencias
			*	@author Felipe Ribeiro
			*/
		public function preferencia(){
			if (verificaSessao() && verificaNivelPagina(array(2))){

				$this->load->model(array('Competencia_model'));
				$this->load->helper('dropdown');

				// Regra de validação do formulário
				$this->form_validation->set_rules('disciplinas[]','disciplinas',array('required'));
				// delimitadores
				$this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');

				if($this->form_validation->run() == FALSE){
					$dados['disciplinas'] = convert($this->disciplinas($this->session->id, FALSE));
					$this->load->view('includes/header', $dados);
					$this->load->view('includes/sidebarProf');
					$this->load->view('preferencias/preferencias');
					$this->load->view('includes/footer');
					$this->load->view('preferencias/js_preferencias');
				} else {

					$disciplinas = $this->input->post('disciplinas[]');
					$this->Competencia_model->clearPreferencia($this->session->id);

					foreach ($disciplinas as $disciplina)
						$this->Competencia_model->insertPreferencia($this->session->id, $disciplina, TRUE);

					$this->session->set_flashdata('success','Disciplinas selecionadas com sucesso');
					redirect('Professor/preferencia');
				}

			}else{
					redirect('/');
			}
		}

		/**
		 * busca todas as disponibilidades selecionadas pelo professor
		 * @author Jean Brock
		 * @since 2017/04/27
		 * @param INT $idProfessor - ID do professor
		 */

		public function getDisponibilidade($idProfessor) {
			$this->load->model('Disponibilidade_model');
			$disponibilidades = $this->Disponibilidade_model->getAllDisponibilidades($idProfessor);

			echo json_encode($disponibilidades);
		}

		public function getDia(){
			$dia = array('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta','Sábado');
			return $dia;
		}

		/**
			*	Busca todas as disponibilidades vinculadas ao professor e enviar para view de preferencias
			* @author Jean Brock
 		 	* @since 2017/04/27
			*/
		public function disponibilidade(){
			if (verificaSessao() && verificaNivelPagina(array(2))){

				$this->load->library(array('form_validation'));
				$this->load->helper(array('form','dropdown','date'));
				$this->load->model(array(
					'Professor_model',
					'Disponibilidade_model',
					'Periodo_model'
				));
				// Definir as regras de validação para cada campo do formulário.

				$this->form_validation->set_rules('periodo', 'periodo', array('required'));
				$this->form_validation->set_rules('dia', 'dia da semana', array('required'));
				$this->form_validation->set_rules('inicio', 'hora inicio', array('required'));
				$this->form_validation->set_rules('fim', 'hora fim', array('required'));
				// Definição dos delimitadores
				$this->form_validation->set_error_delimiters('<p class="text-danger">','</p>');
				// Verifica se o formulario é valido
				if ($this->form_validation->run() == FALSE) {
					$this->session->set_flashdata('formDanger','<strong>Não foi possível cadastrar a disponibilidade, pois existe(m) erro(s) no formulário:</strong>');
					$dados['periodo']         = convert($this->Periodo_model->getTurno(), TRUE);
					$dados['professores']     = convert($this->Professor_model->getAll(TRUE));
					$dados['disponibilidade'] = $this->Disponibilidade_model->getAllDisponibilidades($this->session->id);
					$dados['horas'] = array(
						'0'	=> 'Selecione',
						'7'=>'07:00',
						'8'=>'08:00',
						'9'=>'09:00',
						'10'=>'10:00',
						'11'=>'11:00',
						'12'=>'12:00',
						'13'=>'13:00',
						'14'=>'14:00',
						'15'=>'15:00',
						'16'=>'16:00',
						'17'=>'17:00',
						'18'=>'18:00',
						'19'=>'19:00',
						'20'=>'20:00',
						'21'=>'21:00',
						'22'=>'22:00',
						'23'=>'23:00',
					);
					$dados['dia'] = $this->getDia();



					$this->load->view('includes/header',$dados);
	        $this->load->view('includes/sidebarProf');
	        $this->load->view('disponibilidade/disponibilidades');
					$this->load->view('includes/footer');
	        $this->load->view('disponibilidade/js_disponibilidades');
				} else {
					$periodo = $this->input->post('periodo');
					$professor = ($this->session->id);
					$dia = $this->input->post('dia');
					$inicio = $this->input->post('inicio');
					$fim = $this->input->post('fim');

					if ($this->Disponibilidade_model->insertDisponibilidade ($periodo, $professor, $dia, $inicio, $fim)) {
						$this->session->set_flashdata('success','Disponibilidade cadastrada com sucesso');
					} else {
						$this->session->set_flashdata('danger','Não foi possível cadastrar o disponibilidade, tente novamente ou entre em contato com o administrador do sistema.');
					}
					redirect('Professor/disponibilidade');
				}

			}else{
					redirect('/');
			}
		}

 }

?>
