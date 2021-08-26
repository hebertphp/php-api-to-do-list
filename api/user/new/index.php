<?php

namespace Api\User;

require realpath('../../../vendor/autoload.php');
include '../../../src/Helpers/headers.php';

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $err = [];
        try {
            $headers = apache_request_headers();
            $data = json_decode(file_get_contents('php://input'));
            $args = json_decode(file_get_contents('php://input'), true);

            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                echo json_encode(['message' => 'Payload Precondition Failed']);
                die();
            }

            if (sizeof($args) != 4) {
                echo json_encode(['message' => 'Invalid Arguments Number (Expected Four)']);
                die();
            }

            # verify if fields exist
            (!isset($data->name) ? array_push($err, 1) : null);
            (!isset($data->email) ? array_push($err, 1) : null);
            (!isset($data->username) ? array_push($err, 1) : null);
            (!isset($data->password) ? array_push($err, 1) : null);

            if (sizeof($err) > 0) {
                echo json_encode(['message' => 'Payload Precondition Failed']);
                die();
            }
        } catch (\Exception $ex) {
            echo json_encode(['message' => 'Bad Request (Invalid Syntax)']);
        }

        try {
            # Load classes
            try {
                $user = new \Api\User\User();
                $userModel = new \Api\User\UserModel();
            } catch (\PDOException $pdo_ex) {
                echo json_encode(['message' => $pdo_ex->getMessage()]);
                die();
            }

            # verifing submited data
            $user->setUsername(strip_tags($data->username));
            $user->setPassword(strip_tags($data->password));

            if ($userModel->checkUser($user)) {
                echo json_encode(['message' => 'User Already Exists']);
                die();
            }

            # Create a User
            $newUser = $userModel->create(
                $user->setName(strip_tags($data->name)),
                $user->setEmail(strip_tags($data->email)),
                $user->setUsername(strip_tags($data->username)),
                $user->setPassword(strip_tags($data->password))
            );

            if ($newUser[0]) {
                echo json_encode(
                    ['message' => 'User Successfully Added',
                        'userid' => $newUser[1],
                        'token' => $newUser[2]]
                );

            } else {
                echo json_encode(['message' => 'Could Not Add User']);
            }
            die();

        } catch (\PDOException $pdo_ex) {
            echo json_encode(['message' => $pdo_ex->getMessage()]);
            die();
        }

    } else {
        echo json_encode(['message' => 'Method Not Allowed']);
        die();
    }
} catch (\PDOException $pdo_ex) {
    echo json_encode(['message' => $pdo_ex->getMessage()]);
    die();
}
