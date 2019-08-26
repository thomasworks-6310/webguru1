<?php /** 
* Generated by
* 
*      _____ _          __  __      _     _
*     / ____| |        / _|/ _|    | |   | |
*    | (___ | | ____ _| |_| |_ ___ | | __| | ___ _ __
*     \___ \| |/ / _` |  _|  _/ _ \| |/ _` |/ _ \ '__|
*     ____) |   < (_| | | | || (_) | | (_| |  __/ |
*    |_____/|_|\_\__,_|_| |_| \___/|_|\__,_|\___|_|
*
* The code generator that works in many programming languages
*
*			https://www.skaffolder.com
*
*
* You can generate the code from the command-line
*       https://npmjs.com/package/skaffolder-cli
*
*       npm install -g skaffodler-cli
*
*   *   *   *   *   *   *   *   *   *   *   *   *   *   *   *
*
* To remove this comment please upgrade your plan here: 
*      https://app.skaffolder.com/#!/upgrade
*
* Or get up to 70% discount sharing your unique link:
*       https://app.skaffolder.com/#!/register?friend=5d6412cce752c746a9ebe3be
*
* You will get 10% discount for each one of your friends
* 
*/
?>
<?php

/*
 * This file is part of Slim HTTP Basic Authentication middleware
 *
 * Copyright (c) 2013-2015 Mika Tuupola
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Project home:
 *   https://github.com/tuupola/slim-basic-auth
 *
 */

namespace Slim\Middleware\HttpBasicAuthentication;

class PdoAuthenticator implements AuthenticatorInterface
{
    private $options;

    public function __construct(array $options = array())
    {

        /* Default options. */
        $this->options = array(
            "table" => "users",
            "user" => "user",
            "hash" => "hash"
        );

        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
    }

    public function __invoke(array $arguments)
    {
        $user = $arguments["user"];
        $password = $arguments["password"];

        $driver = $this->options["pdo"]->getAttribute(\PDO::ATTR_DRIVER_NAME);

        $sql = $this->sql();

        $statement = $this->options["pdo"]->prepare($sql);
        $statement->execute(array($user));

        if ($user = $statement->fetch(\PDO::FETCH_ASSOC)) {
            return password_verify($password, $user[$this->options["hash"]]);
        }

        return false;
    }

    public function sql()
    {
        $driver = $this->options["pdo"]->getAttribute(\PDO::ATTR_DRIVER_NAME);

        /* Workaround to test without sqlsrv with Travis */
        if (defined("__PHPUNIT_ATTR_DRIVER_NAME__")) {
            $driver = __PHPUNIT_ATTR_DRIVER_NAME__;
        }

        if ("sqlsrv" === $driver) {
            $sql =
                "SELECT TOP 1 *
                 FROM {$this->options['table']}
                 WHERE {$this->options['user']} = ?";
        } else {
            $sql =
                "SELECT *
                 FROM {$this->options['table']}
                 WHERE {$this->options['user']} = ?
                 LIMIT 1";
        }

        return preg_replace("!\s+!", " ", $sql);
    }
}