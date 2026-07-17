<?php

namespace Loom73\Weave\Controllers;

use InvalidArgumentException;
use Loom73\Beam\QueryResult;
use Loom73\Beam\User;
use Loom73\Heddle\Session;
use Loom73\Woodframe\Config;
use Loom73\Woodframe\Ctrl;
use Loom73\Woodframe\Mailman;
use Loom73\Yarn\AssetStorage;
use Loom73\Yarn\AssetUploader;
use Loom73\Yarn\AssetUploadResult;
use Loom73\Yarn\AssetValidator;
use Loom73\Yarn\Asset;
use PDO;
use Throwable;

class UserCtrl extends Ctrl
{
    protected User $User;
    protected Session $Session;
    public ?string $provider = 'user';

    public function __construct(
        string $model,
        string $controller,
        string $method
    ) {
        parent::__construct($model, $controller, $method);

        /*
         * Explicit controller dependencies.
         *
         * Loom73 convention:
         * - PascalCase properties are reusable components / services / models.
         * - camelCase properties are runtime data, records, or query results.
         */
        $this->User = new User();
        $this->_model = $this->User;
        $this->Session = new Session();
    }

    /**
     * Render login page.
     */
    public function login(): void
    {
        $this->set('title', 'Login');
    }

    /**
     * Validate login form and authorize access.
     */
    public function access(): void
    {
        if (!$this->isPost(notEmpty: true)) {
            return;
        }

        $username = $this->posted('username');
        $password = $this->posted('password', false);

        $user = $this->User->findByUsernameOrEmail($username);

        $this->destroyToken();

        if ($user->fails() || $user->isEmpty()) {
            $this->redirectWithError('/user/login', [
                'message' => MSG_ACCESS_USER_NOT_FOUND,
                'data' => null,
                'error' => $this->debugError($user),
            ]);
        }

        $profile = $user->first();

        if ((int) $profile->status === STATUS_INACTIVE) {
            $this->redirectWithWarning('/user/login', [
                'message' => MSG_ACCESS_INACTIVE,
                'data' => $profile,
                'error' => null,
            ]);
        }

        if (!$this->verifyPassword($password, $profile)) {
            $this->redirectWithError('/user/login', [
                'message' => MSG_ACCESS_WRONG_PASSWORD,
                'data' => $profile,
                'error' => null,
            ]);
        }

        $this->Auth->authorize($profile->idauth_user);

        $this->redirectWithSuccess('/main', [
            'message' => MSG_ACCESS_SUCCESS,
            'data' => $profile,
            'error' => null,
        ]);
    }

    /**
     * Logout current user.
     */
    public function logout(): void
    {
        unset($_SESSION[$_SERVER['APPNAME']][$_SERVER['SESSION_KEY']]);

        session_destroy();

        $this->redirect('/user/login');
    }

    /**
     * Password recovery request form.
     */
    public function recover(): void
    {
        $this->set('title', MSG_ACCESS_RECOVERY);
    }

    public function recover_send(): void
    {
        if (!$this->isPost(notEmpty: true)) {
            $this->redirect('/user/recover');
        }

        $username = $this->posted('username');

        $user = $this->User->findByUsernameOrEmail($username);
        $this->destroyToken();

        if ($user->fails() || $user->isEmpty()) {
            $this->redirectWithInfo('/user/recover', [
                'message' => MSG_RECOVERY_EMAIL_SENT,
                'data' => null,
                'error' => null,
            ]);
        }

        $profile = $user->first();

        $recovery = $this->User->setRecoveryCode($user);

        if ($recovery instanceof Throwable || !$recovery) {
            $this->redirectWithError('/user/recover', [
                'message' => MSG_CANNOT_SET_RECOVERY_CODE,
                'data' => $recovery instanceof Throwable
                    ? $recovery->getCode() . '::' . $recovery->getMessage()
                    : null,
            ]);
        }

        $emailMessage = 'Please click on the link below to reset your password. <br />'
            . '<a href="' . $_SERVER['APPURL'] . '/user/reset/' . $recovery . '">Reset My Password</a>';

        $mail = Mailman::sendMail(
            [
                'email' => $_SERVER['APPEMAIL'],
                'name' => $_SERVER['APPNAME'],
            ],
            $profile->email,
            $_SERVER['APPNAME'] . ' - Password reset',
            $emailMessage
        );

        /*
         * Preserves the original behaviour:
         * Mailman::sendMail() appears to return false on success.
         */
        if (!$mail === true) {
            $this->redirectWithSuccess('/user/recover', [
                'message' => MSG_RECOVERY_EMAIL_SENT,
                'data' => null,
                'error' => null,
            ]);
        }

        $this->redirectWithError('/user/recover', [
            'message' => MSG_EMAIL_FAILED_SEND,
            'data'  => null,
            'error' => ($_SERVER['DEBUG'] ?? 0) > 0 && $mail instanceof \Throwable
                    ? $mail->getCode() . '::' . $mail->getMessage()
                    : null,
            ]);
    }

    /**
     * Reset password after recovery hash validation.
     */
    public function reset(string $hash): void
    {
        $this->set('title', MSG_TITLES['H_RESET_PWD']);

        $user = $this->User->getBy([
            'recovery' => [
                'operator' => '=',
                'value' => $hash,
                'type' => PDO::PARAM_STR,
            ],
        ]);

        if ($user->fails() || $user->isEmpty()) {
            $this->redirectWithError('/user/recover', [
                'message' => MSG_RECOVERY_CODE_INVALID,
                'data' => null,
                'error' => $this->debugError($user),
            ]);
        }

        $profile = $user->first();

        if ($this->recoveryCodeExpired($profile)) {
            $this->redirectWithError('/user/recover', [
                'message' => MSG_RECOVERY_CODE_EXPIRED,
                'data' => null,
                'error' => null,
            ]);
        }

        $this->set('hash', $hash);
    }

    public function reset_save(): void
    {
        if (!$this->isPost(notEmpty: true)) {
            $this->redirect('/user/recover');
        }

        $hash = $this->posted('recovery', false, null);

        if ($hash === null || $hash === '') {
            $this->redirectWithError('/user/recover', [
                'message' => MSG_RECOVERY_CODE_MISSING,
                'data' => null,
                'error' => null,
            ]);
        }

        $user = $this->User->getBy([
            'recovery' => [
                'operator' => '=',
                'value' => $hash,
                'type' => PDO::PARAM_STR,
            ],
        ]);

        $this->destroyToken();

        if ($user->fails() || $user->isEmpty()) {
            $this->redirectWithError('/user/recover', [
                'message' => MSG_RECOVERY_CODE_INVALID,
                'data' => null,
                'error' => $this->debugError($user),
            ]);
        }

        $profile = $user->first();

        if ($this->recoveryCodeExpired($profile)) {
            $this->redirectWithError('/user/recover', [
                'message' => MSG_RECOVERY_CODE_EXPIRED,
                'data' => null,
                'error' => null,
            ]);
        }

        try {
            $data = $this->passwordPayloadFromPost(required: true);
        } catch (InvalidArgumentException $e) {
            $this->redirectWithError('/user/reset/' . urlencode($hash), [
                'message' => $e->getMessage(),
                'data' => null,
                'error' => null,
            ]);
        }

        /*
         * Burn the recovery code after use.
         *
         * This is better than only expiring recovery_created_at,
         * because the token can no longer be replayed.
         */
        $data['recovery'] = [
            'value' => null,
            'type' => PDO::PARAM_NULL,
        ];

        $data['recovery_created_at'] = [
            'value' => null,
            'type' => PDO::PARAM_NULL,
        ];

        $result = $this->User->updateById(
            $data,
            (int) $profile->idauth_user
        );

        if ($result->fails()) {
            $this->redirectWithError('/user/reset/' . urlencode($hash), [
                'message' => MSG_USER_RECOVERY_FAIL,
                'data' => null,
                'error' => $this->debugError($result),
            ]);
        }

        $this->redirectWithSuccess('/user/login', [
            'message' => MSG_USER_RECOVERY_SUCCESS,
            'data' => null,
            'error' => null,
        ]);
    }

    /**
     * User profile page.
     */
    public function profile(): void
    {
        $this->requireAuth();

        $this->set('title', MSG_TITLES['H_EDIT_PROFILE']);
        $this->set('user', $this->user);

        $Asset = new Asset();

        $avatar = $Asset->latestForOwnerSlot(
            ownerType: 'user',
            ownerId: (string) $this->user->data[0]->id,
            ownerSlot: 'avatar'
        );

        $this->set('avatar', $avatar);
    }

    public function profile_update(): void
    {
        $this->requireAuth();

        if (!$this->isPost(notEmpty: true)) {
            $this->redirect('/user/profile');
        }

        $profile = $this->user?->first();

        if (!$profile) {
            $this->redirectWithError('/user/login', [
                'message' => MSG_ACCESS_USER_NOT_FOUND,
                'data' => null,
                'error' => null,
            ]);
        }

        $userId = (int) $profile->id;

        $hasAvatar = $this->hasUploadedFile('avatar');

        try {
            $data = $this->profilePayloadFromPost();
        } catch (InvalidArgumentException $e) {
            $this->redirectWithError('/user/profile', [
                'message' => $e->getMessage(),
                'data' => null,
                'error' => null,
            ]);
        }

        if (empty($data) && !$hasAvatar) {
            $this->redirectWithWarning('/user/profile', [
                'message' => MSG_NOTHING_TO_UPDATE,
                'data' => null,
                'error' => null,
            ]);
        }

        if (!empty($data)) {
            $update = $this->User->updateById($data, $userId);

            if ($update->fails()) {
                $this->redirectWithError('/user/profile', [
                    'message' => MSG_PROFILE_UPDATE_FAILED,
                    'data' => null,
                    'error' => $this->debugError($update),
                ]);
            }
        }

        if ($hasAvatar) {
            $avatar = $this->registerProfileAvatar($userId);

            if ($avatar->fails()) {
                $this->redirectWithError('/user/profile', [
                    'message' => MSG_AVATAR_UPLOAD_FAILED,
                    'data' => null,
                    'error' => ($_SERVER['DEBUG'] ?? 0) > 0
                        ? $avatar->errorMessage()
                        : null,
                ]);
            }
        }

        $this->redirectWithSuccess('/user/profile', [
            'message' => MSG_PROFILE_UPDATE_SUCCESS,
            'data' => null,
            'error' => null,
        ]);
    }
    protected function hasUploadedFile(string $field): bool
    {
        return isset($_FILES[$field])
            && ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }
    protected function registerProfileAvatar(int $userId): AssetUploadResult
    {
        $Uploader = new AssetUploader();

        return $Uploader->upload(
            file: $_FILES['avatar'],
            ownerType: 'user', // asset_owner_types.slug = user
            ownerId: (string) $userId,
            ownerSlot: 'avatar',
            uploadedBy: $userId,
        );
    }

    protected function profilePayloadFromPost(): array
    {
        $data = [];

        $username = $this->posted('username', FILTER_SANITIZE_FULL_SPECIAL_CHARS, null);

        if ($username !== null && $username !== '') {
            $data['username'] = [
                'value' => $username,
                'type' => PDO::PARAM_STR,
            ];
        }

        $email = $this->posted('email', FILTER_VALIDATE_EMAIL, null);

        if ($email !== null && $email !== false && $email !== '') {
            $data['email'] = [
                'value' => $email,
                'type' => PDO::PARAM_STR,
            ];
        }

        $passwordData = $this->passwordPayloadFromPost(required: false);

        return array_merge($data, $passwordData);
    }

    /**
     * Admin user edit form.
     */
    public function edit(int $id): void
    {
        $this->requireAdmin();

        $this->set('title', MSG_TITLES['H_EDIT_USER']);

        $data = $this->User->oneProfile($id);

        $this->set('data', $data);
    }

    /**
     * Admin create user form.
     */
    public function create(): void
    {
        $this->requireAdmin();

        $this->set('title', MSG_TITLES['H_CREATE_USER']);
    }

    /**
     * Save user data, both create and update.
     */
    public function save(): void
    {
        $this->requireAuth();

        if (!$this->isPost(notEmpty: true)) {
            return;
        }

        $id = $this->posted('id', FILTER_VALIDATE_INT, null);

        $isUpdate = $id !== null && $id !== false;

        try {
            $data = $this->userPayloadFromPost(
                requirePassword: !$isUpdate
            );
        } catch (InvalidArgumentException $e) {
            $this->redirectWithError(
                $isUpdate ? '/user/edit/' . $id : '/user/create',
                [
                    'message' => $e->getMessage(),
                    'data' => $_POST,
                    'error' => null,
                ]
            );
        }

        if ($isUpdate) {
            $result = $this->User->updateById($data, (int) $id);

            $this->evaluateResponse(
                result: $result,
                id: (int) $id,
                data: $data
            );
        }

        $result = $this->User->create($data);

        if ($result->fails()) {
            $this->evaluateResponse(
                result: $result,
                data: $data
            );
        }

        $userId  = $result->insertId;

        /*
         * We create a default session record for the newly created user.
         * The redirect target must use the new user ID, not the session ID.
         */
        $sessionResult = $this->Session->createSession($userId);

        $this->evaluateResponse(
            result: $sessionResult,
            id: $userId,
            data: $data
        );
    }

    /**
     * Admin user list.
     */
    public function list(): void
    {
        $this->requireAdmin();

        $this->set('title', MSG_TITLES['H_USER_LIST']);

        $userList = $this->User->allProfiles();

        $this->set('UserList', $userList);
    }



    /*
    |--------------------------------------------------------------------------
    | User helpers
    |--------------------------------------------------------------------------
    */

    protected function verifyPassword(string $password, object $user): bool
    {
        $combinedPassword = $password . $user->salt;

        return password_verify($combinedPassword, $user->password);
    }

    protected function recoveryCodeExpired(object $user): bool
    {
        if (empty($user->recovery_created_at)) {
            return true;
        }

        $createdAt = strtotime($user->recovery_created_at);

        if (!$createdAt) {
            return true;
        }

        $expiresAt = $createdAt + (RECOVERY_CODE_TIMEOUT * 60);

        return time() > $expiresAt;
    }

    protected function userPayloadFromPost(bool $requirePassword = false): array
    {
        $fields = $this->User->getFields();

        $data = [];

        foreach ($fields as $field) {
            $name = $field['name'];

            if ($this->shouldIgnoreUserField($name)) {
                continue;
            }

            if ($name === 'password') {
                $passwordData = $this->passwordPayloadFromPost(
                    required: $requirePassword
                );

                $data = array_merge($data, $passwordData);

                continue;
            }

            $value = $this->post($name, null);

            if ($value === null || $value === '') {
                continue;
            }

            $data[$name] = [
                'value' => $this->posted($name),
                'type' => $field['type'],
            ];
        }

        return $data;
    }

    protected function shouldIgnoreUserField(string $name): bool
    {
        return in_array($name, [
            'idauth_user',
            'created_at',
            'modified_at',
            'recovery',
            'recovery_created_at',
            'salt',
        ], true);
    }

    protected function passwordPayloadFromPost(bool $required = false): array
    {
        $password = $this->posted('password', false, null);
        $confirmPassword = $this->posted('confirm_password', false, null);

        if ($password === null || $password === '') {
            if ($required) {
                throw new InvalidArgumentException('Password is required.');
            }

            return [];
        }

        if ($confirmPassword === null || $confirmPassword === '') {
            if ($required) {
                throw new InvalidArgumentException('Password confirmation is required.');
            }

            return [];
        }

        if (!hash_equals($password, $confirmPassword)) {
            throw new InvalidArgumentException('Password confirmation does not match.');
        }

        $salt = bin2hex(random_bytes(16));
        $combinedPassword = $password . $salt;

        return [
            'password' => [
                'value' => password_hash($combinedPassword, PASSWORD_ARGON2I),
                'type' => PDO::PARAM_STR,
            ],

            'salt' => [
                'value' => $salt,
                'type' => PDO::PARAM_STR,
            ],
        ];
    }

    protected function debugError(QueryResult $result): ?array
    {
        if (($_SERVER['DEBUG'] ?? 0) <= 0 || $result->passes()) {
            return null;
        }

        $exception = $result->exception();

        return [
            'label' => $result->errorCode() . '::' . $result->errorMessage(),
            'details' => $exception
                ? $exception->getFile() . ' on line ' . $exception->getLine()
                : null,
            'trace' => $exception?->getTrace(),
        ];
    }

    /**
     * Temporary Yarn test endpoint.
     *
     * Move to a development-only controller or remove once AssetUploader exists.
     */
    public function upload_tester(): void
    {
        if (!$this->isPost(notEmpty: true)) {
            return;
        }

        $config = Config::get('yarn');

        $file = $_FILES['avatar'] ?? null;

        if (!$file) {
            return;
        }

        $validator = new AssetValidator($config);
        $validation = $validator->validateUploadedFile($file);

        if ($validation->fails()) {
            echo '<pre>';
            print_r($validation->errors);
            echo '</pre>';
            exit;
        }

        $storage = new AssetStorage($config);

        $stored = $storage->storeUploadedFile(
            tmpPath: $file['tmp_name'],
            extension: $validation->extension,
            originalName: $validation->originalName,
        );

        echo '<pre>';
        print_r([
            'validation' => $validation,
            'stored' => $stored,
        ]);
        echo '</pre>';
    }
}