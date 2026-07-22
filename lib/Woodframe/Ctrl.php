<?php

namespace Loom73\Woodframe;

use InvalidArgumentException;
use JetBrains\PhpStorm\NoReturn;
use Loom73\Beam\Model;
use Loom73\Beam\QueryResult;
use Loom73\Heddle\Auth;
use Loom73\Ledger\Ledger;
use Loom73\Yarn\Asset;
use Loom73\Yarn\AssetUploader;
use Loom73\Yarn\AssetUploadResult;

/**
 * Base Controller
 *
 * Ctrl provides the common behaviour shared by Loom73 application controllers:
 * model/template bootstrapping, request helpers, CSRF validation, flash-based
 * redirects, and final rendering.
 *
 * Loom73 convention:
 * - GET requests read application state and prepare views.
 * - POST requests perform actions: create, update, delete, upload,
 *   authenticate, or otherwise mutate application/session state.
 */
abstract class Ctrl
{
    protected string $_controller;

    protected ?Model $_model = null;

    protected string $_method;

    protected Template $_template;

    protected ?Auth $Auth = null;

    protected ?QueryResult $user = null;
    protected ?Ledger $Ledger = null;

    protected ?string $provider = null;

    protected ?string $token = null;

    protected bool $isAuthenticated = false;

    protected bool $shouldRender = true;

    public function __construct(
        string $model,
        string $controller,
        string $method
    ) {
        $this->_controller = $controller;
        $this->_method = $method;
        $this->provider = $controller;

        if (class_exists($model)) {
            $this->_model = new $model;
        }

        $this->_template = new Template($controller, $method);

        $this->set('xss', set_CSRF_Token());
        $this->set('bodyClass', $this->bodyClassName($model, $method));
        $this->set('css', '');
        $this->set('js', '');

        // Load Up the Auth layer
        $this->bootAuth();
    }

    protected function bootAuth(): void
    {
        $this->Auth = new Auth();

        $this->isAuthenticated = $this->Auth->isLoggedIn();

        if ($this->isAuthenticated) {
            $this->user = $this->Auth->getProfile();
            $this->set('user', $this->user);
        }

        $this->set('is_authenticated', $this->isAuthenticated);
    }

    public function set(string $name, mixed $value): void
    {
        $this->_template->set($name, $value);
    }

    protected function bodyClassName(string $model, string $method): string
    {
        $parts = explode('\\', $model);
        $base = array_pop($parts);

        $base = strtolower((string) $base);
        $method = strtolower($method);

        return trim($base . ' ' . $method . ' ' . $base . '-' . $method);
    }

    /*
    |--------------------------------------------------------------------------
    | Request helpers
    |--------------------------------------------------------------------------
    |
    | post() returns trimmed input.
    | posted() returns filtered input and preserves Loom73's historical default
    | sanitization behaviour.
    |
    | Output escaping remains the responsibility of the view/template layer,
    | because different output contexts require different escaping strategies.
    |
    */

    public function requestMethod(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function isPost(
        bool $notEmpty = false,
        bool $csrf = true
    ): bool {
        if ($this->requestMethod() !== 'POST') {
            return false;
        }

        if ($notEmpty && empty($_POST)) {
            return false;
        }

        if ($csrf && !$this->validateToken()) {
            return false;
        }

        return true;
    }

    public function isGet(bool $notEmpty = false): bool
    {
        if ($this->requestMethod() !== 'GET') {
            return false;
        }

        if ($notEmpty && empty($_GET)) {
            return false;
        }

        return true;
    }

    /**
     * Backwards-compatible request method check.
     *
     * Prefer isPost() and isGet() in new controllers.
     */
    public function httpCheck(
        string $httpMethod,
        bool $notEmpty = false
    ): bool {
        return match (strtoupper($httpMethod)) {
            'POST' => $this->isPost(notEmpty: $notEmpty),
            'GET' => $this->isGet(notEmpty: $notEmpty),
            default => $this->requestMethod() === strtoupper($httpMethod),
        };
    }

    public function validateToken(): bool
    {
        $token = $_POST['csrf'] ?? null;

        $appName = $_SERVER['APPNAME'] ?? null;

        if (!$appName) {
            return false;
        }

        $sessionToken = $_SESSION[$appName]['xss'] ?? null;

        if (!$token || !$sessionToken) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    /**
     * Return a trimmed POST value without applying a filter.
     */
    public function post(string $index, mixed $default = null): mixed
    {
        if (!isset($_POST[$index])) {
            return $default;
        }

        return trim((string) $_POST[$index]);
    }

    /**
     * Return a filtered POST value.
     *
     * By default this preserves Loom73's historical behaviour:
     * text input is sanitized with FILTER_SANITIZE_FULL_SPECIAL_CHARS.
     *
     * Pass false as filter to receive the trimmed value without sanitization.
     */
    public function posted(
        string $index,
        int|false $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        mixed $default = false
    ): mixed {
        $value = $this->post($index, null);

        if ($value === null) {
            return $default;
        }

        if ($filter === false) {
            return $value;
        }

        return filter_var($value, $filter);
    }

    public function destroyToken(): void
    {
        $appName = $_SERVER['APPNAME'] ?? null;

        if (!$appName) {
            return;
        }

        unset($_SESSION[$appName]['xss']);
    }

    /** Asset Upload helpers */
    protected function hasUploadedFile(string $field): bool
    {
        if (!isset($_FILES[$field])) {
            return false;
        }

        $file = $_FILES[$field];

        if (is_array($file['error'] ?? null)) {
            foreach ($file['error'] as $error) {
                if ($error !== UPLOAD_ERR_NO_FILE) {
                    return true;
                }
            }

            return false;
        }

        return ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /**
     * $_FILES normalizer. In case we have multiple file uploads, we build
     * and array where each index contains a single uploaded file and
     * all the data connected to it
     */
    protected function uploadedFiles(string $field): array
    {
        if (!isset($_FILES[$field])) {
            return [];
        }

        $file = $_FILES[$field];

        /*
         * Single upload.
         */
        if (!is_array($file['name'] ?? null)) {
            if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                return [];
            }

            return [$file];
        }

        /*
         * Multiple upload.
         */
        $files = [];

        foreach ($file['name'] as $index => $name) {
            $error = $file['error'][$index] ?? UPLOAD_ERR_NO_FILE;

            if ($error === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $files[] = [
                'name' => $name,
                'type' => $file['type'][$index] ?? null,
                'tmp_name' => $file['tmp_name'][$index] ?? null,
                'error' => $error,
                'size' => $file['size'][$index] ?? 0,
            ];
        }

        return $files;
    }

    protected function uploadedFile(string $field): ?array
    {
        $files = $this->uploadedFiles($field);

        return $files[0] ?? null;
    }

    /**
     * Upload a single Asset
     * @param string $field
     * @param int|null $ownerType
     * @param string|null $ownerId
     * @param string|null $ownerSlot
     * @param int|null $assetType
     * @param int|null $uploadedBy
     * @param string $visibility
     * @param bool $replaceExisting
     * @return AssetUploadResult
     */
    protected function uploadAsset(
        string $field,
        ?int $ownerType = null,
        ?string $ownerId = null,
        ?string $ownerSlot = null,
        ?int $assetType = null,
        ?int $uploadedBy = null,
        string $visibility = Asset::VISIBILITY_PRIVATE,
        bool $replaceExisting = false
    ): AssetUploadResult {
        $file = $this->uploadedFile($field);

        if (!$file) {
            return AssetUploadResult::failure(
                error: new InvalidArgumentException("No file uploaded for field: {$field}")
            );
        }

        $Uploader = new AssetUploader();

        return $Uploader->upload(
            file: $file,
            ownerType: $ownerType,
            ownerId: $ownerId,
            ownerSlot: $ownerSlot,
            assetType: $assetType,
            uploadedBy: $uploadedBy,
            visibility: $visibility,
            replaceExisting: $replaceExisting
        );
    }

    /**
     * Upload multiple assets in one go
     * @param string $field
     * @param int|null $ownerType
     * @param string|null $ownerId
     * @param string|null $ownerSlot
     * @param int|null $assetType
     * @param int|null $uploadedBy
     * @param string $visibility
     * @param bool $replaceExisting
     * @return array An array of AssetUploadResult Objects
     */
    protected function uploadAssets(
        string $field,
        ?int $ownerType = null,
        ?string $ownerId = null,
        ?string $ownerSlot = null,
        ?int $assetType = null,
        ?int $uploadedBy = null,
        string $visibility = Asset::VISIBILITY_PRIVATE,
        bool $replaceExisting = false
    ): array {
        $files = $this->uploadedFiles($field);

        if (empty($files)) {
            return [];
        }

        $Uploader = new AssetUploader();

        return $Uploader->uploadMany(
            files: $files,
            ownerType: $ownerType,
            ownerId: $ownerId,
            ownerSlot: $ownerSlot,
            assetType: $assetType,
            uploadedBy: $uploadedBy,
            visibility: $visibility,
            replaceExisting: $replaceExisting
        );
    }

    /**
     *  Rendering/Not rendering helpers.
     *  these are helpers in case you need to deliver something that is NOT HTML
     *  (like in the case of Assets - see AssetCtrl for a use case.)
     */

    protected function disableRender(): void
    {
        $this->shouldRender = false;
    }
    protected function enableRender(): void
    {
        $this->shouldRender = true;
    }

    /** Access Control Guards/helpers */
    protected function requireAuth(): void
    {
        if (!$this->isAuthenticated) {
            $this->redirect('/user/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();

        if (!$this->Auth?->isAdmin($this->user)) {
            $this->redirect('/main/forbidden');
        }
    }

    protected function requireEditor(): void
    {
        $this->requireAuth();

        if (!$this->Auth?->can($this->user, 'edit_content')) {
            $this->redirect('/main/forbidden');
        }
    }

    protected function requireAbility(string $ability): void
    {
        $this->requireAuth();

        if (!$this->Auth?->can($this->user, $ability)) {
            $this->redirect('/main/forbidden');
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CRUD / QueryResult response handling
    |--------------------------------------------------------------------------
    */

    #[NoReturn]
    protected function evaluateResponse(
        QueryResult $result,
        ?int $id = null,
        ?array $data = null,
        ?string $fragment = 'edit',
        ?string $successMessage = null,
        ?string $errorMessage = null
    ): void {
        $this->destroyToken();

        if ($result->fails()) {
            $message = $errorMessage ?? MSG_QUERY_FAIL;

            if ($result->databaseErrorCode() === 1062) {
                $message .= ' ' . MSG_DUPLICATE_ENTRY;
            }

            $error = null;

            if (($_SERVER['DEBUG'] ?? 0) > 0) {
                $exception = $result->exception();

                $error = [
                    'label' => $result->errorCode() . '::' . $result->errorMessage(),
                    'details' => $exception
                        ? $exception->getFile() . ' on line ' . $exception->getLine()
                        : null,
                    'trace' => $exception?->getTrace(),
                ];
            }

            $this->redirectWithError(
                $this->backUrl(),
                [
                    'message' => $message,
                    'data' => $data,
                    'error' => $error,
                ]
            );
        }

        $message = $successMessage ?? MSG_QUERY_SUCCESS;

        $recordId = $id ?? $result->insertId;

        $this->redirectWithSuccess(
            $this->resourceUrl(
                fragment: $fragment,
                id: $recordId
            ),
            [
                'message' => $message,
                'data' => $data,
                'error' => null,
            ]
        );
    }

    protected function resourceUrl(
        ?string $fragment = 'edit',
        int|string|null $id = null
    ): string {
        $provider = $this->provider ?? $this->_controller;

        $url = '/' . trim($provider, '/');

        if ($fragment !== null && $fragment !== '') {
            $url .= '/' . trim($fragment, '/');
        }

        if ($id !== null) {
            $url .= '/' . $id;
        }

        return $url;
    }

    protected function backUrl(string $fallback = '/'): string
    {
        return $_SERVER['HTTP_REFERER'] ?? $fallback;
    }

    /*
    |--------------------------------------------------------------------------
    | Redirect helpers
    |--------------------------------------------------------------------------
    */

    #[NoReturn]
    protected function redirect(string $url): void
    {
        $this->shouldRender = false;

        header("Location: {$url}");
        exit;
    }

    #[NoReturn]
    protected function redirectWithSuccess(string $url, array $message): void
    {
        Flash::success($message);
        $this->redirect($url);
    }

    #[NoReturn]
    protected function redirectWithWarning(string $url, array $message): void
    {
        Flash::warning($message);
        $this->redirect($url);
    }

    #[NoReturn]
    protected function redirectWithError(string $url, array $message): void
    {
        Flash::error($message);
        $this->redirect($url);
    }

    #[NoReturn]
    protected function redirectWithInfo(string $url, array $message): void
    {
        Flash::info($message);
        $this->redirect($url);
    }

    /** Ledger Helpers */
    protected function ledger(): Ledger
    {
        return $this->Ledger ??= new Ledger();
    }
    protected function recordAction(
        string $action,
        string $ownerType,
        string $ownerId,
        string $summary,
        array $metadata = [],
        ?int $actorId = null
    ): bool
    {
        //$actorId = $this->user?->first()?->id;
        $actorId ??= $this->user?->first()?->id;

        if (!$actorId):
            Logger::error(
                'Ledger',
                'Unable to determine actor for action',
                [
                    'action' => $action,
                    'owner_type' => $ownerType,
                    'owner_id' => $ownerId,
                ]
            );

            return false;
        endif;

        return $this->ledger()->record(
            actorId: (int) $actorId,
            action: $action,
            ownerType: $ownerType,
            ownerId: $ownerId,
            summary: $summary,
            metadata: $metadata
        );
    }

    protected function recordAnonymousAction(
        string $action,
        string $ownerType,
        string $ownerId,
        string $summary,
        array $metadata = []
    ): bool {
        return $this->ledger()->record(
            actorId: null,
            action: $action,
            ownerType: $ownerType,
            ownerId: $ownerId,
            summary: $summary,
            metadata: $metadata
        );
    }


    public function __destruct()
    {
        if ($this->shouldRender) :
            $this->_template->render();
        endif;
    }
}


