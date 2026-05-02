<?php

/**
 * Courier role check. Session must be started; include after connect.php.
 *
 * @return array{ok:bool,user_id?:int,msg?:string}
 */
function courier_auth_check(): array
{
    if (!isset($_SESSION['user-id'])) {
        return ['ok' => false, 'msg' => 'Unauthorized'];
    }
    $role = strtolower(trim((string)($_SESSION['role'] ?? '')));
    if ($role !== 'courier') {
        return ['ok' => false, 'msg' => 'Forbidden'];
    }
    return ['ok' => true, 'user_id' => (int) $_SESSION['user-id']];
}
