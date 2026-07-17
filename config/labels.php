<?php
/** Library of Generic Labels the system can use.
 *  Check documentation/labels.md for naming conventions we like to use *
 * */

/** Generic DB Labels */
const MSG_QUERY_FAIL = 'Failed to execute query on the database.';
const MSG_QUERY_SUCCESS = 'Query on the database was successful.';
const MSG_DUPLICATE_ENTRY = 'Duplicate entry found.';

/** User access control labels */
const MSG_ACCESS_USER_NOT_FOUND = 'User does not exist.';
const MSG_ACCESS_SUCCESS = 'Access granted.';
const MSG_ACCESS_WRONG_PASSWORD = 'Wrong password.';
const MSG_ACCESS_INACTIVE = 'Your account is pending activation.';
const MSG_ACCESS_RECOVERY = 'Password Recovery';
const MSG_CANNOT_SET_RECOVERY_CODE = 'We couldn\'t set the recovery code. Please contact your system administrator.';
const MSG_RECOVERY_EMAIL_SENT = 'If your account exists, an email with instructions has been sent to your inbox. Please click on the link in the email to reset your password.';
const MSG_EMAIL_FAILED_SEND = 'An error occurred while sending the email.';
const MSG_RECOVERY_CODE_INVALID = 'The recovery code is invalid.';
const MSG_RECOVERY_CODE_MISSING = 'The recovery code is missing.';
const MSG_USER_RECOVERY_FAIL = 'Impossibile impostare il reset password.';
const MSG_USER_RECOVERY_SUCCESS = 'Password reset successfully.';
const MSG_RECOVERY_CODE_EXPIRED= 'Reset code expired.';
const MSG_NOTHING_TO_UPDATE = 'No data sent, no update occurrred.';
const MSG_PROFILE_UPDATE_FAILED = 'Profile update failed.';
const MSG_AVATAR_UPLOAD_FAILED = 'Avatar upload failed.';
const MSG_PROFILE_UPDATE_SUCCESS  = 'Profile update successfully.';

const MSG_PDO_POTENTIAL_DUPLICATE = ' (Potential duplicate/data conflict)';

const MSG_TITLES = [
    'H_EDIT_PROFILE' => 'Edit Profile',
    'H_EDIT_USER' => 'Edit User',
    'H_CREATE_USER' => 'Create User',
    'H_RECOVER_PWD' => 'Password Recovery',
    'H_RESET_PWD' => 'Password Reset',
    'H_USER_LOGIN' => 'Login',
    'H_USER_LIST' => 'User List',
];