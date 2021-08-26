<?php

namespace Api\User;

class UserModel
{

    private static $pdo;

    /**
     * Model Constructor
     */
    public function __construct()
    {
        self::$pdo = \Api\Database\Database::connection();
    }

    /**
     * Token Verify
     */

    public function auth(User $user)
    {
        try {
            $sql = 'SELECT id FROM users
                    WHERE users.token = :token';

            $stmt = self::$pdo->prepare($sql);
            $stmt->bindValue(':token', $user->getToken(), \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                return [true, $stmt->fetch(\PDO::FETCH_BOTH)];

            } else {
                return [false];
            }
        } catch (\PDOException $ex) {
            throw $ex;
        }
    }

    /**
     * Checks if a user already exists
     */

    public function checkUser(User $user)
    {
        try {
            $sql = 'SELECT id FROM users
                    WHERE users.username=:username AND users.password=:password';

            $stmt = self::$pdo->prepare($sql);
            $stmt->bindValue(':username', $user->getUsername(), \PDO::PARAM_STR);
            $stmt->bindValue(':password', $user->getPassword(), \PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                # return ( bool ) TRUE;
                return $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                return (int) 0;
            }
        } catch (\PDOException $ex) {
            throw $ex;
        }
    }

    /**
     * Search user by idKey
     */

    public function search(User $user)
    {
        try {
            $sql = 'SELECT users.id, users.name, users.email
                    FROM users WHERE users.id = :id';

            $stmt = self::$pdo->prepare($sql);
            $stmt->bindValue(':id', $user->getId(), \PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                return $stmt->fetch(\PDO::FETCH_ASSOC);
            } else {
                return (bool) false;
            }
        } catch (\PDOException $ex) {
            throw $ex;
        }
    }

    /**
     * New User
     */

    public function create(User $user)
    {
        try {
            $token = strtoupper(bin2hex(random_bytes(10)));

            self::$pdo->beginTransaction();

            $sql = 'INSERT INTO users (users.name, users.email, users.username, users.password, users.token)
                    VALUES (:name, :email, :username, :password, :token)';

            $stmt = self::$pdo->prepare($sql);
            $stmt->bindValue(':name', $user->getName(), \PDO::PARAM_STR);
            $stmt->bindValue(':email', $user->getEmail(), \PDO::PARAM_STR);
            $stmt->bindValue(':username', $user->getUsername(), \PDO::PARAM_STR);
            $stmt->bindValue(':password', $user->getPassword(), \PDO::PARAM_STR);
            $stmt->bindValue(':token', $token, \PDO::PARAM_STR);
            $stmt->execute();

            $userId = self::$pdo->lastInsertId();
            self::$pdo->commit();

            return [true, $userId, $token];

        } catch (\PDOException $ex) {
            self::$pdo->rollback();
            throw $ex;
        }
    }

    /**
     * Update User data
     */

    public function update(User $user)
    {
        try {
            self::$pdo->beginTransaction();

            $sql = 'UPDATE users SET users.name=:name, users.email = :email
                    WHERE users.id = :id';

            $stmt = self::$pdo->prepare($sql);
            $stmt->bindValue(':name', $user->getName(), \PDO::PARAM_STR);
            $stmt->bindValue(':email', $user->getEmail(), \PDO::PARAM_STR);
            $stmt->bindValue(':id', $user->getId(), \PDO::PARAM_INT);
            $stmt->execute();

            self::$pdo->commit();

            return (string) true;

        } catch (\PDOException $ex) {
            self::$pdo->rollback();
            throw $ex;
        }
    }

    public function delete(User $user)
    {
        try {
            self::$pdo->beginTransaction();

            $sql = 'DELETE FROM users WHERE users.id = :id ';

            $stmt = self::$pdo->prepare($sql);
            $stmt->bindValue(':id', $user->getId(), \PDO::PARAM_INT);
            $stmt->execute();

            self::$pdo->commit();

            return (string) true;

        } catch (\PDOException $ex) {
            self::$pdo->rollback();
            //throw $ex;
            return false;
        }
    }
}
