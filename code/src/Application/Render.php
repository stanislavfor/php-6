<?php

namespace Geekbrains\Application1\Application;

use Exception;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use PDO;

class Render {

    private string $viewFolder = '/src/Domain/Views/';
    private FilesystemLoader $loader;
    private Environment $environment;

    public function __construct(){
        $this->loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . $this->viewFolder);
        $this->environment = new Environment($this->loader, [
            'cache' => $_SERVER['DOCUMENT_ROOT'].'/cache/',
        ]);
    }

    public function renderPage(string $contentTemplateName = 'page-index.tpl', array $templateVariables = []) {
        $template = $this->environment->load('main.tpl');        
        $templateVariables['content_template_name'] = $contentTemplateName; 
        return $template->render($templateVariables);
    }

    public static function renderExceptionPage(Exception $exception): string {
        // $contentTemplateName = "error.tpl";
        $viewFolder = '/src/Domain/Views/';
        $loader = new FilesystemLoader($_SERVER['DOCUMENT_ROOT'] . $viewFolder);
        $environment = new Environment($loader, [
            'cache' => $_SERVER['DOCUMENT_ROOT'].'/cache/',
        ]);

        $template = $environment->load('main.tpl');

        $templateVariables = [
            'content_template_name' => 'error.tpl',
            'error_message' => $exception->getMessage(),
        ];
        
        // $templateVariables['content_template_name'] = $contentTemplateName;
        // $templateVariables['error_message'] = $exception->getMessage();
 
        return $template->render($templateVariables);
    }

    public static function updateUser(PDO $pdo, int $id, array $data): string {
        try {
            $columns = [];
            $values = [];
            foreach ($data as $key => $value) {
                $columns[] = "$key = ?";
                $values[] = $value;
            }

            $values[] = $id;

            $query = "UPDATE users SET " . implode(', ', $columns) . " WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute($values);

            if ($stmt->rowCount() > 0) {
                return "Запись с ID пользователя $id обновлен успешно.";
            } else {
                return "Не найден пользователь с ID $id или обновление записи не требуется.";
            }
        } catch (Exception $e) {
            return self::renderExceptionPage($e);
        }
    }

    public static function deleteUser(PDO $pdo, int $id): string {
        try {
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id]);

            if ($stmt->rowCount() > 0) {
                return "Запись с ID пользователя $id успешно удалена.";
            } else {
                return "Не найден пользователь с ID $id.";
            }
        } catch (Exception $e) {
            return self::renderExceptionPage($e);
        }
    }
}