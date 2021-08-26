<?php
###################################################53
namespace Api\Task;

namespace Api\User;

require realpath('../../../vendor/autoload.php');
include('../../../src/Helpers/headers.php');

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

            if (!isset($headers['Authorization'])) {
                echo json_encode(['message' => 'Invalid or Missing Token']);

                die();
            }

            if (1 ==1) {
                $teste = "a";
            }

            if (sizeof($args) != 2) {
                echo json_encode(
                    [ 'message' => 'Invalid Arguments Number (Expected Two)']
                );
                die();
            }

            # verify if fields exist
            (!isset($data->name) ? array_push($err, 1):null);
            if (sizeof($err) > 0) {
                echo json_encode(
                    [ 'message' => 'Payload Precondition Failed']
                );

                die();
            }
        } catch (Exception $ex) {
            echo json_encode([ 'message' => 'Bad Request (Invalid Syntax)']);
            die();
        }

        # Load classes
        try {
            $task = new \Api\Task\Task();
            $taskModel = new \Api\Task\TaskModel();

            $user = new \Api\User\User();
            $userModel = new \Api\User\UserModel();
        } catch (\PDOException $pdo_ex) {
            echo json_encode([ 'message' => $pdo_ex->getMessage() ]);
            die();
        }

        try {
            $ret = $userModel->auth($user->setToken($headers['Authorization']));
            if (!$ret[0]) {
                echo json_encode([ 'message' => 'Token Refused']);
                die;
            } else {
                $task->setUserId($ret[1][0]);
                $task->setName(strip_tags($data->name));
                $newTask = $taskModel->create($task->setName(strip_tags($data->name)));
                echo json_encode([ 'message' => 'Task Successfully Added' ]);
                die();
            }
        } catch (\Exception $ex) {
            echo json_encode([ 'message' => $ex->getMessage()]);
            die;
        }
    } else {
        echo json_encode([ 'message' => 'Method Not Allowed' ]);
    }
} catch (\Exception $ex) {
    echo json_encode([ 'message' => $ex->getMessage() ]);
    die();
}
