<?php
/**
 * Sagar Advertising CRM - Template View Renderer
 */

declare(strict_types=1);

class View {
    /**
     * Render view template with standard header and footer layout
     */
    public static function render(string $viewPath, array $data = [], string $layout = 'default'): void {
        extract($data, EXTR_SKIP);

        $currentUser = Session::user();
        $flashes = Session::getFlashes();
        $viewFile = ROOT_PATH . '/views/' . ltrim($viewPath, '/') . '.php';

        if (!file_exists($viewFile)) {
            die("View template not found: {$viewPath}");
        }

        if ($layout === 'none') {
            require $viewFile;
            return;
        }

        // Default layout
        require ROOT_PATH . '/views/layouts/header.php';
        require $viewFile;
        require ROOT_PATH . '/views/layouts/footer.php';
    }
}
