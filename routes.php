<?php

require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Middleware/auth.php';

$controller = $_GET['controller'] ?? 'auth';
$action     = $_GET['action']     ?? 'login';

switch ($controller) {

    // -------------------------------------------------------
    // AUTH
    // -------------------------------------------------------
    case 'auth':
        $authController = new AuthController();
        switch ($action) {
            case 'login':     $authController->exibirLogin(); break;
            case 'entrar':    $authController->entrar();      break;
            case 'dashboard': $authController->dashboard();   break;
            case 'logout':    $authController->logout();      break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Acao de autenticacao nao encontrada.']);
        }
        break;

    // -------------------------------------------------------
    // USUARIOS
    // -------------------------------------------------------
    case 'usuarios':
        //exigirAutenticacao();
        $usuariosController = new UsuariosController();
        switch ($action) {
            case 'listar':      $usuariosController->listar();      break;
            case 'buscarPorId': $usuariosController->buscarPorId(); break;
            case 'criar':       $usuariosController->criar();       break;
            case 'atualizar':   $usuariosController->atualizar();   break;
            case 'excluir':     $usuariosController->excluir();     break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Acao de usuarios nao encontrada.']);
        }
        break;

    // -------------------------------------------------------
    // PESSOAS
    // -------------------------------------------------------
    case 'pessoas':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/PessoasController.php';
        $pessoasController = new PessoasController();
        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;
            case 'buscar':      // alias para compatibilidade
            case 'buscarPorId':
                $pessoasController->buscarPorId();
                break;
            case 'criar':
                $pessoasController->criar();
                break;
            case 'atualizar':
                $pessoasController->atualizar();
                break;
            case 'inativar':
                $pessoasController->inativar();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Acao de pessoas nao encontrada.']);
        }
        break;

    // -------------------------------------------------------
    // TIPOS DE ATENDIMENTO
    // -------------------------------------------------------
    case 'tipos':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
        $tiposController = new TiposAtendimentosController();
        switch ($action) {
            case 'listar':
                $tiposController->listar();
                break;
            case 'buscar':      // alias para compatibilidade
            case 'buscarPorId':
                $tiposController->buscarPorId();
                break;
            case 'criar':
                $tiposController->criar();
                break;
            case 'atualizar':
                $tiposController->atualizar();
                break;
            case 'inativar':
                $tiposController->inativar();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Acao de tipos nao encontrada.']);
        }
        break;

    // -------------------------------------------------------
    // ATENDIMENTOS
    // -------------------------------------------------------
    case 'atendimentos':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
        $atendimentosController = new AtendimentosController();
        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;
            case 'buscar':      // alias
            case 'buscarPorId':
                $atendimentosController->buscarPorId();
                break;
            case 'criar':
                $atendimentosController->criar();
                break;
            case 'alterarStatus':   // alias
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;
            default:
                http_response_code(404);
                echo json_encode(['erro' => 'Acao de atendimentos nao encontrada.']);
        }
        break;

    // -------------------------------------------------------
    // DEFAULT
    // -------------------------------------------------------
    default:
        http_response_code(404);
        echo json_encode(['erro' => 'Controller nao encontrado.']);
        break;
}
