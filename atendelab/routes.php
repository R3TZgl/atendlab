<?php

require_once __DIR__ . '/app/Middleware/auth.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/UsuariosController.php';

$controller = $_GET['controller'] ?? 'auth';
$action = $_GET['action'] ?? 'login';

function responderRotaNaoEncontrada(string $mensagem): void
{
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['erro' => $mensagem]);
    exit;
}

switch ($controller) {
    case 'auth':
        $authController = new AuthController();
        switch ($action) {
            case 'login':
                $authController->exibirLogin();
                break;
            case 'entrar':
                $authController->entrar();
                break;
            case 'dashboard':
                $authController->dashboard();
                break;
            case 'logout':
                $authController->logout();
                break;
            default:
                http_response_code(404);
                echo 'Ação de autenticação não encontrada.';
        }
        break;

    case 'dashboard':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/DashboardController.php';
        $dashboardController = new DashboardController();
        switch ($action) {
            case 'resumo':
                $dashboardController->resumo();
                break;
            default:
                responderRotaNaoEncontrada('Ação de dashboard não encontrada.');
        }
        break;

    case 'frontend':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/FrontendController.php';
        $frontendController = new FrontendController();
        switch ($action) {
            case 'pessoas':
                $frontendController->pessoas();
                break;
            case 'tipos':
                $frontendController->tipos();
                break;
            case 'atendimentos':
                $frontendController->atendimentos();
                break;
            default:
                responderRotaNaoEncontrada('Página não encontrada.');
        }
        break;

    case 'pessoas':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/PessoasController.php';
        $pessoasController = new PessoasController();
        switch ($action) {
            case 'listar':
                $pessoasController->listar();
                break;
            case 'buscar':
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
                responderRotaNaoEncontrada('Ação de pessoas não encontrada.');
        }
        break;

    case 'tipos':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
        $tiposController = new TiposAtendimentosController();
        switch ($action) {
            case 'listar':
                $tiposController->listar();
                break;
            case 'buscar':
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
                responderRotaNaoEncontrada('Ação de tipos de atendimento não encontrada.');
        }
        break;

    case 'atendimentos':
        exigirAutenticacao();
        require_once __DIR__ . '/app/Controllers/AtendimentosController.php';
        $atendimentosController = new AtendimentosController();
        switch ($action) {
            case 'listar':
                $atendimentosController->listar();
                break;
            case 'visualizar':
                $atendimentosController->visualizar();
                break;
            case 'criar':
                $atendimentosController->criar();
                break;
            case 'alterarStatus':
            case 'atualizarStatus':
                $atendimentosController->atualizarStatus();
                break;
            case 'opcoesFormulario':
                $atendimentosController->opcoesFormulario();
                break;
            default:
                responderRotaNaoEncontrada('Ação de atendimentos não encontrada.');
        }
        break;

    case 'usuarios':
        exigirAutenticacao();
        $usuarioController = new UsuariosController();
        switch ($action) {
            case 'listar':
                $usuarioController->listar();
                break;
            case 'buscarPorId':
                $usuarioController->buscarPorId();
                break;
            case 'criar':
                $usuarioController->criar();
                break;
            case 'atualizar':
                $usuarioController->atualizar();
                break;
            case 'excluir':
                $usuarioController->excluir();
                break;
            default:
                http_response_code(404);
                echo 'Acao de usuarios nao encontrada.';
        }
        break;

    default:
        responderRotaNaoEncontrada('Controller não encontrado.');
}
